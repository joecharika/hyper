<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils;


use Hyper\Functions\Arr;
use Hyper\Functions\Str;
use InvalidArgumentException;
use Traversable;

/**
 * Class Html
 * @package hyper\Utils
 */
class Html
{
    const UNKNOWN_TAG = "Unknown tag";
    public static $print = false;

    public function __construct($print = true)
    {
        self::$print = $print;
    }

    #region Html tags

    #region 1. Basic HTML

    /**
     * <!DOCTYPE> Defines the document type
     *
     * @static
     * @access public
     * @param string $type Type of the document
     * @return string
     */
    public static function docType($type = 'html5')
    {
        $docTypes = array(
            'html5' => '<!DOCTYPE html>',
            'xhtml11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
            'xhtml1-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
            'xhtml1-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml1-frame' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
            'html4-strict' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            'html4-trans' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            'html4-frame' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
        );

        return self::output(
            array_key_exists(strtolower($type), $docTypes)
                ? "{$docTypes[$type]}\n"
                : self::UNKNOWN_TAG
        );
    }

    private static function output($output)
    {
        if (self::$print) {
            print $output;
            return '';
        } else return $output;
    }

    /**
     * <html> Defines an HTML document
     *
     * @static
     * @access public
     * @param string $head <Head /> of the document
     * @param string $body <Body /> of the document
     * @param array $attributes
     * @return string
     */
    public static function html($head = "", $body = "", $attributes = array())
    {
        return Html::create("html", $attributes, "$head$body");
    }

    /**
     * Generates an HTML tag
     *
     * @param string $tagName Name of the tag
     * @param array $attributes HTML attributes
     * @param string $innerHtml Content of the tag. Omit to create a self-closing tag
     *
     * @param bool $escape
     * @return string
     * @see attributes()
     */
    public static function create(string $tagName, array $attributes, string $innerHtml = '', $escape = false): string
    {
        $innerHtml = $escape ? Html::escape($innerHtml) : $innerHtml;
        $open = Html::open($tagName, $attributes);
        $close = Html::close($tagName);
        return "$open$innerHtml$close";
    }

