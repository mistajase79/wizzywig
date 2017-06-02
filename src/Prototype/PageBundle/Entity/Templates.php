<?php

namespace Prototype\PageBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Templates
 *
 * @ORM\Table(name="templates")
 * @ORM\Entity(repositoryClass="Prototype\PageBundle\Repository\PageRepository")
 */
class Templates
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

	/**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

	/**
     * @var string
     *
     * @ORM\Column(name="templatefile", type="string", length=255)
     */
    private $templatefile;

	/**
	 * @Assert\Image
	 */
	public $imageUpload;

	/**
	 * @var string
	 * @ORM\Column(name="image", type="string", length=255, nullable=true)
	 */
	private $image;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private $active = true;
	/**
   * @ORM\Column(type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
     * @var int
     * @ORM\Column(name="number_of_html", type="integer", nullable=true)
     */
    private $numberOfHtml;

	/**
     * @var int
     * @ORM\Column(name="number_of_components", type="integer", nullable=true)
     */
    private $numberOfComponents;

	/**
	 * @ORM\OneToMany(targetEntity="Prototype\PageBundle\Entity\Page", mappedBy="template")
	 */
	private $pagetemplate;

    /**
     * @ORM\Column(name="inherited_bundle_name", type="string", length=255, nullable=true)
     */
    private $inherited_bundle_name;


	public function __toString(){

		return $this->getTitle();

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
     * @return Templates
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
     * Set description
     *
     * @param string $description
     * @return Templates
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set templatefile
     *
     * @param string $templatefile
     * @return Templates
     */
    public function setTemplatefile($templatefile)
    {
        $this->templatefile = $templatefile;

        return $this;
    }

    /**
     * Get templatefile
     *
     * @return string
     */
    public function getTemplatefile()
    {
        return $this->templatefile;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Templates
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Templates
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
     * @return Templates
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

    /**
     * Set numberOfHtml
     *
     * @param integer $numberOfHtml
     * @return Templates
     */
    public function setNumberOfHtml($numberOfHtml)
    {
        $this->numberOfHtml = $numberOfHtml;

        return $this;
    }

    /**
     * Get numberOfHtml
     *
     * @return integer
     */
    public function getNumberOfHtml()
    {
        return $this->numberOfHtml;
    }

    /**
     * Set numberOfComponents
     *
     * @param integer $numberOfComponents
     * @return Templates
     */
    public function setNumberOfComponents($numberOfComponents)
    {
        $this->numberOfComponents = $numberOfComponents;

        return $this;
    }

    /**
     * Get numberOfComponents
     *
     * @return integer
     */
    public function getNumberOfComponents()
    {
        return $this->numberOfComponents;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pagetemplate = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add pagetemplate
     *
     * @param \Prototype\PageBundle\Entity\Page $pagetemplate
     * @return Templates
     */
    public function addPagetemplate(\Prototype\PageBundle\Entity\Page $pagetemplate)
    {
        $this->pagetemplate[] = $pagetemplate;

        return $this;
    }

    /**
     * Remove pagetemplate
     *
     * @param \Prototype\PageBundle\Entity\Page $pagetemplate
     */
    public function removePagetemplate(\Prototype\PageBundle\Entity\Page $pagetemplate)
    {
        $this->pagetemplate->removeElement($pagetemplate);
    }

    /**
     * Get pagetemplate
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPagetemplate()
    {
        return $this->pagetemplate;
    }


    /**
     * Set inherited_bundle_name
     *
     * @param string $inherited_bundle_name
     *
     * @return Page
     */
    public function setInheritedBundleName($inherited_bundle_name)
    {
        $this->inherited_bundle_name = $inherited_bundle_name;

        return $this;
    }

    /**
     * Get inherited_bundle_name
     *
     * @return string
     */
    public function getInheritedBundleName()
    {
        return $this->inherited_bundle_name;
    }
}
