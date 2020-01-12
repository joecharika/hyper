<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;


use Hyper\Database\DatabaseConfig;

class HyperConfig
{
    public
        /**
         * @var DatabaseConfig
         */
        $db,
        $debug = true,
        $limitRequests = true,
        $errors = ['default' => 'error.html.twig', 'custom' => []],
        $reportLink,
        $authorize = ['action' => 'login', 'controller' => 'auth'];

    public function __construct(string $json = '')
    {
        $abb = json_decode($json);
        $this->db = @$abb->db ?: $this->db;
        $this->debug = @$abb->debug ?: $this->debug;
        $this->limitRequests = @$abb->limitRequests ?: $this->limitRequests;
        $this->errors = @$abb->errors ?: $this->errors;
        $this->reportLink = @$abb->reportLink ?: $this->reportLink;
        $this->authorize['action'] = @$abb->authorize->action ?: $this->authorize['action'];
        $this->authorize['controller'] = @$abb->authorize->controller ?: $this->authorize['controller'];
    }

}