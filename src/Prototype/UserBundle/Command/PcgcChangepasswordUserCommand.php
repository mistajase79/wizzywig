<?php

namespace Prototype\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PcgcChangepasswordUserCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('pcgc:changepassword:user')
            ->setDescription('Change a CMS user password')
            ->setHelp('This command allows you to change a users password...duh!')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $io->title('ProtoCMS Change Password Command');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $helper = $this->getHelper('question');

        //build & capture email address question
        $questionEmail = new Question('Please enter the users email address: ');
        $questionEmail->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('The email must not be blank');
            }
            return $value;
        });
        $questionEmail->setMaxAttempts(3);
        $email = $helper->ask($input, $output, $questionEmail);

        //check if email exists
        $output->writeln('Checking '.$email.'.....');
        $user = $em->getRepository('PrototypeUserBundle:User')->findOneByEmail($email);
        if(!$user){
            $io->error('The email does not exist');
            return;
        }
        $output->writeln('Found user : '.$user->getUsername());

        //build & capture password question
        $questionPassword = new Question('Enter new password: ');
        $questionPassword->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('The password must not be blank');
            }
            return $value;
        });
        $questionPassword->setMaxAttempts(3);
        $password = $helper->ask($input, $output, $questionPassword);

        //Change password in Database
        $password_encoder = $this->getContainer()->get('security.password_encoder');
        $encryedPassword = $password_encoder->encodePassword($user,$password);
        $user->setEmailresetkey(null);
        $user->setPassword($encryedPassword);
        $em->persist($user);
        $em->flush();

        $io->success('Password for '.$email.' has been changed');

    }

}
