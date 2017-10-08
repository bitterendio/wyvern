<?php

/*
|--------------------------------------------------------------------------
| Essential
|--------------------------------------------------------------------------
|
| Core functions and settings
|
*/

if ( !function_exists('autoload_folder') )
{
    /**
     * Autoload folder
     *
     * Function to load all relevant files within given folder
     * and it's children
     *
     * @param $path string Path to given folder
     * @param $extension string Files to load by extension
     * @return bool|void
     */
    function autoload_folder($path, $extension = '.php')
    {
        if (!is_dir($path))
            return false;

        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {

                if ($entry != "." && $entry != ".." && !is_dir($path . '/' . $entry) && strpos($entry, $extension) !== false) {

                    require_once ($path . '/' . $entry);

                } else if ($entry != "." && $entry != ".." && is_dir($path . '/' . $entry)) {

                    autoload_folder($path . '/' . $entry);

                }
            }

            closedir($handle);
        }
    }
}

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| Load all calls registered in separate files in /api folder
|
*/

$relative_path = '/api';
foreach( array_unique([get_template_directory(), get_stylesheet_directory()]) as $folder )
{
    autoload_folder($folder . $relative_path);
}

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
|
| Autoload all php files in /includes folder
|
*/

$relative_path = '/includes';
foreach( array_unique([get_template_directory(), get_stylesheet_directory()]) as $folder )
{
    autoload_folder($folder . $relative_path);
}

/*
|--------------------------------------------------------------------------
| Extensions
|--------------------------------------------------------------------------
|
| Load extensions
|
*/

$relative_path = '/extensions';
foreach( array_unique([get_template_directory(), get_stylesheet_directory()]) as $folder )
{
    autoload_folder($folder . $relative_path);
}