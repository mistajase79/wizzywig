<?php

namespace Prototype\CaseStudiesBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * CaseStudies
 *
 * @ORM\Table(name="casestudies")
 * @ORM\Entity(repositoryClass="Prototype\CaseStudiesBundle\Repository\CaseStudiesRepository")
 * @Gedmo\Loggable
 * @Gedmo\TranslationEntity(class="Prototype\CaseStudiesBundle\Entity\CaseStudiesTranslations")
 */
class CaseStudies implements Translatable
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
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    private $subtitle;

    /**
     * @var string
     * @Assert\NotBlank(message="Content should not be blank")
     * @Gedmo\Translatable
     * @ORM\Column(name="content", type="text", length=255, nullable=true)
     */
    private $content;

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
    * @Assert\File(mimeTypes={
    *               "application/pdf",
    *               "application/msword",
    *               "application/vnd.ms-excel",
    *               "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    *               "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    * }, mimeTypesMessage = "Please upload a valid file (PDF,DOC,XLS,DOCX,XLSX)")
    *
    */
    private $fileUpload;

    /**
    * @Gedmo\Translatable
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
     private $file;

    public function __toString(){
      return $this->getTitle();
    }

    public function getFilePath(){
        return "userfiles/images/casestudies";
    }

    public function getFullImagePath()
    {
        return $this->getFilePath().'/'.$this->image;
    }

    public function setTranslatableLocale($locale){
       $this->locale = $locale;
    }


    public function uploadFile()
    {
        if (null === $this->fileUpload) {
            return;
        }

        $folder = getcwd().'/userfiles/files/casestudies';
        $customfunctions = new Custom();
        $newname = $customfunctions->generateFilename($folder, $this->fileUpload->getClientOriginalName());
        $file = $this->getFileUpload();
        $this->setFile($newname);
        $file->move($folder,$this->getFile());

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
     * @return CaseStudies
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
     * @return CaseStudies
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
     * Set content
     *
     * @param string $content
     * @return CaseStudies
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
     * Set slug
     *
     * @param string $slug
     * @return CaseStudies
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
     * @return CaseStudies
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
     * @return CaseStudies
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
     * @return CaseStudies
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
     * @return CaseStudies
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
     * @return CaseStudies
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
     * Set fileUpload
     *
     * @param string $fileUpload
     * @return CaseStudies
     */
    public function setFileUpload($fileUpload)
    {
        $this->fileUpload = $fileUpload;

        return $this;
    }

    /**
     * Get fileUpload
     *
     * @return string
     */
    public function getFileUpload()
    {
        return $this->fileUpload;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return CaseStudies
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }


}
