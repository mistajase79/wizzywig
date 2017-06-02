<?php

namespace Prototype\MeetTheTeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="meettheteam_translations", indexes={
 *      @ORM\Index(name="meettheteam_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\MeetTheTeamRepository")
 */
class TeamMemberTranslations extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
