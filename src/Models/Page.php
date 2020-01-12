<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


class Page
{
    public $href;
    public $page;

    /**
     * @param mixed $href
     * @return Page
     */
    public function setHref($href)
    {
        $this->href = $href;
        return $this;
    }

    /**
     * @param mixed $page
     * @return Page
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }
}