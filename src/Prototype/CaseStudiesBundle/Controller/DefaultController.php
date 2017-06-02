<?php

namespace Prototype\CaseStudiesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;

class DefaultController extends Controller
{
    /**
     * @Route("/pcgc-casestudies", name="embed_casestudies")
     * @ProtoCmsComponent("Embed CaseStudies", active=true, routeName="embed_casestudies")
     */
    public function embedCaseStudiesAction()
    {
        return $this->render('@theme/casestudies/casestudies.html.twig');
    }
}
