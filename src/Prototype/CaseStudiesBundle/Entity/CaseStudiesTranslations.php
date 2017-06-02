<?php

namespace Prototype\CaseStudiesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="casestudies_translations", indexes={
 *      @ORM\Index(name="casestudies_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\CaseStudiesRepository")
 */
class CaseStudiesTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
