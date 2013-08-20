<?php

namespace Web\TranslatorBundle\Command;
// Message
use Web\TranslatorBundle\Entity\Translation;
use Web\TranslatorBundle\Loader\Loader;
use Web\TranslatorBundle\Entity\Message;

// Command stuff
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// Yaml...
//use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
//use Symfony\Component\Translation\Loader\YamlFileLoader;
// Doctrine/MongoDB
use Symfony\Component\HttpFoundation\Response;


class TranslatorImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        //
        $this->setName('nelm:translator:import')
                ->setDescription('Import localization data into mongoDB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $bundles = $this->getContainer()->getParameter('translator_bundles');
        $target_languages = $this->getContainer()
                ->getParameter('translator_target_languages');
        $main_language = $this->getContainer()
                ->getParameter('translator_main_language');
        $target_domains = $this->getContainer()
                ->getParameter('translator_target_domains');
        $translator_format = $this->getContainer()
                ->getParameter('translator_format');

        array_push($target_languages, $main_language);
        foreach ($bundles as $bundlename => $dir) {
            foreach ($target_languages as $locale) {
                foreach ($target_domains as $domain) {
                    $filename = "$dir/Resources/translations/$domain.$locale.$translator_format";
                    if (file_exists($filename)) {
                        $this
                                ->importContents($filename, $locale,
                                        $bundlename, $domain);
                    }
                }
            }
        }
    }

    /**
     * Import a localization file into database
     *
     * @param type $filename
     * @param type $locale
     * @param type $bundlename
     * @param type $domain
     */
    protected function importContents($filename, $locale, $bundlename,
            $domain = 'messages')
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $translator_format = $this->getContainer()
                ->getParameter('translator_format');
        $loader = Loader::create($translator_format);
        $locale_file_contents = $loader->load($filename, $locale, $domain);
        $repository = $locale_file_contents->all();

        // Import all the messages for this domain into the document manager
        foreach ($repository[$domain] as $translation_key => $translation) {
            // Check if it's already in?
            $translation_entry = $em
                    ->getRepository('WebTranslatorBundle:Translation')
                    ->findOneBy(
                            array('translation_key' => $translation_key,
                                    'domain' => $domain,
                                    'bundlename' => $bundlename));
            // Escape escaped double quotes (from our exportContents)
            $translation = str_replace('\"', '"', $translation);
            if (!empty($translation_entry)) {
                $msgs = $translation_entry->getMessages();

                $message = $em
                        ->getRepository('WebTranslatorBundle:Message')
                        ->findOneBy(
                                array('translation' => $translation_entry,
                                        'locale' => $locale));
                if (null === $message) {
                    $message = new Message();
                    $message->setTranslation($translation_entry);
                    $message->setLocale($locale);
                    $message->setMessage($translation);

                    $msgs->add($message);

                    $translation_entry->setMessages($msgs);

                    $em->persist($message);
                    $em->persist($translation_entry);
                }

            } else {
                $entry = new Translation();
                $entry->setTranslationKey($translation_key);
                $entry->setDomain($domain);
                $entry->setBundlename($bundlename);

                $message = new Message();
                $message->setTranslation($entry);
                $message->setLocale($locale);
                $message->setMessage($translation);

                $em->persist($message);
                $em->persist($entry);
            }
        }

        $em->flush();
    }
}
