<?php

namespace Prototype\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Prototype\UserBundle\Entity\User;
use Prototype\PageBundle\Entity\Page;
use Prototype\PageBundle\Entity\Templates;
use Prototype\MenuBundle\Entity\Menu;
use Prototype\MenuBundle\Entity\MenuItems;
use Prototype\PageBundle\Entity\Locales;

class PcgcInitialiseCmsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('pcgc:initialise:cms')
            ->setDescription('Create initial DB records')
            ->setHelp('This command add default user, home page and menu records')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $io->title('ProtoCMS Create Initial DB Records');
        //check if email exists
        $output->writeln('Checking for ProtoDev07.....');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('PrototypeUserBundle:User')->findOneByUsername('protodev07');
        if($user){
            $io->error('Records already Added');
            return;
        }

        $output->writeln('Not Found - OK');
        $output->writeln('Adding Records...');

        $user = new User();
        $user->setUsername('protodev07');
        $user->setEmail('web@prototypecreative.co.uk');
        $user->setIsActive(1);
        $user->setRoles(array('ROLE_DEVELOPER'));
        $setPassAs = 'abcde';
        $password = $this->getContainer()->get('security.password_encoder')->encodePassword($user, 'abcde');
        $user->setPassword($password);
        $em->persist($user);
        //$em->flush();

        $output->writeln('-----------------------');
        $output->writeln('User: protodev07');
        $output->writeln('Pass: '.$setPassAs);
        $output->writeln('-----------------------');


        $template = new Templates();
        $template->setTitle('Home');
        $template->setDescription('Home Page Template');
        $template->setTemplatefile('home.html.twig');
        $template->setActive(1);
        $template->setDeleted(0);
        $template->setInheritedBundleName(null);
        $em->persist($template);

        $output->writeln('Template: Home Created');


        $dateTime = new \DateTime();
        $page = new Page();
        $page->setUpdatedBy($user);
        $page->setTitle('Home');
        $page->setSlug('home');
        $page->setContent('Homepage content goes here');
        $page->setCreated($dateTime);
        $page->setUpdated($dateTime);
        $page->setParent(null);
        $page->setActive(1);
        $page->setDeleted(0);
        $page->setTemplate($template);
        $page->setComponents(array());
        $page->setMetatitle(null);
        $page->setMetadescription(null);
        $page->setNavtitle('home');
        $page->setExtraUrlsegments(null);
        $page->setUrl('home');
        $page->setViewableFrom($dateTime);
        $page->setHtmlblocks(array());
        $em->persist($page);

         $output->writeln('Page: Home Created');

        $template2 = new Templates();
        $template2->setTitle('Standard');
        $template2->setDescription('Standard CMS Page');
        $template2->setTemplatefile('cmspage-standard.html.twig');
        $template2->setActive(1);
        $template2->setDeleted(0);
        $template2->setInheritedBundleName(null);
        $em->persist($template2);

        $template3 = new Templates();
        $template3->setTitle('Store Template');
        $template3->setDescription('Store Template');
        $template3->setTemplatefile('cmspage-store.html.twig');
        $template3->setActive(1);
        $template3->setDeleted(0);
        $template3->setInheritedBundleName('PrototypeCatalogBundle');
        $em->persist($template3);

        $template4 = new Templates();
        $template4->setTitle('Contact Template');
        $template4->setDescription('Contains Googlemap');
        $template4->setTemplatefile('cmspage-contact.html.twig');
        $template4->setActive(1);
        $template4->setDeleted(0);
        $template4->setInheritedBundleName(null);
        $em->persist($template4);

        $output->writeln('Extra Templates Added');

        $menu = new Menu();
        $menu->setIdentifier('Top');
        $menu->setActive(1);
        $menu->setDeleted(0);
        $menu->setCreatedAt($dateTime);
        $menu->setUpdatedAt($dateTime);
        $menu->setUpdatedBy($user);
        $em->persist($menu);

        $output->writeln('Menu: Top Added');

        $menuitem = new MenuItems();
        $menuitem->setMenuItemId(1);
		$menuitem->setMenuOverride('Home');
        $menuitem->setActive(1);
        $menuitem->setDeleted(0);
        $menuitem->setCreatedAt($dateTime);
        $menuitem->setUpdatedAt($dateTime);
        $menuitem->setPageId($page);
        $menuitem->setMenuId($menu);
        $em->persist($menuitem);

        $output->writeln('MenuItem: Home Added');

        $locale= new Locales();
        $locale->setLocale('fr');
        $locale->setLanguage('Français');
        $locale->setActive(true);
        $em->persist($locale);

        $locale= new Locales();
        $locale->setLocale('de');
        $locale->setLanguage('Deutsche');
        $locale->setActive(true);
        $em->persist($locale);

        $locale= new Locales();
        $locale->setLocale('es');
        $locale->setLanguage('Espanol');
        $locale->setActive(true);
        $em->persist($locale);

        $output->writeln('Default Locales: fr, de, es Added');

        $em->flush();

        $io->success('Records Created - Now go build a website!');

    }

}
