<?php

namespace Hyper\ViewEngine;

use Hyper\Application\HyperApp;
use Hyper\Functions\Arr;
use function preg_match_all;
use function preg_replace;
use function str_replace;

/**
 * Class HyperView
 * @package hyper\ViewEngine
 */
class HyperView
{
    /**
     * @var
     */
    protected $scripts;
    /**
     * @var
     */
    protected $title;
    /**
     * @var
     */
    protected $styles;

    /**
     * @param string|null $layoutFile
     * @param string $templateFile
     * @param string $path
     * @return string
     */
    public function compile($layoutFile, string $templateFile, string $path)
    {
        $file = null;

        $content = is_null($layoutFile) ? '' : file_get_contents($layoutFile);
        $content = is_null($layoutFile) ? file_get_contents($templateFile) : preg_replace('/<h-section type="body">(.*?)<\/h-section>/s',
            file_get_contents($templateFile), $content);

        $this->runCompile($content, $path, $file, true);


        return $file;
    }

    /**
     * @param string $content
     * @param string $path
     * @param $file
     * @param bool $getTitle
     */
    private function runCompile(string $content, string $path, &$file, $getTitle = false): void
    {

        $content = $this->editForEach($content);

        $content = $this->editElse($content);
        $content = $this->editElseIfs($content);
        $content = $this->editIfs($content);
        $content = $this->putIncludes($content);
        $content = $this->searchSections($content);
        $content = $this->renderSections($content);
        $content = $this->getTitle($content, $getTitle);
        $content = $this->imports($content);
        $file = $this->build($path);
        $this->writeCode($this->sanitize($this->clean($content)), $file);
    }

    /**
     * @param string $content
     * @return string
     */
    private function editForEach(string $content): string
    {
        $content = str_replace('->', '-&amp;gt;', $content);
        $content = str_replace('=>', '=&amp;gt;', $content);

        preg_match_all('/<h-foreach(.*?)>/s', $content, $matches);

        foreach (Arr::safeArrayGet($matches, 0, []) as $match => $inner) {
            $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
            if ($extractHtmlAttributes !== false) {
                $element = (object)$extractHtmlAttributes;

                $array = Arr::safeArrayGet($element->attributes, 'in', []);
                $val = Arr::safeArrayGet($element->attributes, 'for', '$item');

                $content = preg_replace('/<h-foreach(.*?)>/s', "<?php foreach($array as $val) :?>\n", $content, 1);
            }
        }

        return $content;
    }

    /**
     * @param $input
     * @return array|bool
     */
    private static function extractHtmlAttributes($input)
    {
        if (!preg_match('#^(<)([a-z0-9\-._:]+)((\s)+(.*?))?((>)([\s\S]*?)((<)/\2(>))|(\s)*/?(>))$#im', $input,
            $matches)) return false;
        $matches[5] = preg_replace('#(^|(\s)+)([a-z0-9\-]+)(=)(")(")#i', '$1$2$3$4$5<attr:value>$6', $matches[5]);
        $results = array(
            'element' => $matches[2],
            'attributes' => null,
            'content' => isset($matches[8]) && $matches[9] == '</' . $matches[2] . '>' ? $matches[8] : null
        );
        if (preg_match_all('#([a-z0-9\-]+)((=)(")(.*?)("))?(?:(\s)|$)#i', $matches[5], $attrs)) {
            $results['attributes'] = array();
            foreach ($attrs[1] as $i => $attr) {
                $results['attributes'][$attr] = isset($attrs[5][$i]) && !empty($attrs[5][$i]) ? ($attrs[5][$i] != '<attr:value>' ? $attrs[5][$i] : '') : $attr;
            }
        }
        return $results;
    }

