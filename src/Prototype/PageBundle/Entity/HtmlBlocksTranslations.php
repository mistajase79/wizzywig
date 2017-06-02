<?php

namespace Prototype\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="html_blocks_translations", indexes={
 *      @ORM\Index(name="html_blocks_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\HtmlBlocksRepository")
 */
class HtmlBlocksTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
