<?php
namespace Web\TranslatorBundle\Dumper;

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
            $class = "\\Web\\TranslatorBundle\\Yaml\\Dumper";
            break;
        case 'xliff':
            $class = "\\Symfony\\Component\\Translation\\Dumper\\XliffFileDumper";
            break;
        }

        return new $class();

    }
}
