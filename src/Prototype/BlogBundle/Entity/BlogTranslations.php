<?php

namespace Prototype\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="blog_translations", indexes={
 *      @ORM\Index(name="blog_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\BlogRepository")
 */
class BlogTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
