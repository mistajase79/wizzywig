<?php

namespace Prototype\EnquiryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\EnquiryBundle\Entity\Enquiry;
use Symfony\Component\Form\FormError;
//use Prototype\EnquiryBundle\Form\EnquiryTranslationType;
const EnquiryTranslationType = 'Prototype\EnquiryBundle\Form\EnquiryTranslationType';

/**
 * EnquiryTranslation controller.
 *
 * @Route("/control/enquiry")
 */
class EnquiryTranslationController extends Controller
{

    /**
     * Creates a new EnquiryTranslation entity.
     *
     * @Route("/enquiry-translation/{id}/new", name="control_enquiry_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Enquiry $enquiry)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($enquiry, $locales);

        $form = $this->createForm(EnquiryTranslationType, $enquiry, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('enquiry_translation');
            $enquiry = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($enquiry, $formData);
            $enquiry->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($enquiry);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_enquiry_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeEnquiryBundle:enquiry:saveTranslation.html.twig', array(
            'enquiry' => $enquiry,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing EnquiryTranslation entity.
     *
     * @Route("/enquiry-translation/{id}/edit/{locale}", name="control_enquiry_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Enquiry $enquiry, $locale)
    {
        //$deleteForm = $this->createDeleteForm($enquiry, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $enquiry->setTranslatableLocale($locale);
        $em->refresh($enquiry);

        //create form
        $form = $this->createForm(EnquiryTranslationType, $enquiry, array('currentLocale' =>$locale ));
        $enquiry->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('enquiry_translation');
            $enquiry = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($enquiry, $formData);
            $enquiry->setTranslatableLocale($locale); // set locale

            $em->persist($enquiry);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_enquiry_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeEnquiryBundle:enquiry:saveTranslation.html.twig', array(
            'enquiry' => $enquiry,
            'form' => $form->createView(),
        ));
    }

}
