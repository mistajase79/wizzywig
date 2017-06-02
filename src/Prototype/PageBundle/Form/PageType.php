<?php

namespace Prototype\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

const ActiveComponentsType = 'Prototype\PageBundle\Form\ActiveComponentsType';
const ActiveHtmlBlocksType = 'Prototype\PageBundle\Form\ActiveHtmlBlocksType';
const ExtraUrlsegmentsType = 'Prototype\PageBundle\Form\ExtraUrlsegmentsType';
const collectionType = 'Symfony\Component\Form\Extension\Core\Type\CollectionType';
const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
// const summernoteType = 'Prototype\AdminBundle\Form\Type\summernoteType';
//const entityType = 'Symfony\Component\Form\Extension\Core\Type\EntityType';
const entityType = 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
const datetimepickerType = 'Prototype\AdminBundle\Form\Type\datetimepickerType';
//const textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
const redactorType = 'Prototype\AdminBundle\Form\Type\redactorType';
const hiddenType = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';

class PageType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $cmsComponentArray = $options['cmsComponentArray'];
        $cmsHtmlArray = $options['cmsHtmlArray'];

        $builder
            ->add('title')
            ->add('navtitle', null, array('label' => 'Default Menu Title'))
            ->add('url', null, array('label' => 'URL Keyword','required' => false))
            ->add('parent', entityType, array(
                    'attr' => array('readonly' => true),
                    'required' => false,
                    'class' => 'PrototypePageBundle:Page',
                    'query_builder' => function(EntityRepository $er) {
                      return $er->createQueryBuilder('p')
                                ->where('p.deleted = 0')
                                //->orderby('p.slug')
                                ;
                      },)
                    )

            ->add('template', null, array('required' => true,
                'placeholder' => 'Select a template'
            ))
            ->add('active')
            ->add('viewable_from', datetimepickerType)
            ->add('content', redactorType, array('required'=>false))
            ->add('metatitle', null, array('label' => 'Window Title'))
            ->add('metadescription', null, array('label' => 'Meta Description'))

            ->add('components', collectionType, array(
                'entry_type'   => ActiveComponentsType,
                'entry_options'  => array('cmsComponentArray' => $cmsComponentArray),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => false,
            ))
            ->add('htmlblocks', collectionType, array(
                'entry_type'   => ActiveHtmlBlocksType,
                'entry_options'  => array('cmsHtmlArray' => $cmsHtmlArray),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => false,
            ))

            ->add('extraUrlsegments', collectionType, array(
                'label' => false,
                'entry_type'   => ExtraUrlsegmentsType,
                'entry_options'  => array('cmsComponentArray' => $cmsComponentArray),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,

            ))

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prototype\PageBundle\Entity\Page',
            'cmsComponentArray' => null,
            'cmsHtmlArray' => null
        ));
    }
}
