<?php

namespace Prototype\PageBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * HtmlBlocks
 *
 * @ORM\Table(name="html_blocks")
 * @ORM\Entity(repositoryClass="Prototype\PageBundle\Repository\HtmlBlocksRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\PageBundle\Entity\HtmlBlocksTranslations")
 */
class HtmlBlocks implements Translatable
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
     * @Assert\NotBlank(message="The title/Indentifier should not be blank")
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
    * @Gedmo\Slug(fields={"title"}, separator="-", updatable=true)
    * @ORM\Column(length=255, unique=true)
    */
    private $slug;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="html", type="text", nullable=true)
     */
    private $html;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private $active = true;
	/**
   * @ORM\Column(type="boolean", nullable=false)
	 */
	private $deleted = false;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;


    public function __toString(){
        return $this->getTitle();
    }

    public function setTranslatableLocale($locale){
        $this->locale = $locale;
    }



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
     * Set title
     *
     * @param string $title
     *
     * @return HtmlBlocks
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return News
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }


    /**
     * Set html
     *
     * @param string $html
     *
     * @return HtmlBlocks
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
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

	    /**
	     * Set deleted
	     *
	     * @param boolean $deleted
	     * @return Page
	     */
	    public function setDeleted($deleted)
	    {
	        $this->deleted = $deleted;

	        return $this;
	    }

	    /**
	     * Get deleted
	     *
	     * @return boolean
	     */
	    public function getDeleted()
	    {
	        return $this->deleted;
	    }
}