    /**
     * @param $content
     * @return string
     */
    private function editElse($content): string
    {
        preg_match_all('/<h-else>(.*?)<\/h-else>/s', $content, $matches);

        foreach (Arr::safeArrayGet($matches, 0, []) as $match => $inner) {
            $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
            if ($extractHtmlAttributes !== false) {
                $element = (object)$extractHtmlAttributes;
                $content = str_replace($element->content, "<?php else: ?>$element->content", $content);
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    private function editElseIfs($content): string
    {
        preg_match_all('/<h-else-if(.*?)<\/h-else-if>/s', $content, $matches);

        foreach (Arr::safeArrayGet($matches, 0, []) as $match => $inner) {
            $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
            if ($extractHtmlAttributes !== false) {
                $element = (object)$extractHtmlAttributes;
                $condition = Arr::safeArrayGet($element->attributes, 'condition', 'true');
                $content = str_replace($element->content, "<?php elseif($condition) : ?> $element->content", $content);
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    private function editIfs($content): string
    {
//        preg_match_all("/<h-if(.*?)<\/h-if>/s", $content, $matches);

        preg_match_all('/<h-if(.*?)>/s', $content, $matches);

        foreach (Arr::safeArrayGet($matches, 0, []) as $match => $inner) {
            $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
            if ($extractHtmlAttributes !== false) {
                $element = (object)$extractHtmlAttributes;

                $condition = Arr::safeArrayGet($element->attributes, 'condition', 'true');

                $content = preg_replace('/<h-if(.*?)>/s', "<?php if($condition) :?>\n", $content, 1);
            }
        }
        return $content;

//        foreach (safeArrayGet($matches, 0, []) as $match => $inner) {
//            $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
//            if ($extractHtmlAttributes !== false) {
//                $element = (object)$extractHtmlAttributes;
//
//                $condition = safeArrayGet($element->attributes, "condition", 'true');
//
        /*                $content = $this->clean(str_replace($element->content, "<?php if($condition) : ?> $element->content <?php endif; ?>", "$content"));*/
//            }
//        }
//        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function putIncludes(string $content): string
    {
        $includes = [
            'h-include' => 'Views/Includes/',
            'h-include-file' => '',
            'h-include-view' => 'Views/',
        ];

        foreach ($includes as $includeType => $path) {
            preg_match_all("/<$includeType>(.*?)<\/$includeType>/s", $content, $matches);

            foreach (Arr::safeArrayGet($matches, 0, []) as $match => $inner) {
                $extractHtmlAttributes = $this->extractHtmlAttributes($inner);
                if ($extractHtmlAttributes !== false) {
                    $element = (object)$extractHtmlAttributes;
                    $file = $element->content . '.php';
                    $file = (new HyperView())->compileFile($path . $file, "\\$path" . $element->content);
                    $content = str_replace("<$includeType>$element->content</$includeType>",
                        "<?php include '$file'; ?>", $content);
                }
            }
        }

        return $content;
    }

    /**
     * @param string $templateFile
     * @param string $path
     * @return string
     */
    public function compileFile(string $templateFile, string $path): string
    {
        $content = file_get_contents($templateFile);
        $this->runCompile($content, $path, $file);
        return $file;
    }

    /**
     * @param string $content
     * @return string
     */
    private function searchSections(string $content): string
    {
        foreach (HyperApp::$sections as $key => $value) {
            preg_match_all("/<h-section $key>(.*?)<\/h-section>/s", $content, $matches);
            $content = preg_replace("/<h-section $key>(.*?)<\/h-section>/s", '', $content);
            $content = preg_replace("/<h-section $key>(.*?)<\/h-section>/s", '', $content);
            if (array_key_exists(0, $matches[1])) HyperApp::$sections[$key] = $matches[1][0];
        }
        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function renderSections(string $content): string
    {
        foreach (HyperApp::$sections as $key => $value) {
            $content = preg_replace("/<h-section type=\"$key\">(.*?)<\/h-section>/s", HyperApp::$sections[$key],
                $content);
        }
        return $content;
    }

    /**
     * @param string $content
     * @param bool $getTitle
     * @return string
     */
    private function getTitle(string $content, $getTitle = false): string
    {
        if ($getTitle) {
            preg_match('/<h-title>(.*?)<\/h-title>/s', $content, $matches);
            $inner = Arr::safeArrayGet($matches, 1, HyperApp::$name);
            $content = preg_replace('/{{ TITLE }}/s', $inner, $content);
        }
        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    private function imports($content)
    {
        $importsArray = [];
        $imports = '';

        preg_match_all('/<h-use>(.*?)<\/h-use>/s', $content, HyperApp::$imports);
        foreach (Arr::safeArrayGet(HyperApp::$imports, 1, []) as $import) {
            array_push($importsArray, $import);
        }

        preg_match_all('/<\?php use (.*?); \?>/s', $content, HyperApp::$imports);
        foreach (Arr::safeArrayGet(HyperApp::$imports, 1, []) as $import) {
            array_push($importsArray, $import);
        }

        if (!empty($importsArray)) $imports = "<?php\n";

        foreach (array_unique($importsArray) as $import) {
            $imports .= "use $import;\n";
        }

        if (!empty($importsArray)) $imports .= ' ?>';

        $content = preg_replace('/<\?php use (.*?); \?>/s', '', $content);
        $content = preg_replace('/<h-use>(.*?)<\/h-use>/s', '', $content);

        return $this->clean($imports . $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function clean(string $content): string
    {
        $replacements = [
            '%20' => ' ',
            '&amp;gt;' => '>',
            '&gt;' => '>',
            '&lt;' => '<',
            '&amp;lt;' => '<',
        ];

        $content = strtr($content, $replacements);


//        foreach ($replacements as $item => $value) {
//            $content = str_replace('$item', '$value', $content);
//        }

        return $content;
    }

    /**
     * @param string $path
     * @return string
     */
    private function build(string $path): string
    {
        $buildDir = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] . '/Build');
        $buildPath = str_replace('\\', '/', $buildDir . $path);
        $buildFile = str_replace('\\', '/', $buildPath . '_build.php');

        if (!HyperApp::$debug) if (file_exists($buildFile)) return $buildFile;

        if (!file_exists($buildDir)) mkdir($buildDir, 0777, true);

        $_b = explode('/', $buildPath);
        array_pop($_b);
        $buildPath = implode('/', $_b);

        if (!file_exists($buildPath)) mkdir($buildPath, 0777, true);

        return $buildFile;
    }

    /**
     * @param string $content
     * @param string $file
     */
    private function writeCode(string $content, string $file)
    {
        $content = str_replace('<h-code>', '<?php ', $content);
        $content = str_replace('</h-code>', ' ?>', $content);

        $content = str_replace('{{', '<?php print ', $content);
        $content = str_replace('}}', ' ?>', $content);
        $content = str_replace('<h>', '<?php print ', $content);
        $content = str_replace('</h>', ' ?>', $content);

        $fp = fopen($file, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * @param string $content
     * @return string
     */
    private function sanitize(string $content): string
    {

        $replace = [
            '/<\/h-foreach>/s' => '<?php endforeach; ?>',
            '/<\/h-if>/s' => '<?php endif; ?>',
        ];

        $useless = [
            '/<h-else>/s',
            '/<h-else-if (.*?)>/s',
            '/<h-if (.*?)>/s',
            '/<!--(.*?)-->/s',
            '/<h-title>(.*?)<\/h-title>/s',
            '/<\/h-else>/s',
            '/<\/h-else-if>/s',
            '/<h-include>/s',
            '/<\/h-include>/s',
            '/<h-include-file>/s',
            '/<\/h-include-file>/s',
            '/<h-include-view>/s',
            '/<\/h-include-view>/s',
        ];

        foreach ($replace as $item => $rep) {
            $content = preg_replace($item, $rep, $content);
        }

        foreach ($useless as $item) {
            $content = preg_replace($item, '', $content);
        }

        if (HyperApp::$debug) return $content;

        $content = explode('\n', $content);
        $result = [];

        foreach ($content as $line) {
            $line = trim($line);
            if (strlen($line) !== 0) {
                array_push($result, $line);
            }
        }
        return implode(' ', $result);
    }

    /**
     * @param string $template
     * @param string $path
     * @return string
     */
    public function compileTemplate(string $template, string $path): string
    {
        $this->runCompile($template, $path, $file);
        return $file;
    }
}
