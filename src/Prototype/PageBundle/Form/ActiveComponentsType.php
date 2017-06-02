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


class ActiveComponentsType extends AbstractType
{


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //print_r($options['cmsComponentArray']);

        $componentChoices = array();
        $componentAttributes = array();

        foreach($options['cmsComponentArray'] as $cmsComponent){
            //check if a component is URL based - manage groups and attr
            if($cmsComponent['componentType'] =='standard'){
                if($cmsComponent['route'] !=""){// only show components with actions
                    if($cmsComponent['slug'] !=""){ //URL required components
                        $componentChoices['URL Based'][$cmsComponent['name']] = $cmsComponent['route'];
                        $componentAttributes[$cmsComponent['name']] =  array(
                            'data-slug' =>$cmsComponent['slug'],
                            'data-urlbased'=>1
                        );
                    }else{//non URL based components
                        $componentChoices['General'][$cmsComponent['name']] = $cmsComponent['route'];
                        $componentAttributes[$cmsComponent['name']] =  array(
                            'data-slug' =>$cmsComponent['slug'],
                            'data-urlbased'=>0
                        );
                    }
                }
            }

        }

        //echo "<pre>".print_r($componentChoices, true)."</pre>";

        $builder
            ->add('position', hiddenType)
            ->add('route',  choiceType, array(
                'choices'=> $componentChoices,
                'choices_as_values' => true,
                'required'    => false,
                'placeholder' => 'None',
                'empty_data'  => null,
                'choice_attr' => $componentAttributes
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
