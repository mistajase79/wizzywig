<?php

namespace Prototype\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Prototype\PageBundle\Custom\Custom;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ImageManager

 */
class ImageManager
{

    /**
	 * @Assert\File(
     *     mimeTypes = {"image/jpeg", "image/png", "image/gif"},
     *     mimeTypesMessage = "Please upload a valid image (jpeg, png, gif)"
     * )
     */
    public $imageUpload;

    private $image;

    private $thumbnail;

    private $filesize;

    private $filePath;


    public function resizeImage($image)
    {
        if (null === $this->imageUpload) {
            return;
        }

        $folder = getcwd().$this->getFilePath();
        //echo $folder;
        $customfunctions = new Custom();
        $newname = $customfunctions->generateFilename($folder, $this->imageUpload->getClientOriginalName());

        //echo $newname;

        $this->setImage($newname);
        $this->setThumbnail("tn_".$this->getImage());

        $this->imageUpload->move($folder,$this->getImage());
        copy($folder.$this->getImage(), $folder.$this->getThumbnail());

        // $image->open($folder.$this->getImage())
        //     ->zoomCrop(420, 420)
        //     ->save($folder.$this->getImage(), 'jpg', 80);

        $segment = explode('.',$this->getImage());
        $ext = strtolower(end($segment));

        switch ($ext) {
            case "png":
                $image->open($folder.$this->getThumbnail())
                    ->cropResize(105, 105)->fixOrientation()
                    ->fillBackground('transparent')
                    ->save($folder.$this->getThumbnail(), 'png', 80);
            case "gif":
                $image->open($folder.$this->getThumbnail())
                    ->cropResize(105, 105)->fixOrientation()
                    ->fillBackground('transparent')
                    ->save($folder.$this->getThumbnail(), 'gif', 80);
                break;
            case "jpg":
                $image->open($folder.$this->getThumbnail())
                    ->cropResize(105, 105)->fixOrientation()
                    ->fillBackground('#fff')
                    ->save($folder.$this->getThumbnail(), 'jpg', 80);
                break;
            default:
                $image->open($folder.$this->getThumbnail())
                    ->cropResize(105, 105)->fixOrientation()
                    ->fillBackground('#fff')
                    ->save($folder.$this->getThumbnail(), 'jpg', 80, '#fff');
        }


    }

    /**
     * Set filePath
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get filePath
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }


    /**
     * Set image
     *
     * @param string $image
     * @return Posts
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
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return Posts
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
    * Set filesize.
    *
    * @param float $filesize
    */
   public function setFilesize($filesize)
   {
       $this->filesize = $filesize;
   }

   /**
    * Get filesize.
    *
    * @return float
    */
   public function getFilesize()
   {
       return $this->filesize;
   }


}
