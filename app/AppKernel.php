<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
			new Gregwar\ImageBundle\GregwarImageBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            //new Liip\ImagineBundle\LiipImagineBundle(),
            new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),

            //PROTOTYPE BUNDLES
            new Prototype\PageBundle\PrototypePageBundle(),
            new Prototype\AdminBundle\PrototypeAdminBundle(),
            new Prototype\MenuBundle\PrototypeMenuBundle(),
            new Prototype\NewsBundle\PrototypeNewsBundle(),
            new Prototype\UserBundle\PrototypeUserBundle(),
            new Prototype\EnquiryBundle\PrototypeEnquiryBundle(),
            new Prototype\CatalogBundle\PrototypeCatalogBundle(),
            new Prototype\SliderBundle\PrototypeSliderBundle(),
            new Prototype\CaseStudiesBundle\PrototypeCaseStudiesBundle(),
            new Prototype\MeetTheTeamBundle\PrototypeMeetTheTeamBundle(),
        );


        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Prototype\GeneratorBundle\PrototypeGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
