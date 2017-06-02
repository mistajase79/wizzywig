<?php

namespace Prototype\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

///////////////////////////
// FIELD TYPES REFERENCE //
///////////////////////////

// Text fields
//const textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
//const emailType = 'Symfony\Component\Form\Extension\Core\Type\EmailType';
//const textareaType = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';
//const integerType = 'Symfony\Component\Form\Extension\Core\Type\IntegerType';
//const moneyType = 'Symfony\Component\Form\Extension\Core\Type\MoneyType';
//const numberType = 'Symfony\Component\Form\Extension\Core\Type\NumberType';
//const passwordType = 'Symfony\Component\Form\Extension\Core\Type\PasswordType';
//const percentType = 'Symfony\Component\Form\Extension\Core\Type\PercentType';
//const searchType = 'Symfony\Component\Form\Extension\Core\Type\SearchType';
//const urlType = 'Symfony\Component\Form\Extension\Core\Type\UrlType';
//const rangeType = 'Symfony\Component\Form\Extension\Core\Type\RangeType';

// Choice fields
//const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
//const entityType = 'Symfony\Component\Form\Extension\Core\Type\EntityType';
//const countryType = 'Symfony\Component\Form\Extension\Core\Type\CountryType';
//const languageType = 'Symfony\Component\Form\Extension\Core\Type\LanguageType';
//const localeType = 'Symfony\Component\Form\Extension\Core\Type\LocaleType';
//const timezoneType = 'Symfony\Component\Form\Extension\Core\Type\TimezoneType';
//const currencyType = 'Symfony\Component\Form\Extension\Core\Type\CurrencyType';

// Date and Time fields
//const dateType = 'Symfony\Component\Form\Extension\Core\Type\DateType';
//const dateTimeType = 'Symfony\Component\Form\Extension\Core\Type\DateTimeType';
//const timeType = 'Symfony\Component\Form\Extension\Core\Type\TimeType';
//const birthdayType = 'Symfony\Component\Form\Extension\Core\Type\BirthdayType';

// Others
//const checkboxType = 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';
//const radioType = 'Symfony\Component\Form\Extension\Core\Type\RadioType';
//const fileType = 'Symfony\Component\Form\Extension\Core\Type\FileType';

// Field types
//const collectionType = 'Symfony\Component\Form\Extension\Core\Type\CollectionType';
//const repeatedType = 'Symfony\Component\Form\Extension\Core\Type\RepeatedType';

// Hidden
//const hiddenType = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';


class ExtraUrlsegmentsType extends AbstractType
{


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$componentChoices = array();
		foreach($options['cmsComponentArray'] as $cmsComponent){
			if($cmsComponent['componentType'] =='segment'){
				if($cmsComponent['slug'] !=""){
					$componentChoices[$cmsComponent['name']] = $cmsComponent['slug'];
				}
			}
		}

		$builder
			->add('urlsegment',  choiceType, array(
				'label' => 'Dynamic Segment',
				'attr' => array('style'=>'width:250px; display: inline;'),
				'choices'=> $componentChoices,
				'choices_as_values' => true,
				'required'    => false,
				'placeholder' => 'none...',
				'empty_data'  => null,
				)
			)

		;
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'cmsComponentArray' => null
		));
	}



}
