<?php

namespace Prototype\SliderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;

class DefaultController extends Controller
{
    /**
     * @Route("/pcgc-slider", name="embed_slider")
     * @ProtoCmsComponent("Embed Slider", active=true, routeName="embed_slider")
     */
    public function embedSliderAction($request, $pageId)
    {
    	$em = $this->getDoctrine()->getManager();
		$slider = $em->getRepository('PrototypeSliderBundle:Slider')->findByPageId($pageId);
		
		if(!$slider){ return new Response('No Slider Assigned to Page'); }

        return $this->render('@theme/slider/slider.html.twig', array('slider' => $slider));
    }
}
