<?php

namespace Prototype\BlogBundle\Form;

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
const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
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
const hiddenType = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';

//PCGC
//const imagemanagerType = 'Prototype\AdminBundle\Form\Type\imagemanagerType';
//const summernoteType = 'Prototype\AdminBundle\Form\Type\summernoteType';
//const datepickerType = 'Prototype\AdminBundle\Form\Type\datepickerType';
//const captchaType = 'Gregwar\CaptchaBundle\Type\CaptchaType';
//const datetimepickerType = 'Prototype\AdminBundle\Form\Type\datetimepickerType';
//const redactorType = 'Prototype\AdminBundle\Form\Type\redactorType';

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogPostsTranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        
                    ->add('title')        
                    ->add('exert')        
                    ->add('content')        
                    ->add('datePosted', 'datetime')                
                    ->add('image')                                        
                    ->add('categoryId')        
        ;

        // if adding a new translation
        if($options['localsAndAttributesArray'] != null){
            $builder->add('translatableLocale', choiceType, array(
                    'choices' => $options['localsAndAttributesArray']['locales'],
                    'mapped' => false,
                    'choices_as_values' => true,
                    'placeholder' => 'Available languages',
                    'empty_data'  => null,
                    'required' => true,
                    'choice_attr' => $options['localsAndAttributesArray']['attributes'],
                    'attr'=>array('style' => 'width:100%')
                    )
                );
        }else{
            // if editing existing translation
            $builder->add('translatableLocale', hiddenType, array('mapped' => false, 'attr'=>array('value' =>$options['currentLocale'])));
        }
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prototype\BlogBundle\Entity\BlogPosts',
            'localsAndAttributesArray' => null, // if available translations are passed into the formType parse the data for choiceType
            'currentLocale' => null // if currentLocale is passed to the formType its safe to assume the translation exists
        ));
    }
}