    /**
     * Escapes a string for output in HTML
     *
     * @static
     * @param string $string
     * @return string
     */
    public static function escape(string $string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param string $tagName
     * @param array $attributes
     * @return string
     */
    public static function open(string $tagName, $attributes = array())
    {
        $attributesString = Html::parseAttr($attributes);
        $attributesString = empty($attributesString) ? "" : " $attributesString";
        return "<$tagName$attributesString>";
    }

    /**
     * Parse out the attributes
     *
     * @static
     * @access private
     * @param string|array - An array or string for parse the specified attributes
     * @return string The parsed attribute (attribute="value")
     */
    private static function parseAttr($attributes)
    {
        if (is_string($attributes)) {
            return (!empty($attributes)) ? ' ' . trim($attributes) : '';
        } elseif (is_array($attributes)) {
            return Html::attributes($attributes);
        } else return '';
    }

    /**
     * Converts an array of HTML attributes to a string
     *
     * If an attribute is false or null, it will not be set.
     *
     * If an attribute is true or is passed without a key, it will
     * be set without an explicit value (useful for checked, disabled, ..)
     *
     * If an array is passed as a value, it will be joined using spaces
     *
     * Note: Starts with a space
     * <code>
     * Html::attributes(array('id' => 'some-id', 'selected' => false, 'disabled' => true, 'class' => array('a', 'b')));
     * //=> ' id="some-id" disabled class="a b"'
     * </code>
     *
     * @param array $attributes Associative array of attributes
     *
     * @return string
     */
    public static function attributes(array $attributes)
    {
        $result = '';
        foreach ($attributes as $attribute => $value) {
            if ($value === false || $value === null) continue;
            if ($value === true) {
                $result .= ' ' . $attribute;
            } else if (is_numeric($attribute)) {
                $result .= ' ' . $value;
            } else {
                if (is_array($value)) { // support cases like 'class' => array('one', 'two')
                    $value = implode(' ', $value);
                }
                $result .= ' ' . $attribute . '="' . static::escape($value) . '"';
            }
        }
        return $result;
    }

    /**
     * @param string $tagName
     * @return string
     */
    public static function close(string $tagName)
    {
        return "</$tagName>";
    }

    /**
     * <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"> Defines information about the document
     *
     * @static
     * @access public
     * @param $innerHtml
     * @param array $attributes
     * @return string
     */
    public static function head($innerHtml, $attributes = array())
    {
        return Html::create("head", $attributes, "$innerHtml");
    }

    #endregion

    #region 2. Formatting

    /**
     * <title> Defines a title for the document
     *
     * @static
     * @access public
     * @param $title
     * @param array $attributes
     * @return string
     */
    public static function title($title, $attributes = array())
    {
        return Html::create("title", $attributes, "$title");
    }

    /**
     * <body> Defines the document's body
     *
     * @static
     * @access public
     * @param $innerHtml
     * @param array $attributes
     * @return string
     */
    public static function body($innerHtml, $attributes = array())
    {
        return Html::create("body", $attributes, "$innerHtml");
    }

    /**
     * <h1> to <h6> Defines HTML headings
     *
     * @static
     * @access public
     * @param $text
     * @param int $size
     * @param array $attributes
     * @return string
     */
    public static function heading($text, $size = 4, $attributes = array())
    {
        if (in_array($size, range(1, 6)))
            return Html::create("h$size", $attributes, "$text");
        return self::UNKNOWN_TAG;
    }

    /**
     * <p> Defines a paragraph
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function paragraph($text, $attributes = array())
    {
        return Html::create("p", $attributes, "$text");
    }

    /**
     * <br> Inserts a single line break
     *
     * @static
     * @access public
     * @param array $attributes
     * @param int $count How many line breaks?
     * @return string
     */
    public static function break($attributes = array(), $count = 1)
    {
        return str_repeat(Html::short('br', $attributes), $count);
    }

    /**
     * Generates an HTML short-hand tag
     *
     * @param string $tagName Name of the tag
     * @param array $attributes HTML attributes
     *
     * @return string
     * @see attributes()
     *
     */
    public static function short(string $tagName, array $attributes): string
    {
        $attributesString = Html::parseAttr($attributes);
        return "<$tagName $attributesString />" . PHP_EOL;
    }

    /*
     * <bdi> Isolates a part of text that might be formatted in a different direction from other text outside it
    <bdo> Overrides the current text direction
    <big> Not supported in HTML5. Use CSS instead. Defines big text
    <blockquote> Defines a section that is quoted from another source
    <center> Not supported in HTML5. Use CSS instead. Defines centered text
    <cite> Defines the title of a work
    <code> Defines a piece of computer code
    <del> Defines text that has been deleted from a document
    <dfn> Represents the defining instance of a term
    <em> Defines emphasized text
    <font> Not supported in HTML5. Use CSS instead. Defines font, color, and size for text
    <i> Defines a part of text in an alternate voice or mood
    <ins> Defines a text that has been inserted into a document
    <kbd> Defines keyboard input
    <mark> Defines marked/highlighted text
    <meter> Defines a scalar measurement within a known range (a gauge)
    <pre> Defines pre-formatted text
    <progress> Represents the progress of a task
    <q> Defines a short quotation
    <rp> Defines what to show in browsers that do not support ruby annotations
    <rt> Defines an explanation/pronunciation of characters (for East Asian typography)
    <ruby> Defines a ruby annotation (for East Asian typography)
    <s> Defines text that is no longer correct
    <samp> Defines sample output from a computer program
    <small> Defines smaller text
    <strike> Not supported in HTML5. Use <del> or <s> instead. Defines strikethrough text
    <strong> Defines important text
    <sub> Defines sub-scripted text
    <sup> Defines super-scripted text
    <template> Defines a template
    <time> Defines a date/time
    <tt> Not supported in HTML5. Use CSS instead. Defines teletype text
    <u> Defines text that should be stylistically different from normal text
    <var> Defines a variable
    <wbr> Defines a possible line-break
     * */

    #endregion

    #region 3. Forms and Input

    /**
     * <hr> Defines a thematic change in the content
     *
     * @static
     * @access public
     * @param array $attributes
     * @return string
     */
    public static function rule($attributes = array())
    {
        return Html::short('hr', $attributes);
    }

    /**
     * Returns non-breaking space entities
     *
     * @static
     * @access public
     * @param int $count How many spaces?
     * @return string
     */
    public static function space($count = 1)
    {
        return str_repeat('&nbsp;', $count);
    }

    #region Input Variations

    /**
     * <!--...--> Defines a comment
     *
     * @static
     * @access public
     * @param $comment
     * @return string
     */
    public static function comment($comment)
    {
        return "<!--$comment-->" . PHP_EOL;
    }

    /**
     * <acronym> Not supported in HTML5. Use <abbr> instead. Defines an acronym
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function acronym($text, $attributes = array())
    {
        return Html::create("acronym", $attributes, "$text");
    }

    /**
     * <abbr> Defines an abbreviation or an acronym
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function abbr($text, $attributes = array())
    {
        return Html::create("abbr", $attributes, "$text");
    }

    /**
     * <address> Defines contact information for the author/owner of a document/article
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function address($text, $attributes = array())
    {
        return Html::create("address", $attributes, "$text");
    }

    /**
     * <b> Defines bold text
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function bold($text, $attributes = array())
    {
        return self::output(Html::create("b", $attributes, "$text"));

    }

    /**
     * <strong> Defines important tex
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function strong($text, $attributes = array())
    {
        return Html::create("strong", $attributes, "$text");
    }

    /**
     * <bdi> Isolates a part of text that might be formatted in a different
     *
     * @static
     * @access public
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function bdi($text, $attributes = array())
    {
        return Html::create("bdi", $attributes, "$text");
    }

    /**
     * <form> Defines an HTML form for user input
     *
     * @param string $action
     * @param string $method
     * @param string $innerHtml
     * @param array $attributes
     * @return string
     */
    public static function form($action = '', $method = "get", $innerHtml = "", array $attributes = array())
    {
        if (isset($attributes['multipart']) && $attributes['multipart']) {
            $attributes['enctype'] = 'multipart/form-data';
            unset($attributes['multipart']);
        }
        $attributes = array_merge([
            'method' => is_null($method) ? "get" : $method,
            'action' => is_null($action) ? "" : $action,
            'accept-charset' => 'utf-8'
        ], $attributes);

        return Html::create("form", $attributes, $innerHtml);
    }

    /**
     * <label> Defines a label for an <input> element
     *
     * @param $text
     * @param null $for
     * @param array $attributes
     * @return string
     */
    public static function label($text, $for = null, array $attributes = array())
    {
        if (!isset($attributes['for']) && $for !== null) {
            $attributes['for'] = static::autoId($for);
        }
        if (!isset($attributes['id']) && isset($attributes['for'])) {
            $attributes['id'] = $attributes['for'] . '-label';
        }
        return Html::create('label', $attributes, $text);
    }

    /**
     * Generate an ID given the name of an input
     *
     * @static
     * @param string $name
     * @return string|null
     */
    public static function autoId($name)
    {
        if (strpos($name, '[]') !== false) {
            return null;
        }
        $name = preg_replace('/\[([^]]+)\]/u', '-\\1', $name);
        return $name;
    }

    /**
     * @param $name
     * @param null $value
     * @param array $attributes
     * @return string
     */
    public static function text($name, $value = null, array $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'text',
            'value' => $value,
        ), $attributes);
        return Html::short('input', $attributes);
    }

    public static function email($name, $value = null, $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'email',
            'value' => $value,
        ), $attributes);
        return Html::short('input', $attributes);
    }

    /**
     * @param $name
     * @param null $value
     * @param array $attributes
     * @return string
     */
    public static function password($name, $value = null, array $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'password',
            'value' => $value,
        ), $attributes);
        return Html::short('input', $attributes);
    }

    /**
     * @param $name
     * @param $value
     * @param array $attributes
     * @return string
     */
    public static function hidden($name, $value, array $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'hidden',
            'value' => $value,
        ), $attributes);
        return Html::short('input', $attributes);
    }

    /**
     * @param $name
     * @param null $text
     * @param array $attributes
     * @return string
     */
    public static function textArea($name, $text = null, array $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
        ), $attributes);
        return Html::create('textarea', $attributes, (string)$text);
    }

    /**
     * @param $name
     * @param array $collection
     * @param $checked
     * @param array $labelAttributes
     * @param bool $returnAsArray
     * @return array|string
     */
    public static function collectionCheckBoxes($name, array $collection, $checked, array $labelAttributes = array(), $returnAsArray = false)
    {
        if (!(is_array($checked) || $checked instanceof Traversable)) {
            throw new InvalidArgumentException("$name must be an array or Traversable!");
        }

        $checkBoxes = array();
        foreach ($collection as $value => $label) {
            $checkBoxes[] = Html::create(
                'label',
                $labelAttributes,
                Html::checkBox("{$name}[]", in_array($value, $checked, true), $value, array(),
                    false) . Html::escape($label),
                );
        }
        return $returnAsArray ? $checkBoxes : implode('', $checkBoxes);
    }
    #endregion

    /*
    <input> Defines an input control
    <textarea> Defines a multiline input control (text area)
    <button> Defines a clickable button
    <select> Defines a drop-down list
    <optgroup> Defines a group of related options in a drop-down list
    <option> Defines an option in a drop-down list

    <fieldset> Groups related elements in a form
    <legend> Defines a caption for a
    <fieldset> element
    <datalist> Specifies a list of pre-defined options for input controls
    <output> Defines the result of a calculation
    */

    #endregion

    #region 4. Frames

    /*<frame> Not supported in HTML5. Defines a window (a frame) in a frameset
    <frameset> Not supported in HTML5. Defines a set of frames
    <noframes> Not supported in HTML5. Defines an alternate content for users that do not support frames
    <iframe> Defines an inline frame */

    #endregion

    #region 5. Images

    /**
     * @param $name
     * @param bool $checked
     * @param int $value
     * @param array $attributes
     * @param bool $withHiddenField
     * @return array|string
     */
    public static function checkBox($name, $checked = false, $value = 1, array $attributes = array(), $withHiddenField = true)
    {
        $auto_id = static::autoId($name);
        $checkboxAttributes = array_merge(array(
            'name' => $name,
            'type' => 'checkbox',
            'value' => $value,
            'id' => $auto_id,
            'checked' => (bool)$checked,
        ), $attributes);
        $checkbox = Html::create('input', $checkboxAttributes);
        if ($withHiddenField === false) {
            return $checkbox;
        }
        $hiddenAttributes = array(
            'name' => $name,
            'type' => 'hidden',
            'value' => 0,
            'id' => $auto_id . '-hidden',
        );

        $hidden = Html::create('input', $hiddenAttributes);

        return $withHiddenField === 'array'
            ? array($hidden, $checkbox)
            : $hidden . $checkbox;
    }

    /*<img> Defines an image
    <map> Defines a client-side image-map
    <area> Defines an area inside an image-map
    <canvas> Used to draw graphics, on the fly, via scripting (usually JavaScript)
    <figcaption> Defines a caption for a <figure> element
    <figure> Specifies self-contained content
    <picture> Defines a container for multiple image resources
    <svg> Defines a container for SVG graphics */

    #endregion

    #region 6. Audio / Video

    /*<audio> Defines sound content
    <source> Defines multiple media resources for media elements (<video>, <audio> and <picture>)
    <track> Defines text tracks for media elements (<video> and <audio>)
    <video> Defines a video or movie */

    #endregion

    #region 7. Links

    /**
     * @param $name
     * @param array $collection
     * @param $checked
     * @param array $labelAttributes
     * @param bool $returnAsArray
     * @return array|string
     */
    public static function collectionRadios($name, array $collection, $checked, array $labelAttributes = array(), $returnAsArray = false)
    {
        $radioButtons = array();
        foreach ($collection as $value => $label) {
            $radioButtons[] = Html::create(
                'label',
                $labelAttributes,
                Html::radio($name, $value, $value === $checked) . Html::escape($label),
                );
        }
        return $returnAsArray ? $radioButtons : implode('', $radioButtons);
    }
    /*
    <link> Defines the relationship between a document and an external resource (most used to link to style sheets)
    <nav> Defines navigation links*/
    #endregion

    #region 8. Lists

    /**
     * @param $name
     * @param $value
     * @param bool $checked
     * @param array $attributes
     * @return string
     */
    public static function radio($name, $value, $checked = false, array $attributes = array())
    {
        $attributes = array_merge(array(
            'type' => 'radio',
            'name' => $name,
            'value' => $value,
            'checked' => (bool)$checked,
        ), $attributes);
        return Html::create('input', $attributes);
    }

    /*<ul> Defines an unordered list
    <ol> Defines an ordered list
    <li> Defines a list item
    <dir> Not supported in HTML5. Use <ul> instead. Defines a directory list
    <dl> Defines a description list
    <dt> Defines a term/name in a description list
    <dd> Defines a description of a term/name in a description list*/
    #endregion

    #region 9. Tables

    /**
     * @param $name
     * @param array $collection
     * @param null $selected
     * @param array $attributes
     * @return string
     */
    public static function select($name, array $collection, $selected = null, array $attributes = array())
    {
        $attributes = array_merge(array(
            'name' => $name,
            'id' => static::autoId($name),
            'multiple' => false,
        ), $attributes);
        if (is_string($selected) || is_numeric($selected)) {
            $selected = array($selected => 1);
        } else if (is_array($selected)) {
            $selected = array_flip($selected);
        } else {
            $selected = array();
        }
        $content = '';
        foreach ($collection as $value => $element) {
            // Element is an optgroup
            if (is_array($element) && $element) {
                $groupHtml = '';
                foreach ($element as $groupName => $groupElement) {
                    $groupHtml .= self::option($groupName, $groupElement, $selected);
                }
                $content .= Html::create('optgroup', array('label' => $value), $groupHtml, false);
            } else {
                $content .= self::option($value, $element, $selected);
            }
        }
        return self::output(Html::create('select', $attributes, $content));
    }

    /**
     * @param $value
     * @param $label
     * @param bool $selected
     * @return string
     */
    public static function option($value, $label, $selected = false)
    {
        $label = str_replace('&amp;nbsp;', '&nbsp;', Html::escape($label));
        return Html::create(
            'option',
            array(
                'value' => $value,
                'selected' => isset($selected[$value]),
            ),
            $label,
            false
        );
    }

    /**
     * @param $label
     * @param array $options
     * @return string
     */
    public static function optionGroup($label, $options = array())
    {
        $optionsHtml = '';
        foreach ($options as $value => $text) {
            $optionsHtml .= Html::option($value, $text);
        }
        return Html::create('optgroup', ["label" => $label], $optionsHtml);
    }

    /**
     * @param $name
     * @param array $attributes
     * @param string $accept
     * @return string
     */
    public static function file($name, array $attributes = array(), $accept = "*/*")
    {
        $attributes = array_merge(array(
            'type' => 'file',
            'name' => $name,
            'id' => static::autoId($name),
            'accept' => $accept,
        ), $attributes);

        return Html::create('input', $attributes);
    }

    /**
     * @param $name
     * @param string $text
     * @param array $attributes
     * @return string
     */
    public static function button($name, $text = "Button", array $attributes = array())
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'button'
        ), $attributes);
        return Html::create('button', $attributes, $text);
    }

    /**
     * @param $name
     * @param string $text
     * @param array $attributes
     * @param bool $asInput
     * @return string
     */
    public static function submit($name, $text = "Submit", array $attributes = array(), $asInput = false)
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'submit'
        ), $attributes);

        return $asInput ? Html::short('input', array_merge(["value" => $text], $attributes)) : Html::create('button',
            $attributes, $text);
    }

    /**
     * @param $name
     * @param string $text
     * @param array $attributes
     * @param bool $asInput
     * @return string
     */
    public static function reset($name, $text = "Reset", array $attributes = array(), $asInput = false)
    {
        $attributes = array_merge(array(
            'id' => static::autoId($name),
            'name' => $name,
            'type' => 'reset'
        ), $attributes);

        return $asInput ? Html::short('input', array_merge(["value" => $text], $attributes)) : Html::create('button',
            $attributes, $text);
    }

    public static function img($src, $alt = null, $attributes = array())
    {
        return Html::short("img", array_merge(
            [
                "src" => $src,
                "alt" => is_null($alt) ? implode("_", explode("/", $src)) : $alt
            ], $attributes));
    }

    /**
     * <a> Defines a hyperlink
     *
     * @param $url
     * @param array $attributes
     * @param string $innerHtml
     * @return string
     */
    public static function a($url, string $innerHtml = '', array $attributes = array()): string
    {
        return Html::create('a', array_merge(["href" => self::escape("$url")], $attributes), $innerHtml);
    }

    /**
     * @param array $items
     * @param array $options
     * @return string
     */
    public static function list(array $items, $options = []): string
    {
        $type = Arr::key($options, 'type', 'ul');
        $listAttributes = Arr::key($options, 'listAttributes', []);
        $itemAttributes = Arr::key($options, 'itemAttributes', []);
        $itemHtml = Arr::key($options, 'itemHtml', null);
        $beforeHtml = Arr::key($options, 'beforeHtml', '');
        $afterHtml = Arr::key($options, 'afterHtml', '');

        $innerHtml = $beforeHtml;

        foreach ($items as $item) {
            $innerHtml .= Str::contains($item, '<li>') ? $item : Html::create('li', $itemAttributes, (string)$item);
        }

        $innerHtml .= $afterHtml;

        return Html::create($type, $listAttributes, $innerHtml);
    }

    /**
     * @param string|array|null $heading
     * @param string|array|null $content
     * @param string|array|null $footer
     * @param array $attributes
     * @param string $delimiter
     * @return string
     */
    public static function table($heading = null, $content = null, $footer = null, $attributes = array(), $delimiter = "|")
    {
        $heading = Html::tHeaderRow($heading, Arr::key($attributes, "headerAttributes", []),
            Arr::key($attributes, "headerColumnAttributes", []), $delimiter);
        $content = Html::tBody($content, Arr::key($attributes, "bodyAttributes", []),
            Arr::key($attributes, "rowAttributes", []), Arr::key($attributes, "columnAttributes", []),
            $delimiter);
        $footer = Html::tFooterRow($footer, Arr::key($attributes, "footerAttributes", []),
            Arr::key($attributes, "footerColumnAttributes", []), $delimiter);

        return Html::create('table', Arr::key($attributes, "tableAttributes", []), ""
            . $heading
            . $content
            . $footer
        );
    }


    /* <table> Defines a table
     <caption> Defines a table caption
     <th> Defines a header cell in a table
     <tr> Defines a row in a table
     <td> Defines a cell in a table
     <thead> Groups the header content in a table
     <tbody> Groups the body content in a table
     <tfoot> Groups the footer content in a table
     <col> Specifies column properties for each column within a <colgroup> element
     <colgroup> Specifies a group of one or more columns in a table for formatting*/
    #endregion

    #region 9. Styles and Semantics

    public static function tHeaderRow($columns, $attributes = array(), $columnAttributes = array(), $delimiter = "|")
    {
        if (is_null($columns)) return "";

        $columns = is_array($columns)
            ? $columns
            : (strpos($columns, $delimiter) > 0
                ? explode($delimiter, $columns)
                : $columns
            );

        if (is_array($columns)) {
            $_heading = $columns;
            $columns = Html::open("tr", $attributes);
            foreach ($_heading as $item)
                $columns .= Html::th("$item", $columnAttributes);
            $columns .= Html::close("tr");
        }

        return Html::create("thead", [], $columns);
    }

    public static function th($innerHtml, $attributes = array())
    {
        return Html::create("th", $attributes, $innerHtml);
    }

    public static function tBody($rows, $attributes = array(), $rowAttributes = array(), $columnAttributes = array(), $delimiter = "|")
    {
        $rows = is_null($rows)
            ? ""
            : (is_array($rows)
                ? $rows :
                (strpos($rows, $delimiter) > 0
                    ? explode($delimiter, $rows)
                    : $rows)
            );

        if (is_array($rows)) {
            $_heading = $rows;
            $rows = Html::open("tbody", $attributes);
            foreach ($_heading as $item)
                $rows .= Html::tRow($item, $rowAttributes, $columnAttributes);

            $rows .= Html::close("tbody");
        }

        return $rows;
    }

    /*
     * <style> Defines style information for a document

    <span> Defines a section in a document
    <header> Defines a header for a document or section
    <footer> Defines a footer for a document or section
    <main> Specifies the main content of a document

    <article> Defines an article
    <aside> Defines content aside from the page content
    <details> Defines additional details that the user can view or hide
    <dialog> Defines a dialog box or window
    <summary> Defines a visible heading for a <details> element
    <data> Links the given content with a machine-readable translation*/
    #endregion

    #region 10. Meta Info
    /*<head> Defines information about the document
    <meta> Defines metadata about an HTML document
    <base> Specifies the base URL/target for all relative URLs in a document
    <basefont> Not supported in HTML5. Use CSS instead. Specifies a default color, size, and font for all text in a document*/
    #endregion

    #region 11. Programming
    /*<script> Defines a client-side script
    <noscript> Defines an alternate content for users that do not support clientside scripts
    <applet> Not supported in HTML5. Use <embed> or <object> instead. Defines an embedded applet
    <embed> Defines a container for an external (non-HTML) application
    <object> Defines an embedded object <param> Defines a parameter for an object*/
    #endregion

    #region 12. Icons

    public static function tRow($columns, $attributes = array(), $columnAttributes = array(), $delimiter = "|")
    {
        $columns = is_null($columns)
            ? ""
            : (is_array($columns)
                ? $columns :
                (strpos($columns, $delimiter) > 0
                    ? explode($delimiter, $columns)
                    : $columns)
            );

        if (is_array($columns)) {
            $_heading = $columns;
            $columns = Html::open("tr", $attributes);
            foreach ($_heading as $item)
                $columns .= Html::td("$item", $columnAttributes);
            $columns .= Html::close("tr");
        }

        return $columns;
    }

    public static function td($innerHtml, $attributes = array())
    {
        return Html::create("td", $attributes, $innerHtml);
    }
    #endregion
    #endregion

    #region Helper functions

    public static function tFooterRow($columns, $attributes = array(), $columnAttributes = array(), $delimiter = "|")
    {
        if (is_null($columns)) return "";

        $columns = is_array($columns)
            ? $columns
            : (strpos($columns, $delimiter) > 0
                ? explode($delimiter, $columns)
                : $columns
            );

        $tfoot = is_string($columns) ? $columns : Html::open("tr", $attributes);

        if (is_array($columns)) {
            $_heading = $columns;
            foreach ($_heading as $item)
                $tfoot .= Html::th("$item", $columnAttributes);
            $tfoot .= Html::close("tr");
        }


        return Html::create("tfoot", [], $tfoot);
    }

    public static function faIcon($name, $attributes = array(), $type = "fa")
    {
        return Html::create("i", array_merge([
            "class" => "$type fa-$name"
        ], $attributes));
    }

    public static function mdIcon($name, $attributes = array())
    {
        return Html::create("i", $attributes, "$name");
    }

    /**
     * <div> Defines a section in a document
     *
     * @param string $innerHtml
     * @param array $attributes
     * @return string
     */
    public static function div(string $innerHtml = '', array $attributes = array()): string
    {
        return self::output(Html::create('div', $attributes, $innerHtml));
    }

    /**
     * <section> Defines a section in a document
     *
     * @param array $attributes
     * @param string $innerHtml
     * @return string
     */
    public static function section(array $attributes, string $innerHtml = ''): string
    {
        return Html::create('section', $attributes, $innerHtml);
    }

    public static function style($styles)
    {
        $styleText = "";

        if (is_array($styles)) {
            foreach ($styles as $selector => $style) {
                $styleText .= $selector . '{' . $style . '}';
            }
        }
        if (is_string($styles)) {
            $styleText = $styles;
        }
        return Html::create("style", [], $styleText);
    }

    /**
     * HTML::filterXSS($str, $args) -> Filter some string with the params into $args
     *
     * @static
     * @access public
     * @param string $str String to clean the possible XSS attack.
     * @param array $args The array with the parameters
     * @return string The safe string.
     */
    public static function filterXSS($str, $args)
    {
        /* Loop trough the args and apply the filters. */
        while (list($name, $data) = each($args)) {
            $safe = false;
            $type = mb_substr($name, 0, 1);
            switch ($type) {
                case '%':
                    /* %variables: HTML tags are stripped of from the string
                    before it's inserted. */
                    $safe = self::filter($data, 'strip');
                    break;
                case '!':
                    /* !variables: HTML and special characters are escaped from the string
                    before it is used. */
                    $safe = self::filter($data, 'escapeAll');
                    break;
                case '@':
                    /* @variables: Only HTML is escaped from the string. Special characters
                     * is kept as it is. */
                    $safe = self::filter($data, 'escape');
                    break;
                case '&':
                    /* Encode a string according to RFC 3986 for use in a URL. */
                    $safe = self::filter($data, 'url');
                    break;
                default:
                    return null;
                    break;
            }
            if ($safe !== false) {
                $str = str_replace($name, $safe, $str);
            }
        }
        return $str;
    }

    #endregion

    /**
     * ONLY FOR THIS CLASS (self)
     * self::filter description
     *
     * @static
     * @access    private
     * @param string $str The input string to filter
     * @param string $mode The filter mode
     * @return    mixed May return the filtered string or may return null if the $mode variable isn't set
     */
    private static function filter($str, $mode)
    {
        switch ($mode) {
            case 'strip':
                /* HTML tags are stripped from the string
                before it is used. */
                return strip_tags($str);
            case 'escapeAll':
                /* HTML and special characters are escaped from the string
                before it is used. */
                return htmlentities($str, ENT_QUOTES, 'UTF-8');
            case 'escape':
                /* Only HTML tags are escaped from the string. Special characters
                is kept as is. */
                return htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
            case 'url':
                /* Encode a string according to RFC 3986 for use in a URL. */
                return rawurlencode($str);
            case 'filename':
                /* Escape a string so it's safe to be used as filename. */
                return str_replace('/', '-', $str);
            default:
                return null;
        }
    }
}
