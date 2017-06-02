<?php

namespace Prototype\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="catalog_products_translations", indexes={
 *      @ORM\Index(name="catalog_products_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\CatalogRepository")
 */
class CatalogProductsTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
