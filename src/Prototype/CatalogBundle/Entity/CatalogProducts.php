<?php

namespace Prototype\CatalogBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * Catalog
 *
 * @ORM\Table(name="catalog_products")
 * @ORM\Entity(repositoryClass="Prototype\CatalogBundle\Repository\CatalogRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\CatalogBundle\Entity\CatalogProductsTranslations")
 */
class CatalogProducts implements Translatable
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
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
	 * @Assert\NotBlank(message="The price should not be blank")
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $price;

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
    * @ORM\ManyToOne(targetEntity="CatalogCategories", inversedBy="products")
    * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
    */
   private $category;

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

    public function getFilePath(){
        return "userfiles/images/catalog/products";
    }

    public function getFullImagePath()
    {
        return $this->getFilePath().'/'.$this->image;
    }

    public function setTranslatableLocale($locale){
       $this->locale = $locale;
    }

    /**
     * Get fullSlugPath
     *
     * @return string
     */
    public function getFullSlugPath()
    {
        // this function generates a full url category path
        $url = array();
        $cat = $this->category;
        while($cat !=null){
            $cat = $cat->getParent();
            if($cat !=null){ $url[] = $cat->getSlug(); }
        }
        $urlrev = array_reverse($url);
        return "/".implode('/', $urlrev)."/".$this->category->getSlug()."/".$this->getSlug();
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
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * Set price
     *
     * @param string $price
     * @return CatalogProducts
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * @return CatalogProducts
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
     * Set category
     *
     * @param \Prototype\CatalogBundle\Entity\CatalogCategories $category
     * @return CatalogProducts
     */
    public function setCategory(\Prototype\CatalogBundle\Entity\CatalogCategories $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Prototype\CatalogBundle\Entity\CatalogCategories
     */
    public function getCategory()
    {
        return $this->category;
    }
}
