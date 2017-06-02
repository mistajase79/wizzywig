<?php
namespace Prototype\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

const TextType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
const EmailType = 'Symfony\Component\Form\Extension\Core\Type\EmailType';
const RepeatedType = 'Symfony\Component\Form\Extension\Core\Type\RepeatedType';
const PasswordType = 'Symfony\Component\Form\Extension\Core\Type\PasswordType';
const checkboxType = 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';
const fileType = 'Symfony\Component\Form\Extension\Core\Type\FileType';
const choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $options['roles'];

        $builder
            ->add('username', TextType)
            ->add('email', EmailType)
            ->add('name', TextType)
            ->add('is_active', checkboxType, array('label'=> 'Active'))
            ->add('roles', choiceType, array( 'choices'  => $roles, 'expanded'=>false, 'multiple'=>true, 'choices_as_values' => true))
            ->add('imageUpload', fileType, array('label' => 'Profile Image', 'required' => false))
            // ->add('plainPassword', RepeatedType, array(
            //     'type' => PasswordType,
			// 	'invalid_message' => 'The password fields must match.',
            //     'first_options'  => array('label' => 'Password'),
            //     'second_options' => array('label' => 'Repeat Password'),
            // ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prototype\UserBundle\Entity\User',
            'roles' => null
        ));
    }
}
