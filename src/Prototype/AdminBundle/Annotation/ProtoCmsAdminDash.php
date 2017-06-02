<?php
namespace Prototype\AdminBundle\Annotation;

/**
 * @Annotation
 */
class ProtoCmsAdminDash
{
	public $propertyName = null;
	public $active = true;
	public $routeName = null;
	public $icon = "glyphicon-wrench";
	public $parentRouteName = null;
	public $role = "ROLE_ADMIN";
    public $dontLink = false;
    public $menuPosition = 99999;


    public function __construct($options)
    {
        if (isset($options['value'])) {
            $options['propertyName'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('PCGC ProtoCmsAdminDash annotation property "%s" does not exist - try correcting it or adding it to "Prototype\AdminBundle\Annotation\ProtoCmsAdminDash" ', $key));
            }

            $this->$key = $value;
        }
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

	public function getActive()
    {
        return $this->active;
    }

	public function getRouteName()
    {
        return $this->routeName;
    }

	public function getIcon()
    {
        return $this->icon;
    }

	public function getParentRouteName()
    {
        return $this->parentRouteName;
    }

	public function getRole()
    {
        return $this->role;
    }

    public function getDontLink()
    {
        return $this->dontLink;
    }

    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

}
