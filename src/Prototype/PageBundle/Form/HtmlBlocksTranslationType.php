<?php

namespace Prototype\PageBundle\Form;

const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
const redactorType = 'Prototype\AdminBundle\Form\Type\redactorType';

const hiddenType = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlBlocksTranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('html', redactorType, array('label'=>'HTML'))
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
                    'choice_attr' => $options['localsAndAttributesArray']['attributes']
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
            'data_class' => 'Prototype\PageBundle\Entity\HtmlBlocks',
            'localsAndAttributesArray' => null, // if available translations are passed into the formType parse the data for choiceType
            'currentLocale' => null // if currentLocale is passed to the formType its safe to assume the translation exists
        ));
    }
}
