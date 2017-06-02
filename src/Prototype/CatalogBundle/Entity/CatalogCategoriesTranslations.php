<?php

namespace Prototype\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="catalog_categories_translations", indexes={
 *      @ORM\Index(name="catalog_categories_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\CatalogRepository")
 */
class CatalogCategoriesTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
