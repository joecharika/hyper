<?php


namespace Controllers;


/**
 * Class HomeController
 * @package Controllers
 * @author Hyper Team
 */
class HomeController extends Hyper\Application\Controller
{
    /**
     * Home Index Action
     * @url [ /, /home ]
     */
    public function index()
    {
        self::view('home.index');
    }

    /**
     * Home About Action
     * @url [ /home/about ]
     */
    public function about()
    {
        self::view('home.about');
    }
}