<?php

namespace Prototype\SliderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\SliderBundle\Entity\Slider;
use Prototype\SliderBundle\Entity\SliderImages;
use Symfony\Component\Form\FormError;
//use Prototype\SliderBundle\Form\SliderTranslationType;
const SliderImageTranslationType = 'Prototype\SliderBundle\Form\SliderImageTranslationType';

/**
 * SliderTranslation controller.
 *
 * @Route("/control/slider")
 */
class SliderTranslationController extends Controller
{

    /**
     * Creates a new SliderTranslation entity.
     *
     * @Route("/slider-translation/{id}/new", name="control_slider_images_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, SliderImages $sliderImage)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($sliderImage, $locales);

        $form = $this->createForm(SliderImageTranslationType, $sliderImage, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('slider_image_translation');
            $sliderImage = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($sliderImage, $formData);
            $sliderImage->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($sliderImage);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_slider_edit', array('id'=>$sliderImage->getSlider()->getId()));
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeSliderBundle:slider:saveTranslation.html.twig', array(
            'sliderImage' => $sliderImage,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SliderTranslation entity.
     *
     * @Route("/slider-translation/{id}/edit/{locale}", name="control_slider_translation_edit")
     * @Route("/slider-translation/{id}/edit/{locale}", name="control_slider_images_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SliderImages $sliderImage, $locale)
    {
        //$deleteForm = $this->createDeleteForm($slider, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $sliderImage->setTranslatableLocale($locale);
        $em->refresh($sliderImage);

        //create form
        $form = $this->createForm(SliderImageTranslationType, $sliderImage, array('currentLocale' =>$locale ));
        $sliderImage->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('slider_image_translation');
            $sliderImage = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($sliderImage, $formData);
            $sliderImage->setTranslatableLocale($locale); // set locale

            $em->persist($sliderImage);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_slider_edit', array('id'=>$sliderImage->getSlider()->getId()));
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeSliderBundle:slider:saveTranslation.html.twig', array(
            'sliderImage' => $sliderImage,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
