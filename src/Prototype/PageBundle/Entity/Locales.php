<?php

namespace Prototype\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Locales
 *
 * @ORM\Table(name="locales")
 * @ORM\Entity(repositoryClass="Prototype\PageBundle\Repository\LocalesRepository")
 */
class Locales
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=6, unique=true)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255)
     */
    private $language;


	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private $active = true;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return Locales
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
     * Set language
     *
     * @param string $language
     *
     * @return Locales
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }


	/**
	 * Set active
	 *
	 * @param boolean $active
	 * @return Page
	 */
	public function setActive($active)
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive()
	{
		return $this->active;
	}

}
