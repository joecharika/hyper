<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


use Hyper\Application\Http\Request;

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
                ->setHref((new Request())->addQuery($this->baseUrl, $this->page($_page)))
                ->setPage($_page);
        }, range(1, $this->totalPages));

        $this->pages = \array_filter($this->pages, function ($p){
            return $p->page > 0;
        });

//        if($page < 1) Request::redirectToUrl($this->firstPageUrl);
//        if($page > $this->totalPages) Request::redirectToUrl($this->lastPageUrl);
    }

    /**
     * @param $page
     */
    private function urls($page): void
    {
        $request = new Request();
        $this->baseUrl = $request->url;

        $this->firstPageUrl = $request->addQuery($this->baseUrl, []);
        $this->currentPageUrl = Request::url();
        $this->nextPageUrl = ($page + 1) > $this->totalPages
            ? null
            : $request->addQuery($this->baseUrl, $this->page($page + 1));

        $this->prevPageUrl = ($page === 1) ? null : $request->addQuery($this->baseUrl, $this->page($page - 1));

        $this->lastPageUrl = $request->addQuery($this->baseUrl, $this->page($this->totalPages));
    }

    private function page($page): array
    {
        if ($page === 1) return ['page' => null];

        return ['page' => $page];
    }

}
