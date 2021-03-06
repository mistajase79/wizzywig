<?php
namespace Prototype\PageBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="Prototype\PageBundle\Repository\PageRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
*     fields={"slug"},
*     errorPath="slug",
*     message="A page has already be created with that title."
* )
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\PageBundle\Entity\PageTranslations")
 */
class Page implements Translatable
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /**
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
	 * @Assert\NotBlank(message="The title should not be blank")
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

	/**
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
	 * @Assert\NotBlank(message="The menu title should not be blank")
     * @ORM\Column(name="navtitle", type="string", length=128)
     */
    private $navtitle;

    /**
     * @ORM\Column(name="viewable_from", type="datetime")
     */
    private $viewable_from;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(name="url", type="string", length=128, nullable=true)
     */
    private $url;

	/**
	 * @Gedmo\Versioned
	 * @Gedmo\Translatable
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

	/**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

	/**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;


   /**
	* @Gedmo\Blameable(on="update")
	* @Gedmo\Blameable(on="create")
	* @ORM\ManyToOne(targetEntity="Prototype\UserBundle\Entity\User", inversedBy="pagesupdated")
	* @ORM\JoinColumn(name="update_by", referencedColumnName="id")
	*/
   private $updatedBy;

   /**
   * @ORM\ManyToOne(targetEntity="Page")
   * @ORM\JoinColumn(name="parent", unique=false, referencedColumnName="id", nullable=true)
   */
  private $parent;

  /**
   * @ORM\Column(type="boolean", nullable=false)
   */
  private $active = true;
  /**
	* @ORM\Column(type="boolean", nullable=false)
   */
  private $deleted = false;

  /**
   * @Assert\NotBlank(message="You must select a template")
   * @ORM\ManyToOne(targetEntity="Prototype\PageBundle\Entity\Templates", inversedBy="pagetemplate")
   */
  private $template;

  /**
	* @var array
	* @ORM\Column(name="components", type="array", nullable=true)
	*/
	protected $components;

  /**
   * @var array
   * @ORM\Column(name="extraUrlsegments", type="array", nullable=true)
   */
   protected $extraUrlsegments;

	/**
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     * @ORM\Column(name="metatitle", type="string", length=128, nullable=true)
     */
    private $metatitle;

	/**
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     * @ORM\Column(name="metadescription", type="string", length=255, nullable=true)
     */
    private $metadescription;

	/**
    * @ORM\OneToMany(targetEntity="Prototype\MenuBundle\Entity\MenuItems", mappedBy="pageId")
    */
	private $menupage;


    /**
     * @ORM\ManyToMany(targetEntity="Prototype\SliderBundle\Entity\Slider", mappedBy="pages")
     */
    private $slider;

   /**
    * @var array
    * @ORM\Column(name="htmlblocks", type="array", nullable=true)
    */
    protected $htmlblocks;




	public function __toString(){
	  return $this->getTitle();
	}


	public function setTranslatableLocale($locale)
   {
	   $this->locale = $locale;
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
     * Set title
     *
     * @param string $title
     *
     * @return Page
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
     * Set navtitle
     *
     * @param string $navtitle
     *
     * @return Page
     */
    public function setNavtitle($navtitle)
    {
        $this->navtitle = $navtitle;

        return $this;
    }

    /**
     * Get navtitle
     *
     * @return string
     */
    public function getNavtitle()
    {
        return $this->navtitle;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Page
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Page
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Page
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }



    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Page
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
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return Page
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     *
     * @return Page
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

	/**
     * Get updatedBy
     *
     * @return \Prototype\UserBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }


    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Page
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }



    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set parent
     *
     * @param \Prototype\PageBundle\Entity\Page $parent
     * @return Page
     */
    public function setParent(\Prototype\PageBundle\Entity\Page $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Prototype\PageBundle\Entity\Page
     */
    public function getParent()
    {
        return $this->parent;
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




    /**
     * Set template
     *
     * @param \Prototype\PageBundle\Entity\Templates $template
     * @return Page
     */
    public function setTemplate(\Prototype\PageBundle\Entity\Templates $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Prototype\PageBundle\Entity\Templates
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set components
     *
     * @param array $components
     * @return Page
     */
    public function setComponents($components)
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Get components
     *
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }


	/**
     * Set extraUrlsegments
     *
     * @param array $extraUrlsegments
     * @return Page
     */
    public function setExtraUrlsegments($extraUrlsegments)
    {
        $this->extraUrlsegments = $extraUrlsegments;

        return $this;
    }

    /**
     * Get extraUrlsegments
     *
     * @return array
     */
    public function getExtraUrlsegments()
    {
        return $this->extraUrlsegments;
    }


    /**
     * Set metatitle
     *
     * @param string $metatitle
     * @return Page
     */
    public function setMetatitle($metatitle)
    {
        $this->metatitle = $metatitle;

        return $this;
    }

    /**
     * Get metatitle
     *
     * @return string
     */
    public function getMetatitle()
    {
        return $this->metatitle;
    }

    /**
     * Set metadescription
     *
     * @param string $metadescription
     * @return Page
     */
    public function setMetadescription($metadescription)
    {
        $this->metadescription = $metadescription;

        return $this;
    }

    /**
     * Get metadescription
     *
     * @return string
     */
    public function getMetadescription()
    {
        return $this->metadescription;
    }



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menupage = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set viewable_from
     *
     * @param \DateTime $viewableFrom
     * @return Page
     */
    public function setViewableFrom($viewableFrom)
    {
        $this->viewable_from = $viewableFrom;

        return $this;
    }

    /**
     * Get viewable_from
     *
     * @return \DateTime
     */
    public function getViewableFrom()
    {
        return $this->viewable_from;
    }

    /**
     * Add menupage
     *
     * @param \Prototype\MenuBundle\Entity\MenuItems $menupage
     * @return Page
     */
    public function addMenupage(\Prototype\MenuBundle\Entity\MenuItems $menupage)
    {
        $this->menupage[] = $menupage;

        return $this;
    }

    /**
     * Remove menupage
     *
     * @param \Prototype\MenuBundle\Entity\MenuItems $menupage
     */
    public function removeMenupage(\Prototype\MenuBundle\Entity\MenuItems $menupage)
    {
        $this->menupage->removeElement($menupage);
    }

    /**
     * Get menupage
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenupage()
    {
        return $this->menupage;
    }





    /**
     * Add slider
     *
     * @param \Prototype\SliderBundle\Entity\Slider $slider
     * @return Page
     */
    public function addSlider(\Prototype\SliderBundle\Entity\Slider $slider)
    {
        $this->slider[] = $slider;

        return $this;
    }

    /**
     * Remove slider
     *
     * @param \Prototype\SliderBundle\Entity\Slider $slider
     */
    public function removeSlider(\Prototype\SliderBundle\Entity\Slider $slider)
    {
        $this->slider->removeElement($slider);
    }

    /**
     * Get slider
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlider()
    {
        return $this->slider;
    }


    /**
     * Set htmlblocks
     *
     * @param array $htmlblocks
     * @return Page
     */
    public function setHtmlblocks($htmlblocks)
    {
        $this->htmlblocks = $htmlblocks;

        return $this;
    }

    /**
     * Get htmlblocks
     *
     * @return array
     */
    public function getHtmlblocks()
    {
        return $this->htmlblocks;
    }
}
