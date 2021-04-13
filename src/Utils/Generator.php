<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils {


    use function hash;
    use function iconv;
    use function preg_replace;
    use function strtolower;
    use function trim;
    use function uniqid;

    /**
     * Class Generator
     * @package Hyper\Utils
     */
    class Generator
    {
        /**
         * Generate new token
         * @param string $start Starting key
         * @return string
         */
        public static function token($start = '__')
        {
            return hash('whirlpool', $start . uniqid() . uniqid() . date('jNWto.his'));
        }

        /**
         * @param $str
         * @param string $delimiter
         * @return string
         */
        public static function slugify($str, $delimiter = '-')
        {
            return strtolower(
                trim(
                    preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter,
                        preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))),
                    $delimiter)
            );

        }
    }
}