<?php

namespace Web\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="messages")
 * @ORM\Entity
 */
class Message
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="locale", type="string", length=255, nullable=true)
     */
    protected $locale;

    /**
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Translation", inversedBy="messages")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $translation;

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
     * Set locale
     *
     * @param string $locale
     * @return Message
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set translation
     *
     * @param \Web\TranslatorBundle\Entity\Translation $translation
     * @return Message
     */
    public function setTranslation(\Web\TranslatorBundle\Entity\Translation $translation = null)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation
     *
     * @return \Web\TranslatorBundle\Entity\Translation
     */
    public function getTranslation()
    {
        return $this->translation;
    }
}