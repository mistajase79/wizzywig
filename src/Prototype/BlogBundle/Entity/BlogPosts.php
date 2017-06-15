<?php

namespace Prototype\BlogBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * Blog
 *
 * @ORM\Table(name="blog_posts")
 * @ORM\Entity(repositoryClass="Prototype\BlogBundle\Repository\BlogRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\BlogBundle\Entity\BlogTranslations")
 */
class BlogPosts implements Translatable
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
     * @Assert\NotBlank(message="The title should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
  * @ORM\ManyToOne(targetEntity="Prototype\BlogBundle\Entity\BlogCategories", inversedBy="blog_posts")
  * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
  */
    private $categoryId;

    /**
     * @var string
     * @Assert\NotBlank(message="Exert should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="exert", type="text")
     */
    private $exert;

    /**
     * @var string
     * @Assert\NotBlank(message="Content should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="content", type="text" )
     */
    private $content;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_posted", type="datetime")
     * @ORM\Column(type="datetime")
     */
    private $datePosted;

    /**
    * @Gedmo\Translatable
    * @Gedmo\Slug(fields={"title"}, separator="-", updatable=true)
    * @ORM\Column(length=255, unique=true)
    */
    private $slug;

    /**
     * @var string
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

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


    public function __toString(){
      return $this->getTitle();
    }

    // Used by the imagemanager
    public function getFilePath(){
        return "userfiles/images/blog";
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
     * @return Blog
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
     * Set exert
     *
     * @param string $exert
     * @return Blog
     */
    public function setExert($exert)
    {
        $this->exert = $exert;

        return $this;
    }

    /**
     * Get exert
     *
     * @return string
     */
    public function getExert()
    {
        return $this->exert;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Blog
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
     * Set datePosted
     *
     * @param \DateTime $datePosted
     * @return Blog
     */
    public function setDatePosted($datePosted)
    {
        $this->datePosted = $datePosted;

        return $this;
    }

    /**
     * Get datePosted
     *
     * @return \DateTime
     */
    public function getDatePosted()
    {
        return $this->datePosted;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Blog
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
     * Set image
     *
     * @param string $image
     * @return Blog
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Blog
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
     * @return Blog
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
     * @return Blog
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
     * @return Blog
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
     * Set categoryId
     *
     * @param \Prototype\BlogBundle\Entity\BlogCategories $categoryId
     * @return Blog
     */
    public function setCategoryId(\Prototype\BlogBundle\Entity\BlogCategories $categoryId = null)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return \Prototype\BlogBundle\Entity\BlogCategories
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
}
