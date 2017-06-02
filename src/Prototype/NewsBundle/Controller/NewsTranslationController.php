<?php

namespace Prototype\NewsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\NewsBundle\Entity\News;

use Symfony\Component\Form\FormError;
use Prototype\NewsBundle\Entity\Locales;
use Prototype\NewsBundle\Form\NewsTranslationType;

const NewsTranslationType = 'Prototype\NewsBundle\Form\NewsTranslationType';

/**
 * News controller.
 *
 * @Route("/control")
 */
class NewsTranslationController extends Controller
{
    /**
     * Creates a new News entity.
     *
     * @Route("/news-translation/{id}/new", name="control_news_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, News $news)
    {

		$em = $this->getDoctrine()->getManager();
		$locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
		$localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($news, $locales);

		$form = $this->createForm(NewsTranslationType, $news, array(
			'localsAndAttributesArray' =>$localsAndAttributesArray )
		);

		$form->handleRequest($request);

		if ($form->isSubmitted()){

			// find and save translatable setters
			$formData = $request->get('news_translation');
			$news = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($news, $formData);
			$news->setTranslatableLocale($formData['translatableLocale']); // change locale
			$em->persist($news);
			$em->flush();

			$this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
			return $this->redirectToRoute('control_news_index');
			///////////////////////////////////////////
		}

		return $this->render('PrototypeNewsBundle:news:saveTranslation.html.twig', array(
			'news' => $news,
			'form' => $form->createView(),
		));
	}


	/**
     *
     * @Route("/news-transaltion/{id}/edit/{locale}", name="control_news_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, News $news, $locale)
    {
		// load translation
		$em = $this->getDoctrine()->getManager();
		$locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
		$news->setTranslatableLocale($locale);
		$em->refresh($news);

		//create form
		$form = $this->createForm(NewsTranslationType, $news, array('currentLocale' =>$locale ));
		$news->setTranslatableLocale($locale); // change locale
		$form->handleRequest($request);

		if ($form->isSubmitted()){
			// find and save translatable setters
			$formData = $request->get('news_translation');
			$news = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($news, $formData);
			$news->setTranslatableLocale($locale); // set locale

			$em->persist($news);
			$em->flush();

			$this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
			return $this->redirectToRoute('control_news_index');
			///////////////////////////////////////////
		}

		return $this->render('PrototypeNewsBundle:news:saveTranslation.html.twig', array(
			'news' => $news,
			'form' => $form->createView(),
            'locale' => $locale
		));

	}

}
