<?php

namespace Prototype\GeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/gen")
     */
    public function indexAction()
    {
        return $this->render('PrototypeGeneratorBundle:Default:index.html.twig');
    }
}
