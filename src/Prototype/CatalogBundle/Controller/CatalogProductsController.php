<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\CatalogBundle\Entity\CatalogProducts;
//use Prototype\CatalogBundle\Form\CatalogProductsType;
const CatalogProductsType ='Prototype\CatalogBundle\Form\CatalogProductsType';

/**
 * CatalogProducts controller.
 *
 * @Route("/control/catalog/products")
 */
class CatalogProductsController extends Controller
{
	/**
	* Lists all CatalogProducts entities.
	*
	* @Route("/", name="control_catalog_products_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Products",
    * active=true,
    * role="ROLE_CATALOG_EDITOR",
    * routeName="control_catalog_products_index",
    * icon="glyphicon glyphicon-list",
    * parentRouteName="control_catalog_index")
	*/
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$catalogProducts = $em->getRepository('PrototypeCatalogBundle:CatalogProducts')->findBy(array('deleted'=>false));

		return $this->render('PrototypeCatalogBundle:catalogproducts:index.html.twig', array(
			'catalogProducts' => $catalogProducts,
		));
	}

    /**
     * Creates a new CatalogProducts entity.
     *
     * @Route("/new", name="control_catalog_products_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $catalogProduct = new CatalogProducts();
        $form = $this->createForm(CatalogProductsType, $catalogProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($catalogProduct);
                $em->flush();
                $this->addFlash('success','Success - CatalogProducts created');
                return $this->redirectToRoute('control_catalog_products_index');
            }else{
                $this->addFlash('error','Error - CatalogProducts not saved');
            }
        }

        if($catalogProduct->getCategory() != null){ $currentSelected = $catalogProduct->getCategory()->getId(); }else{ $currentSelected =null; }

        $categoryTreeHTML = $this->container->get('pcgc_catalog_services')->buildCategorySelector($currentSelected);


        return $this->render('PrototypeCatalogBundle:catalogproducts:new.html.twig', array(
            'catalogProduct' => $catalogProduct,
            'categoryTreeHTML' => $categoryTreeHTML,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CatalogProducts entity.
     *
     * @Route("/{id}", name="control_catalog_products_show")
     * @Method("GET")
     */
    public function showAction(CatalogProducts $catalogProduct)
    {
        $deleteForm = $this->createDeleteForm($catalogProduct);

        return $this->render('PrototypeCatalogBundle:catalogproducts:show.html.twig', array(
            'catalogProduct' => $catalogProduct,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CatalogProducts entity.
     *
     * @Route("/{id}/edit", name="control_catalog_products_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CatalogProducts $catalogProduct)
    {
        $deleteForm = $this->createDeleteForm($catalogProduct);
        $editForm = $this->createForm(CatalogProductsType, $catalogProduct);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($catalogProduct);
                $em->flush();
                $this->addFlash('success','Success - CatalogProducts updated');
                return $this->redirectToRoute('control_catalog_products_edit', array('id' => $catalogProduct->getId()));
            }else{
                $this->addFlash('error','Error - CatalogProducts not saved');
            }
        }

        if($catalogProduct->getCategory() != null){ $currentSelected = $catalogProduct->getCategory()->getId(); }else{ $currentSelected =null; }

        $categoryTreeHTML = $this->container->get('pcgc_catalog_services')->buildCategorySelector($currentSelected);

        return $this->render('PrototypeCatalogBundle:catalogproducts:edit.html.twig', array(
            'catalogProduct' => $catalogProduct,
            'categoryTreeHTML' => $categoryTreeHTML,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CatalogProducts entity.
     *
     * @Route("/{id}", name="control_catalog_products_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CatalogProducts $catalogProduct)
    {
        $form = $this->createDeleteForm($catalogProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $catalogProduct->setDeleted(true);
            $em->persist($catalogProduct);
            $em->flush();
            $this->addFlash('success','Success - catalogProduct deleted');
        }

        return $this->redirectToRoute('control_catalog_products_index');
    }

    /**
     * Creates a form to delete a CatalogProducts entity.
     *
     * @param CatalogProducts $catalogProduct The CatalogProducts entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CatalogProducts $catalogProduct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_catalog_products_delete', array('id' => $catalogProduct->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


}
