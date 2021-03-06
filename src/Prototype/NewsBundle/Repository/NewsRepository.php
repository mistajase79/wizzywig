<?php

namespace Prototype\NewsBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * NewsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NewsRepository extends EntityRepository
{

	public function findSlugWithLocale($locale = 'en', $slug)
   {
	   //Make a Select query
	   $qb = $this->createQueryBuilder('a');
	   $qb->select('a');
	   $qb->where('a.slug = :slug');
	   $qb->setParameter('slug', $slug);

	   // Use Translation Walker
	   $query = $qb->getQuery();
	   $query->setHint(
		   \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
		   'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
	   );
	   // Force the locale
	   $query->setHint(
		   \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
		   $locale
	   );
	   $query->getOneOrNullResult();

	   $results = $query->getResult();
	   if($results != null){ $results = $results[0];}
	   return $results;
   }
}
