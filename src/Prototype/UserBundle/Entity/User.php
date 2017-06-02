<?php
namespace Prototype\UserBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="Users")
 * @ORM\Entity(repositoryClass="UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\Length(max = 4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

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
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

	/**
     * @Assert\Image
     */
    public $imageUpload;
	/**
     * @ORM\Column(name="profile_image", type="string", nullable=true)
     */
    private $profile_image;

	/**
   * @ORM\OneToMany(targetEntity="Prototype\PageBundle\Entity\Page", mappedBy="updatedBy")
   */
	private $pagesupdated;

	/**
   * @ORM\OneToMany(targetEntity="Prototype\MenuBundle\Entity\Menu", mappedBy="updatedBy")
   */
	private $menuupdated;

	/**
     * @ORM\Column(name="emailresetkey", type="string", nullable=true)
     */
    private $emailresetkey;


    public function __construct()
    {
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    public function isAccountNonExpired()
   {
       return true;
   }

   public function isAccountNonLocked()
   {
       return true;
   }

   public function isCredentialsNonExpired()
   {
       return true;
   }

   public function isEnabled()
   {
       return $this->isActive;
   }

   public function getRoles()
   {
       return  $this->roles;
   }

  public function getPlainPassword()
  {
    return $this->plainPassword;
  }

  public function setPlainPassword($password)
  {
    $this->plainPassword = $password;
  }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }


    /**
     * Add pagesupdated
     *
     * @param \Prototype\PageBundle\Entity\Page $pagesupdated
     *
     * @return User
     */
    public function addPagesupdated(\Prototype\PageBundle\Entity\Page $pagesupdated)
    {
        $this->pagesupdated[] = $pagesupdated;

        return $this;
    }

    /**
     * Remove pagesupdated
     *
     * @param \Prototype\PageBundle\Entity\Page $pagesupdated
     */
    public function removePagesupdated(\Prototype\PageBundle\Entity\Page $pagesupdated)
    {
        $this->pagesupdated->removeElement($pagesupdated);
    }

    /**
     * Get pagesupdated
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPagesupdated()
    {
        return $this->pagesupdated;
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
     * Set name
     *
     * @param string $name
     *
     * @return User
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


	public function filePath(){

		return getcwd()."/userfiles/images/user/";
	}

	public function resizeImage($image)
	{
		if (null === $this->imageUpload) {
			return;
		}

		$this->setProfileImage(uniqid().'.'.$this->imageUpload->guessExtension());
		$folder = $this->filePath();
		// use the original file name here but you should
		// sanitize it at least to avoid any security issues

		// move takes the target directory and then the
		// target filename to move to
		$this->imageUpload->move(
		   $folder,
		   $this->getProfileImage()
		);

		$image->open($folder.$this->getProfileImage())
			->zoomCrop(160, 160)
			->save($folder.$this->getProfileImage(), 'jpg', 80);

    }

    /**
     * Set profileImage
     *
     * @param string $profileImage
     *
     * @return User
     */
    public function setProfileImage($profileImage)
    {
        $this->profile_image = $profileImage;

        return $this;
    }

    /**
     * Get profileImage
     *
     * @return string
     */
    public function getProfileImage()
    {
		if( $this->profile_image == ""){
			return "/control/user-placeholder.png";
		}else{
			return "/userfiles/images/user/".$this->profile_image;
		}

    }

	/**
     * Set emailresetkey
     *
     * @param string $emailresetkey
     *
     * @return User
     */
    public function setEmailresetkey($emailresetkey)
    {
        $this->emailresetkey = $emailresetkey;

        return $this;
    }

    /**
     * Get emailresetkey
     *
     * @return string
     */
    public function getEmailresetkey()
    {
        return $this->emailresetkey;
    }



}
