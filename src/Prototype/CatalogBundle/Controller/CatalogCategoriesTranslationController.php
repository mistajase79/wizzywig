<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\CatalogBundle\Entity\CatalogCategories;
use Symfony\Component\Form\FormError;
//use Prototype\CatalogBundle\Form\CatalogCategoriesTranslationType;
const CatalogCategoriesTranslationType = 'Prototype\CatalogBundle\Form\CatalogCategoriesTranslationType';

/**
 * CatalogCategoriesTranslation controller.
 *
 * @Route("/control/catalog/categories")
 */
class CatalogCategoriesTranslationController extends Controller
{

    /**
     * Creates a new CatalogCategoriesTranslation entity.
     *
     * @Route("/catalog-category-translation/{id}/new", name="control_catalog_categories_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, CatalogCategories $catalogCategory)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($catalogCategory, $locales);

        $form = $this->createForm(CatalogCategoriesTranslationType, $catalogCategory, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('catalog_categories_translation');
            $catalogCategory = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($catalogCategory, $formData);
            $catalogCategory->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($catalogCategory);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_catalog_categories_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeCatalogBundle:catalogcategories:saveTranslation.html.twig', array(
            'catalogCategory' => $catalogCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CatalogCategoriesTranslation entity.
     *
     * @Route("/catalog-category-translation/{id}/edit/{locale}", name="control_catalog_categories_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CatalogCategories $catalogCategory, $locale)
    {
        //$deleteForm = $this->createDeleteForm($catalogCategory, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $catalogCategory->setTranslatableLocale($locale);
        $em->refresh($catalogCategory);

        //create form
        $form = $this->createForm(CatalogCategoriesTranslationType, $catalogCategory, array('currentLocale' =>$locale ));
        $catalogCategory->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('catalog_categories_translation');
            $catalogCategory = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($catalogCategory, $formData);
            $catalogCategory->setTranslatableLocale($locale); // set locale

            $em->persist($catalogCategory);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_catalog_categories_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeCatalogBundle:catalogcategories:saveTranslation.html.twig', array(
            'catalogCategory' => $catalogCategory,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
