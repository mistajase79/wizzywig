<?php

namespace Prototype\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/dash", name="control_dash")
	 * @Security("has_role('ROLE_CMS_ACCESS')")
     */
    public function indexAction(Request $request)
    {
		$request->getSession()->set('_locale', 'en');
		$request->setLocale('en');
        return $this->render('PrototypeAdminBundle:Control:dash.html.twig');
    }

	/**
	 * the $request is used to get the current page route for the menu
     */
    public function showAdminHeaderAction($request = null)
    {
    		if ($this->get('security.authorization_checker')->isGranted('ROLE_CMS_ACCESS')) {
    			//add any logic for admin header elements - ie notifications
                $em = $this->getDoctrine()->getManager();
                $messages = $em->getRepository('PrototypeEnquiryBundle:Enquiry')->findBy(array('deleted'=>false, 'viewed'=>false));

                //notifications and flags left for future use
                $notifications = array();
                $alerts = array();

                //used for hiding top admin bar on frontend
                $isControlDash = true;

            	return $this->render('PrototypeAdminBundle:Control:header.html.twig', array(
                    'request' => $request,
                    'messages' => $messages,
                    'notifications' => $notifications,
                    'alerts' => $alerts,
                    'isControlDash' => $isControlDash
                    )
                );
    		}else{
    			return new Response('');
    		}
    }

}
