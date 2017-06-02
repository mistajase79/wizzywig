<?php

namespace Prototype\EnquiryBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * Enquires
 *
 * @ORM\Table(name="enquiry")
 * @ORM\Entity(repositoryClass="Prototype\EnquiryBundle\Repository\EnquiryRepository")
 * @Gedmo\Loggable
 */
class Enquiry implements Translatable
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
	 * @Assert\NotBlank(message="Your name should not be blank")
     * @Assert\Length( min = 3, minMessage = "Your name must be at least {{ limit }} characters long")
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @var string
	 * @Assert\NotBlank(message="Your Email should not be blank")
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.", checkMX = true )
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
	 * @Assert\NotBlank(message="Subject should not be blank")
     * @Assert\Length( min = 3, minMessage = "Subject must be at least {{ limit }} characters long")
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
	 * @Assert\NotBlank(message="Message should not be blank")
     * @Assert\Length( min = 10, minMessage = "Message must be at least {{ limit }} characters long")
     * @ORM\Column(name="message", type="text")
     */
    private $message;


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
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $viewed = false;
    /**
      * @ORM\Column(type="boolean", nullable=false)
     */
    private $deleted = false;


    public function __toString(){
      return $this->getSubject();
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
     * Set name
     *
     * @param string $name
     * @return Enquiry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Enquiry
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Enquiry
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Enquiry
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Enquiry
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
     * @return Enquiry
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
     * Set viewed
     *
     * @param boolean $viewed
     * @return Enquiry
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * Get viewed
     *
     * @return boolean
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Enquiry
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
