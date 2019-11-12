<?php

namespace Hyper\Models;


use Hyper\Application\Request;

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

    public function __construct(&$list, $page = 1, $perPage = 20)
    {
        $page = (int)$page;

        $this->totalItems = count($list);
        $this->items = $list = array_slice($list, $perPage * ($page - 1), $perPage);
        $this->perPage = $perPage;
        $this->currentItems = count($list);
        $this->currentPage = $page;
        $this->totalPages = (int)ceil($this->totalItems / $perPage);

        $this->urls($page);

//        if($page < 1) Request::redirectToUrl($this->firstPageUrl);
//        if($page > $this->totalPages) Request::redirectToUrl($this->lastPageUrl);
    }

    /**
     * @param $page
     */
    private function urls($page): void
    {
        $this->baseUrl = Request::protocol() . '://' . Request::server() . Request::path();

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
    public function getQuery(int $page = null)
    {
        if (isset($page))
            $_GET['page'] = $page;

        $query = [];

        foreach ((array)Request::query() as $k => $v) {
            array_push($query, $k . '=' . $v);
        }

        return '?' . implode('&', $query);
    }

}
