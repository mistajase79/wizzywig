<?php

namespace Prototype\CaseStudiesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\CaseStudiesBundle\Entity\CaseStudies;
use Symfony\Component\Form\FormError;
//use Prototype\CaseStudiesBundle\Form\CaseStudiesTranslationType;
const CaseStudiesTranslationType = 'Prototype\CaseStudiesBundle\Form\CaseStudiesTranslationType';

/**
 * CaseStudiesTranslation controller.
 *
 * @Route("/control/case-studies")
 */
class CaseStudiesTranslationController extends Controller
{

    /**
     * Creates a new CaseStudiesTranslation entity.
     *
     * @Route("/case-study-translation/{id}/new", name="control_casestudies_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, CaseStudies $caseStudy)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($caseStudy, $locales);

        $form = $this->createForm(CaseStudiesTranslationType, $caseStudy, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('caseStudy_translation');
            $caseStudy = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($caseStudy, $formData);
            $caseStudy->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($caseStudy);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_casestudies_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeCaseStudiesBundle:casestudies:saveTranslation.html.twig', array(
            'caseStudy' => $caseStudy,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CaseStudiesTranslation entity.
     *
     * @Route("/case-study-translation/{id}/edit/{locale}", name="control_casestudies_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CaseStudies $caseStudy, $locale)
    {
        //$deleteForm = $this->createDeleteForm($caseStudy, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $caseStudy->setTranslatableLocale($locale);
        $em->refresh($caseStudy);

        //create form
        $form = $this->createForm(CaseStudiesTranslationType, $caseStudy, array('currentLocale' =>$locale ));
        $caseStudy->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('caseStudy_translation');
            $caseStudy = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($caseStudy, $formData);
            $caseStudy->setTranslatableLocale($locale); // set locale

            $em->persist($caseStudy);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_casestudies_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeCaseStudiesBundle:casestudies:saveTranslation.html.twig', array(
            'caseStudy' => $caseStudy,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
