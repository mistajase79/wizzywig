<?php

namespace Prototype\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="news_translations", indexes={
 *      @ORM\Index(name="news_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\NewsRepository")
 */
class NewsTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
