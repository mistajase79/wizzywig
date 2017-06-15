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
 * @ORM\Table(name="blog_categories")
 * @ORM\Entity(repositoryClass="Prototype\BlogBundle\Repository\BlogRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\BlogBundle\Entity\BlogTranslations")
 */
class BlogCategories implements Translatable
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
     * @var string
     * @Assert\NotBlank(message="Exert should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="exert", type="text", length=255)
     */
    private $exert;

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

    /**
    * @ORM\OneToMany(targetEntity="Prototype\BlogBundle\Entity\BlogPosts", mappedBy="categoryId")
    */
    private $blog_posts;


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
     * Constructor
     */
    public function __construct()
    {
        $this->blog_posts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return BlogCategories
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
     * @return BlogCategories
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
     * Set slug
     *
     * @param string $slug
     * @return BlogCategories
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
     * @return BlogCategories
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
     * @return BlogCategories
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
     * @return BlogCategories
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
     * @return BlogCategories
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
     * @return BlogCategories
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
     * Add blog_posts
     *
     * @param \Prototype\BlogBundle\Entity\BlogPosts $blogPosts
     * @return BlogCategories
     */
    public function addBlogPost(\Prototype\BlogBundle\Entity\BlogPosts $blogPosts)
    {
        $this->blog_posts[] = $blogPosts;

        return $this;
    }

    /**
     * Remove blog_posts
     *
     * @param \Prototype\BlogBundle\Entity\BlogPosts $blogPosts
     */
    public function removeBlogPost(\Prototype\BlogBundle\Entity\BlogPosts $blogPosts)
    {
        $this->blog_posts->removeElement($blogPosts);
    }

    /**
     * Get blog_posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlogPosts()
    {
        return $this->blog_posts;
    }
}
