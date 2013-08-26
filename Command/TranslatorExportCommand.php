<?php

namespace Web\TranslatorBundle\Command;
// Message
use Web\TranslatorBundle\Document\Message;
use Web\TranslatorBundle\Dumper\Dumper;

// Command stuff
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;

class TranslatorExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        //
        $this->setName('nelm:translator:export')
                ->setDescription('Export localization data from database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundles = $this->getContainer()->getParameter('translator_bundles');
        $languages = $this->getContainer()
                ->getParameter('translator_target_languages');
        $domains = $this->getContainer()
                ->getParameter('translator_target_domains');
        $translator_format = $this->getContainer()
                ->getParameter('translator_format');

        foreach ($bundles as $target_bundle => $dir) {
            foreach ($domains as $target_domain) {
                foreach ($languages as $target_locale) {
                    $filename = "$dir/Resources/translations/$target_domain.$target_locale.$translator_format";
                    if (!$this
                            ->exportContents($filename, $target_bundle,
                                    $target_locale, $target_domain)) {
                        $output
                                ->writeln(
                                        "<error>Target: $target_bundle.$target_domain.$target_locale does not exist in the database!</error>");
                    } else {
                        $output->writeln("<info>$filename exported Ok!</info>");
                    }
                }
            }
        }
    }

    /**
     *
     * @param type $filename
     * @param type $bundlename
     * @param type $locale
     * @param type $domain
     * @return boolean
     */
    protected function exportContents($filename, $bundlename, $locale,
            $domain = "messages")
    {
        $bundles = $this->getContainer()->getParameter('translator_bundles');
        $dirBundle = $bundles[$bundlename];
        $dm = $this->getContainer()->get('doctrine');
        $translator_format = $this->getContainer()
                ->getParameter('translator_format');
        $translation_entries = $dm
                ->getRepository('WebTranslatorBundle:Translation')
                ->findBy(
                        array('bundlename' => $bundlename, 'domain' => $domain));
        $messages_to_dump = array();
        foreach ($translation_entries as $specific_message) {
            $messages = $specific_message->getMessages();

            foreach ($messages as $message) {
                $specific_locale = $message->getLocale();
                $translated_message = $message->getMessage();
                $trimmed_msg = \trim($translated_message);
                if (!empty($trimmed_msg)
                        && $specific_locale === $locale) {

                    $messages_to_dump[$domain] = array();
                    $messages_to_dump[$domain][$specific_message
                            ->getTranslationKey()] = $translated_message;
                }
            }

        }

        ksort($messages_to_dump[$domain]);
        if (count($messages_to_dump[$domain]) > 0) {
            $dumper = Dumper::create($translator_format);
            /*
             * @todo yml dumper
             */
            //$dumper->dump($messages_to_dump, $filename);
            $messageCatalogue = new MessageCatalogue($locale, $messages_to_dump);
            $dumper
                    ->dump($messageCatalogue,
                            array(
                                    'path' => $dirBundle.'/Resources/translations'));
            return true;
        } else {
            return false;
        }
    }
}

