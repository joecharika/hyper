<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Files;


use Hyper\Exception\HyperException;
use Hyper\Functions\Arr;
use Hyper\Functions\Str;
use function explode;
use function fclose;
use function file_exists;
use function fopen;
use function fwrite;
use function is_int;
use function rename;
use function unlink;

/**
 * Class FileHandler
 * @package hyper\Files
 */
abstract class FileHandler
{
    /**
     * Uploads an HttpPostedFile to the server, return the name of the uploaded file
     * @param $file
     * @param string $path Path to upload to
     * @return string|null
     * @throws HyperException
     */
    public static function upload($file, $path = 'assets/uploads/')
    {
        #If there is no file at all then no upload will take place
        if (!isset($file)) return null;

        #If the file has a name but no temporary name hence the file did not reach the server
        if (!empty($file['name']) && empty($file['tmp_name'])) throw new HyperException('This file could not be uploaded');

        #If the temporary name is empty also the file did not reach the server
        if (empty($file['tmp_name'])) return null;

        #Convert the file to an object
        $file = (object)$file;

        #Get the file type and pluralize it
        $type = Str::pluralize(Arr::key(explode('/', $file->type), 0, ''));
        $targetDir = $path . $type;

        #Create folder for specific file type if not exists
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $targetDir = "$targetDir/";
        $targetFile = $targetDir . basename($file->name);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        # Complete the upload by moving the file into the specific type directory
        if (move_uploaded_file($file->tmp_name, $targetFile)) {
            $newFileName = $targetDir . hash('sha256', $file->name) . "." . $imageFileType;
            rename($targetFile, $newFileName);
            return $newFileName;
        } else throw new HyperException('File upload failed');
    }

    /**
     * Move source file to destination
     * @param $source
     * @param $destination
     * @return bool
     */
    public static function move($source, $destination): bool
    {
        return rename($source, $destination);
    }

    /**
     * Copy source file to destination
     * @param $source
     * @param $destination
     * @return bool
     * @throws HyperException
     */
    public static function copy($source, $destination): bool
    {
        if (!file_exists($source)) throw new HyperException('File to move was not found');

        $file = fopen($destination, 'w+');
        $success = fwrite($file, file_get_contents($source));
        fclose($file);

        return is_int($success);
    }

    /**
     * Delete the given file, return true on success or otherwise
     * @param $file
     * @return bool
     */
    public static function delete($file): bool
    {
        return unlink($file);
    }

    public static function getName($path, $extension = false)
    {
        $namePieces = explode('\\', strtr($path, ['/' => '\\']));

        $name = @$namePieces[array_key_last(@$namePieces)];

        if (!$extension) {
            if (Str::contains($name, '.')) {
                $extPieces = explode('.', $name);
                if (count($extPieces) == 2)
                    return @$extPieces[0];

                array_pop($extPieces);

                return join('.', $extPieces);
            }
        }

        return $name;
    }

    public static function getExtension($fileName)
    {
        if (Str::contains($fileName, '.')) {
            $extPieces = explode('.', $fileName);
            if (is_array($extPieces))
                return @array_reverse($extPieces)[0];
        }

        return null;
    }
}