<?php

namespace Prototype\SliderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="sliderimages_translations", indexes={
 *      @ORM\Index(name="sliderimages_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\SliderRepository")
 */
class SliderImagesTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
