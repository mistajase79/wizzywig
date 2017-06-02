<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\PageBundle\Entity\HtmlBlocks;
use Symfony\Component\Form\FormError;
//use Prototype\PageBundle\Form\HtmlBlocksTranslationType;
const HtmlBlocksTranslationType = 'Prototype\PageBundle\Form\HtmlBlocksTranslationType';

/**
 * HtmlBlocksTranslation controller.
 *
 * @Route("/control/htmlblocks")
 */
class HtmlBlocksTranslationController extends Controller
{

    /**
     * Creates a new HtmlBlocksTranslation entity.
     *
     * @Route("/html-block-translation/{id}/new", name="control_htmlblocks_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, HtmlBlocks $htmlBlock)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($htmlBlock, $locales);

        $form = $this->createForm(HtmlBlocksTranslationType, $htmlBlock, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('html_blocks_translation');
            $htmlBlock = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($htmlBlock, $formData);
            $htmlBlock->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($htmlBlock);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_htmlblocks_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypePageBundle:htmlblocks:saveTranslation.html.twig', array(
            'htmlBlock' => $htmlBlock,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing HtmlBlocksTranslation entity.
     *
     * @Route("/html-block-translation/{id}/edit/{locale}", name="control_htmlblocks_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, HtmlBlocks $htmlBlock, $locale)
    {
        //$deleteForm = $this->createDeleteForm($htmlBlock, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $htmlBlock->setTranslatableLocale($locale);
        $em->refresh($htmlBlock);

        //create form
        $form = $this->createForm(HtmlBlocksTranslationType, $htmlBlock, array('currentLocale' =>$locale ));
        $htmlBlock->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('html_blocks_translation');
            $htmlBlock = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($htmlBlock, $formData);
            $htmlBlock->setTranslatableLocale($locale); // set locale

            $em->persist($htmlBlock);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_htmlblocks_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypePageBundle:htmlblocks:saveTranslation.html.twig', array(
            'htmlBlock' => $htmlBlock,
            'locale' => $locale,
            'form' => $form->createView(),
        ));
    }

}
