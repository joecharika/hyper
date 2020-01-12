<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Error;

/**
 * \Exception thrown when a syntax error occurs during lexing or parsing of a template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SyntaxError extends Error
{
    /**
     * Tweaks the error message to include suggestions.
     *
     * @param string $name  The original name of the item that does not exist
     * @param array  $items An array of possible items
     */
    public function addSuggestions($name, array $items)
    {
        $alternatives = [];
        foreach ($items as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= \strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = $lev;
            }
        }

        if (!$alternatives) {
            return;
        }

        asort($alternatives);

        $this->appendMessage(sprintf(' Did you mean "%s"?', implode('", "', array_keys($alternatives))));
    }
}

class_alias('Twig\Error\SyntaxError', 'Twig_Error_Syntax');
