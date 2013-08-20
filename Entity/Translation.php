<?php

namespace Web\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="translations")
 * @ORM\Entity
 */
class Translation
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="translation_key", type="string", length=255, nullable=true)
     */
    protected $translation_key;

    /**
     * @ORM\Column(name="domain", type="string", length=255, nullable=true)
     */
    protected $domain;

    /**
     * @ORM\Column(name="bundlename", type="string", length=255, nullable=true)
     */
    protected $bundlename;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="translation")
     **/

    protected $messages;

    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set translation_key
     *
     * @param string $translationKey
     * @return Translation
     */
    public function setTranslationKey($translationKey)
    {
        $this->translation_key = $translationKey;

        return $this;
    }

    /**
     * Get translation_key
     *
     * @return string
     */
    public function getTranslationKey()
    {
        return $this->translation_key;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return Translation
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set bundlename
     *
     * @param string $bundlename
     * @return Translation
     */
    public function setBundlename($bundlename)
    {
        $this->bundlename = $bundlename;

        return $this;
    }

    /**
     * Get bundlename
     *
     * @return string
     */
    public function getBundlename()
    {
        return $this->bundlename;
    }

    /**
     * Add messages
     *
     * @param \Web\TranslatorBundle\Entity\Message $messages
     * @return Translation
     */
    public function addMessage(\Web\TranslatorBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Web\TranslatorBundle\Entity\Message $messages
     */
    public function removeMessage(\Web\TranslatorBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}