<?php

namespace Prototype\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

const textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
const redactorType = 'Prototype\AdminBundle\Form\Type\redactorType';
const hiddenType = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';

class PageTranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($options['localsAndAttributesArray'] == null && $options['currentLocale'] == null){
            throw new \InvalidArgumentException('PCGC: '.get_class($this)." has not been passed 'localsAndAttributesArray' or 'currentLocale' so not sure whether you want to show an add or edit form - trying passing either one");
        }

        $builder
            ->add('title')
            ->add('url', null, array('label' => 'URL Keyword','required' => false))
            ->add('navtitle', null, array('label' => 'Menu Title'))
            ->add('content', redactorType, array('required'=>false))
            ->add('metatitle', null, array('label' => 'Window Title'))
            ->add('metadescription', null, array('label' => 'Meta Description'))
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
            'data_class' => 'Prototype\PageBundle\Entity\Page',
            'localsAndAttributesArray' => null, // if available translations are passed into the formType parse the data for choiceType
            'currentLocale' => null // if currentLocale is passed to the formType its safe to assume the translation exists
        ));
    }
}
