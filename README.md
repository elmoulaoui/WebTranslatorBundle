WebTranslatorBundle
===================

manage translation files with web pages


Installation
===================

1) app/AppKernel.php, enable the bundle : 

$bundles = array(
                ....
                new Web\TranslatorBundle\WebTranslatorBundle(),
                ....
        );


2) add bundle configuration in app/config/parameters.yml

    translator_bundles:e
      acme-demo-bundle:  "%kernel.root_dir%/../src/Acme/DemoBundle"
    translator_main_language:   fr
    translator_target_languages: [en]
    translator_target_domains: [messages, validators, AcmeDemoBundle]
    translator_format: xliff


3) import bundle routing in app/config/routing.yml

web_translator:
    resource: "@WebTranslatorBundle/Resources/config/routing.yml"
    prefix:   /translator

