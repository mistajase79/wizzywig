<?php

namespace Prototype\AdminBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;


class TranslationService extends Controller
{

	public function __construct($kernel,RegistryInterface $doctrine){
	    $this->container = $kernel->getContainer();
		$this->doctrine = $doctrine;
	}

	// this function looks for the gedmo translation of the given entity then
	// returns the entity fields
	public function findAndSetTranslatableEntityData($entity, $formData){
		////////////////////////////////////////////
		// translation script
		$reader = new AnnotationReader();
		$reflClass = new \ReflectionClass($entity);
		$methods = $reflClass->getMethods();
		$refProp = $reflClass->getProperties();
		$translatableMetadataArray = array();

		foreach ($refProp as $props) {
			$propertyAnnotations = $reader->getPropertyAnnotations($props);
			foreach ($propertyAnnotations as $field) {
				if(get_class($field) == "Gedmo\Mapping\Annotation\Translatable"){
					//print_r($propertyAnnotations);
					foreach ($propertyAnnotations as $field) {
						if(get_class($field) == "Doctrine\ORM\Mapping\Column"){
							$translatableMetadataArray[] = $field->name;
						}
					}
				}
			}
		}

		// save translation
		foreach ($translatableMetadataArray as $field) {
			if($field !=""){
				$setterMethod = "set".ucwords($field);
				$entity->$setterMethod($formData[$field]);
			}
		}

		return $entity;
	}


	public function setDataOnTranslatableFields($translatableMetadataArray, $entity, $formData){

		return $entity;
	}


	//creates array with all translations for a single entity used for form choiceTypes
	public function fetchAvailableTranslations($entity, $allLocales){
		$repository = $this->doctrine->getRepository('Gedmo\Translatable\Entity\Translation');
		$translations = $repository->findTranslations($entity);

		$newArray = array();
		foreach($allLocales as $locale){
			//echo "<pre>".print_r($locale, true)."</pre>";
			$newArray[$locale->getLocale()] = array('locale'=>$locale->getLocale(), 'disabled'=>false, 'language'=>$locale->getLanguage());
			foreach($translations as $translocale => $fields ){
				if($locale->getLocale() == $translocale){
					$newArray[$locale->getLocale()] = array('locale'=>$locale->getLocale(), 'disabled'=>true, 'language'=>$locale->getLanguage());
				}
			}
		}

		$localsAndAttributesArray = array();
		$localsAndAttributesArray['attributes'] = array();
		foreach( $newArray as $locale){
			if($locale['disabled'] == true){

				$localsAndAttributesArray['locales'][$locale['language']." (".$locale['locale'].") - already exists"] = $locale['locale'];
				$localsAndAttributesArray['attributes'][$locale['language']." (".$locale['locale'].") - already exists"] =  array('disabled' => 'disabled');
			}else{
				$localsAndAttributesArray['locales'][$locale['language']." (".$locale['locale'].")"] = $locale['locale'];
			}
		}

		return $localsAndAttributesArray;


	}


}
