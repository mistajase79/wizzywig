<?php

namespace Prototype\NewsBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * News
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="Prototype\NewsBundle\Repository\NewsRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\NewsBundle\Entity\NewsTranslations")
 */
class News implements Translatable
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
	 * @Assert\NotBlank(message="The headline should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;


    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    private $subtitle;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="article", type="text", nullable=true)
     */
    private $article;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(name="exert", type="text", nullable=true)
     */
    private $exert;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date", type="date")
     */
    private $publishDate;


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
     * @var string
	 * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;

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
  * @Gedmo\Blameable(on="update")
  * @Gedmo\Blameable(on="create")
  * @ORM\ManyToOne(targetEntity="Prototype\UserBundle\Entity\User", inversedBy="pagesupdated")
  * @ORM\JoinColumn(name="update_by", referencedColumnName="id")
  */
	private $updatedBy;


    public function __toString(){
        return $this->getTitle();
    }

    public function setTranslatableLocale($locale){
        $this->locale = $locale;
    }



	public function getFilePath(){
  		return "userfiles/images/news";
  	}

    /**
     * Get fullimage
     *
     * @return string
     */
    public function getFullImagePath()
    {
        return $this->getFilePath().'/'.$this->image;
    }


    //this function has now been replaced with the image manager
	public function resizeImage($image)
	{
		if (null === $this->imageUpload) {
			return;
		}

		$folder = $this->filePath();
		$customfunctions = new Custom();
        $newname = $customfunctions->generateFilename($folder, $this->imageUpload->getClientOriginalName());

		$this->setImage($newname);
		$this->setThumbnail("tn_".$this->getImage());
		$folder = $this->filePath();

		$this->imageUpload->move($folder,$this->getImage());
		copy($folder.$this->getImage(), $folder.$this->getThumbnail());

		$image->open($folder.$this->getImage())
			->zoomCrop(420, 420)
			->save($folder.$this->getImage(), 'jpg', 80);

		$image->open($folder.$this->getThumbnail())
			->zoomCrop(160, 160)
			->save($folder.$this->getThumbnail(), 'jpg', 80);

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
     * @return News
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
     * Set subtitle
     *
     * @param string $subtitle
     * @return News
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return News
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
     * Set article
     *
     * @param string $article
     * @return News
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return string
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set exert
     *
     * @param string $exert
     * @return News
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
     * Set publishDate
     *
     * @param \DateTime $publishDate
     * @return News
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return News
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
     * @return News
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
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return News
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return News
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
     * @return News
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
}
