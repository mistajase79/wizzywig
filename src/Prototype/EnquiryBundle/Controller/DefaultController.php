<?php

namespace Prototype\EnquiryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;

use Prototype\EnquiryBundle\Entity\Enquiry;

class DefaultController extends Controller
{
    /**
     * @Route("/pcgc-enquiry", name="embed_enquiry")
     * @ProtoCmsComponent("Embed Enquiry Form", active=true, routeName="embed_enquiry")
     */
    public function embedEnquiryAction(Request $request)
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(EnquiryType, $enquiry);
        $form->handleRequest($request);

        $error = false;
        $success = false;

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($enquiry);
                $em->flush();
                $success = true;
                //$this->addFlash('success','Success - Enquiry sent');
            }else{
                $error = true;
                //$this->addFlash('error','Error - Enquiry not sent');
            }
        }

        return $this->render('@theme/enquiry/enquiry.html.twig', array(
            'enquiry' => $enquiry,
            'error' => $error,
            'success' => $success,
            'form' => $form->createView(),
        ));
    }
}
