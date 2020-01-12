<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


use Hyper\Http\Request;

class Pagination
{
    public $totalPages,
        $totalItems,
        $currentItems,
        $currentPage,
        $perPage,
        $firstPageUrl,
        $currentPageUrl,
        $lastPageUrl,
        $nextPageUrl,
        $prevPageUrl,
        $items,
        $baseUrl;

    /** @var array */
    public $pages;

    public function __construct(&$list, $page = 1, $perPage = 20)
    {
        $page = (int)$page;

        if (isset($page))
            $_GET['page'] = $page;

        $this->totalItems = count($list);
        $this->items = $list = array_slice($list, $perPage * ($page - 1), $perPage);
        $this->perPage = $perPage;
        $this->currentItems = count($list);
        $this->currentPage = $page;
        $this->totalPages = (int)ceil($this->totalItems / $perPage);

        $this->urls($page);

        $this->pages = array_map(function ($_page) {
            return (new Page())
                ->setHref($this->baseUrl . $this->getQuery($_page))
                ->setPage($_page);
        }, range(1, $this->totalPages));

//        if($page < 1) Request::redirectToUrl($this->firstPageUrl);
//        if($page > $this->totalPages) Request::redirectToUrl($this->lastPageUrl);
    }

    /**
     * @param $page
     */
    private function urls($page): void
    {
        $this->baseUrl = Request::protocol() . '://' . Request::host() . Request::path();

        $this->firstPageUrl = $this->baseUrl . $this->getQuery();
        $this->currentPageUrl = Request::url();
        $this->nextPageUrl = ($page + 1) > $this->totalPages ? null : $this->baseUrl . $this->getQuery($page + 1);
        $this->prevPageUrl = ($page === 1) ? null : $this->baseUrl . (($page - 1 === 0) ? '' : $this->getQuery($page - 1));
        $this->lastPageUrl = $this->baseUrl . $this->getQuery($this->totalPages);
    }

    /**
     * @param int $page
     * @return mixed
     */
    public function getQuery(int $page = 1)
    {
        $newQuery = array_merge((array)Request::query(), ['page' => $page]);

        if ($page === 1) {
            unset($newQuery['page']);
        }

        array_walk($newQuery, function (&$a, $b) {
            $a = $b . '=' . $a;
        });

        return '?' . implode('&', $newQuery);
    }

}
