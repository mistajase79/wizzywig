<?php

namespace Prototype\SliderBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * Slider
 *
 * @ORM\Table(name="slider_images")
 * @ORM\Entity(repositoryClass="Prototype\SliderBundle\Repository\SliderRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\SliderBundle\Entity\SliderImagesTranslations")
 */
class SliderImages implements Translatable
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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="html", type="text", length=255, nullable=true)
     */
    private $html;

    /**
     * @var integer
     * @ORM\Column(name="position", type="integer", length=2, nullable=false)
     */
    private $position;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
      * @Gedmo\Timestampable(on="update")
      * @ORM\Column(type="datetime")
      */
    private $updatedAt;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active = true;
    /**
      * @ORM\Column(type="boolean", nullable=false)
     */
    private $deleted = false;

    /**
    * @ORM\ManyToOne(targetEntity="Slider", inversedBy="images")
    * @ORM\JoinColumn(name="slider_id", referencedColumnName="id")
    */
   private $slider;


    public function __toString(){
      return $this->getTitle();
    }

    public function getFilePath(){
        return "userfiles/images/slider";
    }

    public function getFullImagePath()
    {
        return $this->getFilePath().'/'.$this->image;
    }

    public function setTranslatableLocale($locale){
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
     * @return SliderImages
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
     * Set html
     *
     * @param string $html
     * @return SliderImages
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
     * Set image
     *
     * @param string $image
     * @return SliderImages
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
     * Set link
     *
     * @param string $link
     * @return SliderImages
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return SliderImages
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return SliderImages
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return SliderImages
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
     * @return SliderImages
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
     * Set images
     *
     * @param \Prototype\SliderBundle\Entity\Slider $images
     * @return SliderImages
     */
    public function setImages(\Prototype\SliderBundle\Entity\Slider $images = null)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return \Prototype\SliderBundle\Entity\Slider
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return SliderImages
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set slider
     *
     * @param \Prototype\SliderBundle\Entity\Slider $slider
     * @return SliderImages
     */
    public function setSlider(\Prototype\SliderBundle\Entity\Slider $slider = null)
    {
        $this->slider = $slider;
    
        return $this;
    }

    /**
     * Get slider
     *
     * @return \Prototype\SliderBundle\Entity\Slider 
     */
    public function getSlider()
    {
        return $this->slider;
    }
}
