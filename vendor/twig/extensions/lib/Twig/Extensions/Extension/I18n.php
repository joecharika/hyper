<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

class Twig_Extensions_Extension_I18n extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(new Twig_Extensions_TokenParser_Trans());
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
             new Twig_SimpleFilter('trans', 'gettext'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'i18n';
    }
}

class_alias('Twig_Extensions_Extension_I18n', 'Twig\Extensions\I18nExtension', false);
