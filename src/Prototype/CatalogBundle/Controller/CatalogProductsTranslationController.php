<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\CatalogBundle\Entity\CatalogProducts;
use Symfony\Component\Form\FormError;
//use Prototype\CatalogBundle\Form\CatalogProductsTranslationType;
const CatalogProductsTranslationType = 'Prototype\CatalogBundle\Form\CatalogProductsTranslationType';

/**
 * CatalogProductsTranslation controller.
 *
 * @Route("/control/catalog/products")
 */
class CatalogProductsTranslationController extends Controller
{

    /**
     * Creates a new CatalogProductsTranslation entity.
     *
     * @Route("/catalog-product-translation/{id}/new", name="control_catalog_products_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, CatalogProducts $catalogProduct)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($catalogProduct, $locales);

        $form = $this->createForm(CatalogProductsTranslationType, $catalogProduct, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('catalog_products_translation');
            $catalogProduct = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($catalogProduct, $formData);
            $catalogProduct->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($catalogProduct);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_catalog_products_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeCatalogBundle:catalogproducts:saveTranslation.html.twig', array(
            'catalogProduct' => $catalogProduct,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CatalogProductsTranslation entity.
     *
     * @Route("/catalog-product-translation/{id}/edit/{locale}", name="control_catalog_products_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CatalogProducts $catalogProduct, $locale)
    {
        //$deleteForm = $this->createDeleteForm($catalogProduct, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $catalogProduct->setTranslatableLocale($locale);
        $em->refresh($catalogProduct);

        //create form
        $form = $this->createForm(CatalogProductsTranslationType, $catalogProduct, array('currentLocale' =>$locale ));
        $catalogProduct->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('catalog_products_translation');
            $catalogProduct = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($catalogProduct, $formData);
            $catalogProduct->setTranslatableLocale($locale); // set locale

            $em->persist($catalogProduct);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_catalog_products_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeCatalogBundle:catalogproducts:saveTranslation.html.twig', array(
            'catalogProduct' => $catalogProduct,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
