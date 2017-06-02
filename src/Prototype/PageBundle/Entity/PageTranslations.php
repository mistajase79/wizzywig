<?php

namespace Prototype\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="page_translations", indexes={
 *      @ORM\Index(name="page_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\PageTranslationRepository")
 */
class PageTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
