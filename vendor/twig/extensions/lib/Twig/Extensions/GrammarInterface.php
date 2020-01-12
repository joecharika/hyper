<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
interface Twig_Extensions_GrammarInterface
{
    public function setParser(Twig_Parser $parser);

    public function parse(Twig_Token $token);

    public function getName();
}
