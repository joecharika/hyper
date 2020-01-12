<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Files;


use Hyper\Exception\HyperException;
use function file_exists;
use function getimagesize;
use function imagealphablending;
use function imagecolorallocate;
use function imagecolortransparent;
use function imagecopyresized;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagejpeg;
use function imagepng;
use function imagesavealpha;

/**
 * Class ImageHandler
 * @package Hyper\Files
 */
abstract class ImageHandler
{
    /**
     * Optimise image quality
     * @param $image
     * @param array $sizes
     * @return bool|object
     * @throws HyperException
     */
    public static function optimise($image, $sizes = [10, 30, 50, 70, 90, 100])
    {
        $name = FileHandler::getName($image);
        $extension = FileHandler::getExtension($image);
        $org = $image;

        $image = self::image($image);

        $result = true;

        foreach ($sizes as $size) {
            $folder = Folder::assets() . "uploads/optimised/x{$size}/";
            Folder::create($folder);

            $result &= self::save($org, $image, "$folder$name.$extension", $size);

            if (!$result)
                throw new HyperException("Failed to save $folder/$name.$extension");
        }

        return !$result ?: self::getOptimisedImg("$name.$extension");
    }

    /**
     * @param $fileName
     * @return false|resource
     * @throws HyperException
     */
    public static function image($fileName)
    {
        $type = @getimagesize($fileName)['mime'];

        if ($type == 'image/jpeg')
            $img = imagecreatefromjpeg($fileName);
        elseif ($type == 'image/gif')
            $img = imagecreatefromgif($fileName);
        elseif ($type == 'image/png')
            $img = imagecreatefrompng($fileName);
        else
            throw new HyperException('Unsupported image type');

        return $img;
    }

    /**
     * @param $originalFile
     * @param $imageResource
     * @param $savePath
     * @param $quality
     * @return bool
     */
    private static function save($originalFile, $imageResource, $savePath, $quality)
    {
        $info = getimagesize($originalFile);
        $type = @$info['mime'];

        if ($type == 'image/png')
            return imagepng(
                self::resize($imageResource, $info, $quality / 100),
                $savePath,
                10 - ($quality / 10),
                PNG_ALL_FILTERS
            );

        return imagejpeg(
            self::resize($imageResource, $info, $quality / 100),
            $savePath,
            $quality
        );
    }

    /**
     *
     * **********************************
     * @param $source
     * @param $orgSize
     * @param int $percent
     * **********************************
     * @return false|resource
     */
    public static function resize($source, $orgSize, $percent = 1)
    {
        [$width, $height] = $orgSize;

        $newWidth = $width * $percent;
        $newHeight = $height * $percent;

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        // integer representation of the color black (rgb: 0,0,0)
        $background = imagecolorallocate($thumb, 0, 0, 0);

        // removing the black from the placeholder
        imagecolortransparent($thumb, $background);

        // turning off alpha blending (to ensure alpha channel information
        // is preserved, rather than removed (blending with the rest of the
        // image in the form of black))
        imagealphablending($thumb, false);

        // turning on alpha channel information saving (to ensure the full range
        // of transparency is preserved)
        imagesavealpha($thumb, true);

        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $thumb;
    }

    /**
     * @param $fileName
     * @return object
     */
    public static function getOptimisedImg($fileName)
    {
        return (object)[
            'xs' => self::getImage($fileName, 10) ?? self::getImage($fileName, 20),
            'sm' => self::getImage($fileName, 30) ?? self::getImage($fileName, 40),
            'md' => self::getImage($fileName, 50) ?? self::getImage($fileName, 60),
            'lg' => self::getImage($fileName, 70) ?? self::getImage($fileName, 80),
            'xl' => self::getImage($fileName, 90),
            'org' => self::getImage($fileName, 100),
        ];
    }

    /**
     * @param $fileName
     * @param int $size
     * @return string|null
     */
    public static function getImage($fileName, int $size)
    {
        $base = Folder::assets() . 'uploads/optimised/';
        $name = FileHandler::getName($fileName);
        $extension = FileHandler::getExtension($fileName);

        if (file_exists("{$base}x{$size}/$name.$extension")) {
            return "/assets/uploads/optimised/x{$size}/$name.$extension";
        }

        return $fileName;
    }
}