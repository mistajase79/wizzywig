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

class UserAdminChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('email', EmailType)
            // ->add('username', TextType)
            // ->add('firstname', TextType)
            // ->add('surname', TextType)
            // ->add('username', TextType)
            // ->add('is_active', checkboxType, array('label'=> 'Active'))
            // ->add('imageUpload', fileType, array('label' => 'Profile Image', 'required' => false))
            ->add('plainPassword', RepeatedType, array(
                'type' => PasswordType,
				'invalid_message' => 'The password fields must match.',
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prototype\UserBundle\Entity\User'
        ));
    }
}
