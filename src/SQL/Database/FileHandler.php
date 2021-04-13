<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\SQL\Database;


use Hyper\{Application\Annotations\file,
    Application\Annotations\saveAs,
    Application\Http\Request,
    Application\HyperApp,
    Exception\HyperError,
    Exception\HyperException,
    Exception\UploadException,
    Files\Folder,
    Files\ImageHandler,
    Functions\Arr,
    Functions\Logger,
    Functions\Obj,
    Functions\Str
};
use function array_keys;
use function file_exists;
use function mime_content_type;
use function strtr;

class FileHandler
{
    use HyperError;

    private ?DatabaseContext $context;

    public function __construct(DatabaseContext $context)
    {
        $this->context = $context;
    }

    public function cleanUpObjectUploads($oldObject, $newObject)
    {
        $entity = (array)$newObject;
        $old = (array)$oldObject;

        $path = Folder::assets() . 'uploads/deleted/';
        Folder::create($path);

        foreach (array_keys($entity) as $k) {
            if (file::of($this->context->model, $k)) {
                if ($entity[$k] !== $old[$k]) {
                    $file = $oldFile = strtr(strtr(Folder::root() . $old[$k], ['/' => DIRECTORY_SEPARATOR]), [DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR => DIRECTORY_SEPARATOR]);

                    $mime = @mime_content_type($file);

                    if ($mime === false) {
                        foreach (@HyperApp::config()->uploads->imageResizeSizes ?? [] as $size) {
                            $image = ImageHandler::getImage($old[$k], $size);
                            $oldFile = strtr(strtr(Folder::root() . $image, ['/' => DIRECTORY_SEPARATOR]), [DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR => DIRECTORY_SEPARATOR]);

                            if (file_exists($oldFile)) {
                                $oldFile = strtr(strtr(Folder::root() . $image, ['/' => DIRECTORY_SEPARATOR]), [DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR => DIRECTORY_SEPARATOR]);
                                $newFile = $path . \Hyper\Files\FileHandler::getName($image, true);
                                \Hyper\Files\FileHandler::move($oldFile, $newFile);
                            }
                        }
                    } else {
                        \Hyper\Files\FileHandler::move($file, $path . \Hyper\Files\FileHandler::getName($file, true));
                    }
                }
            }
        }
    }

    /**
     * @param array $entity
     * @return array
     */
    public function uploads(array $entity): array
    {
        $entityArray = $entity;

        foreach ($entityArray as $item => $value) {
            if (file::of($this->context->model, $item)) {
                $file = $this->handleUpload(Obj::property(Request::files(), $item));

                if (isset($file)) {
                    if (saveAs::of($this->context->model, $item) === 'base64') {
                        $var = base64_encode(file_get_contents($file));
                        $entityArray[$item] = "data:{$entityArray[$item]->type};base64,$var";
                    } else
                        $entityArray[$item] = $file;
                }
            }
        }

        return $entityArray;
    }

    /**
     * @param $file
     * @return string|null Upload file path
     */
    private function handleUpload($file)
    {
        # If there is no file at all then no upload will take place
        if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) return null;

        if ($file['error'] == UPLOAD_ERR_OK) {
            #If the file has a name but no temporary name hence the file did not reach the server
            if (!empty($file['name']) && empty($file['tmp_name'])) self::error(new HyperException('This file could not be uploaded'));

            #If the temporary name is empty also the file did not reach the server
            if (empty($file['tmp_name'])) return null;

            #Convert the file to an object
            $file = (object)$file;

            #Get the file type and pluralize it
            $type = Str::pluralize(Arr::key(explode('/', $file->type), 0, ''));
            $targetDir = "assets/uploads/$type";

            #Create folder for specific file type if not exists
            Folder::create($targetDir);

            $targetDir = "$targetDir/";
            $targetFile = $targetDir . basename($file->name);
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            # Complete the upload by moving the file into the specific type directory
            if (move_uploaded_file($file->tmp_name, $targetFile)) {
                $newFileName = $targetDir . \Hyper\Files\FileHandler::uploadName() . "." . $fileType;
                Logger::log("Uploading $newFileName", 'UPLOAD LOG');

                rename($targetFile, $newFileName);

                if ($type === 'images') {
                    try {
                        $img = ImageHandler::optimise($newFileName, @HyperApp::config()->uploads->imageResizeSizes);
                        if ($img !== false) {
                            \Hyper\Files\FileHandler::delete($newFileName);
                            return \Hyper\Files\FileHandler::getName($img->ref, true);
                        }
                    } catch (HyperException $e) {
                        Logger::log("Failed to optimise image [$newFileName], reverting to normal save");
                    }
                }

                return "/$newFileName";

            } else self::error(new HyperException('File upload failed'));
        }

        self::error(new UploadException($file['error']));

        return null;
    }
}