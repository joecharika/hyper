<?php

use Hyper\Exception\HyperException;
use Hyper\Functions\Arr;

function checkSyntax($fileName, $checkIncludes = true)
{
    // If it is not a file or we can't read it throw an exception
    if (!is_file($fileName) || !is_readable($fileName))
        (new HyperException())->throw("Cannot read file $fileName");

    // Sort out the formatting of the filename
    $fileName = realpath($fileName);

    // Get the shell output from the syntax check command
    $output = shell_exec('php -l "' . $fileName . '"');

    // Try to find the parse error text and chop it off
    $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

    // If the error text above was matched, throw an exception containing the syntax error
    if ($count > 0)
        (new HyperException())->throw(trim($syntaxError));

    // If we are going to check the files includes
    if ($checkIncludes) {
        foreach (getIncludes($fileName) as $include) {
            // Check the syntax for each include
            checkSyntax($include);
        }
    }
}

/**
 * @param $fileName
 * @return array
 */
function getIncludes($fileName)
{
    // NOTE that any file coming into this function has already passed the syntax check, so
    // we can assume things like proper line terminations

    $includes = array();
    // Get the directory name of the file so we can prepend it to relative paths
    $dir = dirname($fileName);

    // Split the contents of $fileName about requires and includes
    // We need to slice off the first element since that is the text up to the first include/require
    $requireSplit = array_slice(preg_split('/require|include/i', file_get_contents($fileName)), 1);

    // For each match
    foreach ($requireSplit as $string) {
        // Substring up to the end of the first line, i.e. the line that the require is on
        $string = substr($string, 0, strpos($string, ";"));

        // If the line contains a reference to a variable, then we cannot analyse it
        // so skip this iteration
        if (strpos($string, "$") !== false)
            continue;

        // Split the string about single and double quotes
        $quoteSplit = preg_split('/[\'"]/', $string);

        // The value of the include is the second element of the array
        // Putting this in an if statement enforces the presence of '' or "" somewhere in the include
        // includes with any kind of run-time variable in have been excluded earlier
        // this just leaves includes with constants in, which we can't do much about
        if ($include = Arr::safeArrayGet($quoteSplit, 1, null)) {
            // If the path is not absolute, add the dir and separator
            // Then call realpath to chop out extra separators
            if (strpos($include, ':') === FALSE)
                $include = realpath($dir . DIRECTORY_SEPARATOR . $include);

            array_push($includes, $include);
        }
    }

    return $includes;
}

/**
 * @param $php
 * @throws Exception
 */
function strCheckSyntax($php)
{
    $fileName = __DIR__ . '\\' . uniqid() . '.php';

    $fileHandle = fopen($fileName, 'w');

    fwrite($fileHandle, $php);

    $fileName = realpath($fileName);

    $output = shell_exec('php -l "' . $fileName . '"');

    $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

    if ($count > 0)
        throw new Exception(trim($syntaxError));

    fclose($fileHandle);

}

