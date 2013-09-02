<?php

namespace Web\TranslatorBundle\Controller;
use Web\TranslatorBundle\Entity\Translation;
use Web\TranslatorBundle\Entity\Message;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TranslatorController extends Controller
{

    public function indexAction()
    {
        return $this->forward('WebTranslatorBundle:Translator:showBundles');
    }

    public function showBundlesAction()
    {
        $bundles = $this->container->getParameter('translator_bundles');
        asort($bundles);
        return $this
                ->render(
                        'WebTranslatorBundle:Translator:show_bundles.html.twig',
                        array('bundles' => $bundles));
    }

    public function showDomainsAction($bundle)
    {
        $target_bundles = $this->container->getParameter('translator_bundles');

        if (!isset($target_bundles[$bundle])) {
            return $this->forward('WebTranslatorBundle:Translator:showBundles');
        }

        $entries = $this->get('doctrine')
                ->getRepository('WebTranslatorBundle:Translation')
                ->findBy(array('bundlename' => $bundle));

        $domains = array();

        foreach ($entries as $specific_entry) {
            if (!in_array($specific_entry->getDomain(), $domains)) {
                array_push($domains, $specific_entry->getDomain());
            }
        }

        asort($domains);

        return $this
                ->render(
                        'WebTranslatorBundle:Translator:show_domains.html.twig',
                        array('bundlename' => $bundle, 'domains' => $domains));
    }

    public function showKeysAction($bundle, $domain)
    {
        $target_bundles = $this->container->getParameter('translator_bundles');
        if (!isset($target_bundles[$bundle])
                || !in_array($domain,
                        $this->container
                                ->getParameter('translator_target_domains'))) {
            return $this->forward('WebTranslatorBundle:Translator:showBundles');
        }

        $entries = $this->get('doctrine')
                ->getRepository('WebTranslatorBundle:Translation')
                ->findBy(array('bundlename' => $bundle, 'domain' => $domain));
        $translator_languages = $this->container
                ->getParameter('translator_target_languages');
        array_push($translator_languages,
                $this->container->getParameter('translator_main_language'));

        $processed_entries = array();
        foreach ($entries as $specific_entry) {
            $languages = array();
            foreach ($specific_entry->getMessages() as $message) {
                $locale = $message->getLocale();
                array_push($languages, $locale);
            }
            $processed_entries[$specific_entry->getTranslationKey()] = array_diff(
                    $translator_languages, $languages);
        }

        $target_locales = $this->container
                ->getParameter('translator_target_languages');

        ksort($processed_entries);

        return $this
                ->render('WebTranslatorBundle:Translator:show_keys.html.twig',
                        array('bundlename' => $bundle, 'domain' => $domain,
                                'target_locales' => $target_locales,
                                'entries' => $processed_entries));
    }

    public function showKeysMissingLocaleAction($bundle, $domain, $locale)
    {
        $target_bundles = $this->container->getParameter('translator_bundles');
        if (!isset($target_bundles[$bundle])
                || !in_array($domain,
                        $this->container
                                ->getParameter('translator_target_domains'))
                || !in_array($locale,
                        $this->container
                                ->getParameter('translator_target_languages'))) {
            return $this->forward('WebTranslatorBundle:Translator:showBundles');
        }

        $entries = $this->get('doctrine')
                ->getRepository('WebTranslatorBundle:Translation')
                ->findBy(array('bundlename' => $bundle, 'domain' => $domain));

        $keys_to_show = array();
        foreach ($entries as $specific_entry) {
            $message = $this->get('doctrine')
                    ->getRepository('WebTranslatorBundle:Message')
                    ->findOneBy(
                            array('translation' => $specific_entry,
                                    'locale' => $locale));
            if (null === $message) {
                array_push($keys_to_show, $specific_entry->getTranslationKey());
            }
        }
        asort($keys_to_show);
        return $this
                ->render(
                        'WebTranslatorBundle:Translator:show_keys_missing_locale.html.twig',
                        array('bundlename' => $bundle, 'domain' => $domain,
                                'locale' => $locale, 'keys' => $keys_to_show));
    }

    public function showKeyAction($bundle, $domain, $key)
    {
        $target_bundles = $this->container->getParameter('translator_bundles');
        if (!isset($target_bundles[$bundle])
                || !in_array($domain,
                        $this->container
                                ->getParameter('translator_target_domains'))) {
            return $this->forward('WebTranslatorBundle:Translator:showBundles');
        }

        $entry_to_edit = $this->get('doctrine')
                ->getRepository('WebTranslatorBundle:Translation')
                ->findOneBy(
                        array('bundlename' => $bundle, 'domain' => $domain,
                                'translation_key' => $key));

        $main_language = $this->container
                ->getParameter('translator_main_language');
        $target_languages = $this->container
                ->getParameter('translator_target_languages');

        $messages = $entry_to_edit->getMessages();

        $main_message = $messages
                ->filter(
                        function ($item) use ($main_language)
                        {
                            return $item->getLocale() === $main_language;
                        });

        $main_message = $main_message->current();

        foreach ($target_languages as $target_locale) {
            $message = $this->get('doctrine')
                    ->getRepository('WebTranslatorBundle:Message')
                    ->findOneBy(
                            array('translation' => $entry_to_edit,
                                    'locale' => $target_locale));
            if (null === $message) {
                $message = new Message();
                $message->setTranslation($entry_to_edit);
                $message->setLocale($target_locale);
                $message->setMessage('');

                $messages->add($message);
            }
        }

        return $this
                ->render('WebTranslatorBundle:Translator:show_key.html.twig',
                        array('bundlename' => $bundle, 'domain' => $domain,
                                'key' => $key,
                                'main_language' => $main_language,
                                'main_message' => $main_message->getMessage(),
                                'messages' => $messages));
    }

    public function updateKeyAction(Request $request, $bundle, $domain, $key)
    {
        $target_bundles = $this->container->getParameter('translator_bundles');
        if (!isset($target_bundles[$bundle])
                || !in_array($domain,
                        $this->container
                                ->getParameter('translator_target_domains'))) {
            return $this->forward('WebTranslatorBundle:Translator:showBundles');
        }

        $dm = $this->get('doctrine');
        $entry_to_update = $dm
                ->getRepository('WebTranslatorBundle:Translation')
                ->findOneBy(
                        array('bundlename' => $bundle, 'domain' => $domain,
                                'translation_key' => $key));
        $target_languages = $this->container
                ->getParameter('translator_target_languages');

        $messages = $entry_to_update->getMessages();
        foreach ($target_languages as $target_locale) {
            $new_translation = $request->get($target_locale, null);
            $trimmed_translation = \trim($new_translation);

            if (!empty($trimmed_translation)) {
                $message = $this->get('doctrine')
                        ->getRepository('WebTranslatorBundle:Message')
                        ->findOneBy(
                                array('translation' => $entry_to_update,
                                        'locale' => $target_locale));

                if (null === $message) {
                    $message = new Message();

                }

                $message->setTranslation($entry_to_update);
                $message->setLocale($target_locale);
                $message->setMessage($trimmed_translation);

                $dm->getManager()->persist($message);
            }

        }

        $dm->getManager()->persist($entry_to_update);
        $dm->getManager()->flush();

        return $this
                ->forward('WebTranslatorBundle:Translator:showKey',
                        array('bundle' => $bundle, 'domain' => $domain,
                                'key' => $key));
    }
}

