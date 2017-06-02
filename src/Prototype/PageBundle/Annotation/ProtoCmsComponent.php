<?php
namespace Prototype\PageBundle\Annotation;

/**
 * @Annotation
 * Getters, setters and default values for the ProtoCmsComponent annotation
 */
class ProtoCmsComponent
{
    public $propertyName = null;
    public $componentType = 'standard';
    public $slug = null;
    public $routeName = null;
    public $active = true;
    public $bundle = null;
    public $slugEntity = null;

    public function __construct($options)
    {

        if (isset($options['value'])) {
            $options['propertyName'] = $options['value'];
            unset($options['value']);
        }
        //echo "<pre>".print_r($options, true)."</pre>";
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('PCGC ProtoCmsComponent annotation property "%s" does not exist - try correcting it or adding it to "Prototype\PageBundle\Annotation\ProtoCmsComponent" ', $key));
            }
            $this->$key = $value;
        }
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getComponentType()
    {
        return $this->componentType;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function getSlugEntity()
    {
        return $this->slugEntity;
    }

}
