<?php

namespace Prototype\SliderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="slider_translations", indexes={
 *      @ORM\Index(name="slider_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\SliderRepository")
 */
class SliderTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
