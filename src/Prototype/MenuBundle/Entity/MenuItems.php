<?php

namespace Prototype\MenuBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Prototype\PageBundle\Custom\Custom;

/**
 * News
 *
 * @ORM\Table(name="menuitems")
 * @ORM\Entity(repositoryClass="Prototype\MenuBundle\Repository\MenuRepository")
 */
class MenuItems
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
  * @ORM\ManyToOne(targetEntity="Prototype\MenuBundle\Entity\Menu", inversedBy="menu_items")
  * @ORM\JoinColumn(name="menuId", referencedColumnName="id")
  */
    private $menuId;

    /**
  * @ORM\ManyToOne(targetEntity="Prototype\PageBundle\Entity\Page", inversedBy="menupage")
  * @ORM\JoinColumn(name="pageId", referencedColumnName="id")
  */
    private $pageId;

    /**
     * @var string
     * @ORM\Column(name="name_override", type="string", length=128, nullable=true)
     */
    private $name_override;

    /**
     * @var integer
     * @ORM\Column(name="menu_item_id", type="integer", length=5, nullable=true)
     */
    private $menu_item_id;

    /**
     * @var integer
     * @ORM\Column(name="menu_parent_id", type="integer", length=5, nullable=true)
     */
    private $menu_parent_id;

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
    private $active = true;
    /**
      * @ORM\Column(type="boolean", nullable=false)
     */
    private $deleted = false;




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
     * Set menu_item_id
     *
     * @param integer $menuItemId
     * @return MenuItems
     */
    public function setMenuItemId($menuItemId)
    {
        $this->menu_item_id = $menuItemId;

        return $this;
    }

    /**
     * Get menu_item_id
     *
     * @return integer
     */
    public function getMenuItemId()
    {
        return $this->menu_item_id;
    }

    /**
     * Set menu_parent_id
     *
     * @param integer $menuParentId
     * @return MenuItems
     */
    public function setMenuParentId($menuParentId)
    {
        $this->menu_parent_id = $menuParentId;

        return $this;
    }

    /**
     * Get menu_parent_id
     *
     * @return integer
     */
    public function getMenuParentId()
    {
        return $this->menu_parent_id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return MenuItems
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
     * @return MenuItems
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
     * @return MenuItems
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
     * @return MenuItems
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
     * Set menuId
     *
     * @param \Prototype\MenuBundle\Entity\Menu $menuId
     * @return MenuItems
     */
    public function setMenuId(\Prototype\MenuBundle\Entity\Menu $menuId = null)
    {
        $this->menuId = $menuId;

        return $this;
    }

    /**
     * Get menuId
     *
     * @return \Prototype\MenuBundle\Entity\Menu
     */
    public function getMenuId()
    {
        return $this->menuId;
    }

    /**
     * Set pageId
     *
     * @param \Prototype\PageBundle\Entity\Page $pageId
     * @return MenuItems
     */
    public function setPageId(\Prototype\PageBundle\Entity\Page $pageId = null)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId
     *
     * @return \Prototype\PageBundle\Entity\Page
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set name_override
     *
     * @param string $nameOverride
     * @return MenuItems
     */
    public function setNameOverride($nameOverride)
    {
        $this->name_override = $nameOverride;

        return $this;
    }

    /**
     * Get name_override
     *
     * @return string
     */
    public function getNameOverride()
    {
        return $this->name_override;
    }
}
