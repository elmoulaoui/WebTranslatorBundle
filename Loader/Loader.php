<?php
namespace WebTranslatorBundle\Loader;

/**
 *
 * @author elmoulaoui
 *
 */
class Loader
{
    public static function create($format)
    {
        $class = "\\Symfony\\Component\\Translation\\Loader\\"
                . ucfirst($format) . "FileLoader";
        return new $class();

    }
}
