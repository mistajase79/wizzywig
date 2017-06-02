<?php
namespace Prototype\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

const textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
const emailType = 'Symfony\Component\Form\Extension\Core\Type\EmailType';
const fileType = 'Symfony\Component\Form\Extension\Core\Type\FileType';

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('username', textType, array('label' => 'Username', 'required' => true))
            ->add('email', emailType, array('label' => 'Email Address'))
            ->add('name', textType, array('label' => 'Name', 'required' => false))
			->add('imageUpload', fileType, array('label' => 'Profile Image', 'required' => false))

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prototype\UserBundle\Entity\User'
        ));
    }
}
