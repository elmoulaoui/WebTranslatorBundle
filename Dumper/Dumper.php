<?php
namespace WebTranslatorBundle\Dumper;

/**
 *
 * @author elmoulaoui
 *
 */
class Dumper
{
    public static function create($format)
    {
        switch ($format) {
        case 'yaml':
            $class = "\\WebTranslatorBundle\\Yaml\\Dumper";
            break;
        case 'xliff':
            $class = "\\Symfony\\Component\\Translation\\Dumper\\XliffFileDumper";
            break;
        }

        return new $class();

    }
}
