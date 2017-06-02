<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\CatalogBundle\Entity\CatalogCategories;
//use Prototype\CatalogBundle\Form\CatalogCategoriesType;
const CatalogCategoriesType ='Prototype\CatalogBundle\Form\CatalogCategoriesType';

/**
 * CatalogCategories controller.
 *
 * @Route("/control/catalog/categories")
 */
class CatalogCategoriesController extends Controller
{
	/**
	* Lists all CatalogCategories entities.
	*
	* @Route("/", name="control_catalog_categories_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Categories",
    * active=true,
    * role="ROLE_CATALOG_EDITOR",
    * routeName="control_catalog_categories_index",
    * icon="glyphicon glyphicon-list",
    * parentRouteName="control_catalog_index"
    * )
	*/
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$catalogCategories = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findBy(array('deleted'=>false));

		return $this->render('PrototypeCatalogBundle:catalogcategories:index.html.twig', array(
			'catalogCategories' => $catalogCategories,
		));
	}

    /**
     * Creates a new CatalogCategories entity.
     *
     * @Route("/new", name="control_catalog_categories_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $catalogCategory = new CatalogCategories();
        $form = $this->createForm(CatalogCategoriesType, $catalogCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($catalogCategory);
                $em->flush();
                $this->addFlash('success','Success - CatalogCategories created');
                return $this->redirectToRoute('control_catalog_categories_index');
            }else{
                $this->addFlash('error','Error - CatalogCategories not saved');
            }
        }

        $categoryTreeHTML = $this->container->get('pcgc_catalog_services')->buildCategorySelector();

        return $this->render('PrototypeCatalogBundle:catalogcategories:new.html.twig', array(
            'catalogCategory' => $catalogCategory,
            'categoryTreeHTML' => $categoryTreeHTML,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CatalogCategories entity.
     *
     * @Route("/{id}", name="control_catalog_categories_show")
     * @Method("GET")
     */
    public function showAction(CatalogCategories $catalogCategory)
    {
        $deleteForm = $this->createDeleteForm($catalogCategory);

        return $this->render('PrototypeCatalogBundle:catalogcategories:show.html.twig', array(
            'catalogCategory' => $catalogCategory,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CatalogCategories entity.
     *
     * @Route("/{id}/edit", name="control_catalog_categories_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CatalogCategories $catalogCategory)
    {
        $deleteForm = $this->createDeleteForm($catalogCategory);
        $editForm = $this->createForm(CatalogCategoriesType, $catalogCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($catalogCategory);
                $em->flush();
                $this->addFlash('success','Success - CatalogCategories updated');
                return $this->redirectToRoute('control_catalog_categories_edit', array('id' => $catalogCategory->getId()));
            }else{
                $this->addFlash('error','Error - CatalogCategories not saved');
            }
        }

        if($catalogCategory->getParent() != null){ $currentSelected = $catalogCategory->getParent()->getId(); }else{ $currentSelected =null; }

        $categoryTreeHTML = $this->container->get('pcgc_catalog_services')->buildCategorySelector($currentSelected);

        return $this->render('PrototypeCatalogBundle:catalogcategories:edit.html.twig', array(
            'catalogCategory' => $catalogCategory,
            'categoryTreeHTML' => $categoryTreeHTML,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CatalogCategories entity.
     *
     * @Route("/{id}", name="control_catalog_categories_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CatalogCategories $catalogCategory)
    {
        $form = $this->createDeleteForm($catalogCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $catalogCategory->setDeleted(true);
            $em->persist($catalogCategory);
            $em->flush();
            $this->addFlash('success','Success - catalogCategory deleted');
        }

        return $this->redirectToRoute('control_catalog_categories_index');
    }

    /**
     * Creates a form to delete a CatalogCategories entity.
     *
     * @param CatalogCategories $catalogCategory The CatalogCategories entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CatalogCategories $catalogCategory)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_catalog_categories_delete', array('id' => $catalogCategory->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
