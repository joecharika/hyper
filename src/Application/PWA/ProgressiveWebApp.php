<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\PWA;


use Hyper\Application\HyperApp;
use Hyper\Exception\HyperException;
use Hyper\Files\Folder;

class ProgressiveWebApp
{
    private $id, $name;

    public function __construct($name = null)
    {
        $this->id = uniqid();
        $this->name = $name ?? @HyperApp::instance()->name ?? 'pwa-version';
    }

    /**
     * @return false|string The function returns the number of bytes that were written to the file, or false on failure foreach of the files.
     * @throws HyperException
     */
    public function save()
    {
        $root = Folder::root();

        # Start saving stuff
        $sw = file_put_contents("{$root}service-worker.js", $this->getServiceWorker());
        $preCache = file_put_contents("{$root}precache-manifest.{$this->id}.js", $this->getPreCacheManifest());
        $manifest = file_put_contents("{$root}manifest.json", $this->getManifest());

        return json_encode([
            'service-worker' => $sw,
            'precache-manifest' => $preCache,
            'manifest' => $manifest
        ]);
    }

    public function getServiceWorker(): string
    {

        return <<<JS
        /**
         * Welcome to your Workbox-powered service worker!
         *
         * You'll need to register this file in your web app and you should
         * disable HTTP caching for this file too.
         * See https://goo.gl/nhQhGp
         *
         * The rest of the code is auto-generated. Please don't update this file
         * directly; instead, make changes to your Workbox build configuration
         * and re-run your build process.
         * See https://goo.gl/2aRDsh
         */
        
        importScripts("https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js");
        
        importScripts(
          "/precache-manifest.{$this->id}.js"
        );
        
        workbox.core.setCacheNameDetails({prefix: "$this->name"});
        
        self.addEventListener('message', function(event){
          if (event.data && event.data.type === 'SKIP_WAITING') {
            self.skipWaiting();
          }
        });
        
        /**
         * The workboxSW.precacheAndRoute() method efficiently caches and responds to
         * requests for URLs in the manifest.
         * See https://goo.gl/S9QRab
         */
        self.__precacheManifest = [].concat(self.__precacheManifest || []);
        workbox.precaching.precacheAndRoute(self.__precacheManifest, {});

        JS;

    }

    #region FilesGiver

    public function getPreCacheManifest()
    {
        $__files = [];

        foreach (@$this->read_all_files(Folder::assets())['files'] ?? [] as $file) {
            $__files[] = (object)[
                "revision" => uniqid(),
                "url" => '/' . strtr($file, [Folder::root() => '', '\\' => '/'])
            ];
        }

        $files = json_encode($__files);

        return "self.__precacheManifest = (self.__precacheManifest || []).concat({$files})";

    }

    /**
     * Finds path, relative to the given root folder, of all files and directories in the given directory and its sub-directories non recursively.
     * Will return an array of the form
     * array(
     *   'files' => [],
     *   'dirs'  => [],
     * )
     * @param string $root
     * @result array
     * @return array
     * @author sreekumar
     */
    function read_all_files($root = '.')
    {
        $files = array('files' => array(), 'dirs' => array());
        $directories = array();
        $last_letter = $root[strlen($root) - 1];
        $root = ($last_letter == '\\' || $last_letter == '/') ? $root : $root . DIRECTORY_SEPARATOR;

        $directories[] = $root;

        while (sizeof($directories)) {
            $dir = array_pop($directories);
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $file = $dir . $file;
                    if (is_dir($file)) {
                        $directory_path = $file . DIRECTORY_SEPARATOR;
                        array_push($directories, $directory_path);
                        $files['dirs'][] = $directory_path;
                    } elseif (is_file($file)) {
                        $files['files'][] = $file;
                    }
                }
                closedir($handle);
            }
        }

        return $files;
    }

    /**
     * @return false|string
     * @throws HyperException
     */
    public function getManifest()
    {
        $manifest = @HyperApp::config()->manifest;

        if (!isset($manifest)) throw new HyperException('Manifest not defined in hyper config');

        return json_encode($manifest);
    }

    public function getRegisterServiceWorkerJS()
    {
        return <<<JS
            /* Register your service worker */
            if ('serviceWorker' in navigator) {
              window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                .then(r => console.log('ServiceWorker registered successfully', r) )
                .catch(error => console.log("ServiceWorker registration failed", error));
              });
            }
        JS;

    }

    #endregion

    private function write($fileName, $content)
    {
        $file = fopen($fileName, 'w+');
        fwrite($file, $content);
        fclose($file);
    }

}