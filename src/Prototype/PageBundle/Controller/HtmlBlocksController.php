<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\PageBundle\Entity\HtmlBlocks;
//use Prototype\PageBundle\Form\HtmlBlocksType;
const HtmlBlocksType ='Prototype\PageBundle\Form\HtmlBlocksType';

/**
 * HtmlBlocks controller.
 *
 * @Route("/control/htmlblocks")
 */
class HtmlBlocksController extends Controller
{
    /**
    * Lists all HtmlBlocks entities.
    *
    * @Route("/", name="control_htmlblocks_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("HTML Blocks", active=true, routeName="control_htmlblocks_index", icon="fa fa-cubes", menuPosition=50, parentRouteName="control_content_index")
    */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $htmlBlocks = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted'=>false));

        return $this->render('PrototypePageBundle:htmlblocks:index.html.twig', array(
            'htmlBlocks' => $htmlBlocks,
        ));
    }

    /**
     * Creates a new HtmlBlocks entity.
     *
     * @Route("/new", name="control_htmlblocks_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $htmlBlock = new HtmlBlocks();
        $form = $this->createForm(HtmlBlocksType, $htmlBlock);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($htmlBlock);
                $em->flush();
                $this->addFlash('success','Success - HTML Block created');
                return $this->redirectToRoute('control_htmlblocks_index');
            }else{
                $this->addFlash('error','Error - HTML Block not saved');
            }
        }

        return $this->render('PrototypePageBundle:htmlblocks:new.html.twig', array(
            'htmlBlock' => $htmlBlock,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a HtmlBlocks entity.
     *
     * @Route("/{id}", name="control_htmlblocks_show")
     * @Method("GET")
     */
    public function showAction(HtmlBlocks $htmlBlock)
    {
        $deleteForm = $this->createDeleteForm($htmlBlock);

        return $this->render('PrototypePageBundle:htmlblocks:show.html.twig', array(
            'htmlBlock' => $htmlBlock,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing HtmlBlocks entity.
     *
     * @Route("/{id}/edit", name="control_htmlblocks_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, HtmlBlocks $htmlBlock)
    {
        $deleteForm = $this->createDeleteForm($htmlBlock);
        $editForm = $this->createForm(HtmlBlocksType, $htmlBlock);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($htmlBlock);
                $em->flush();
                $this->addFlash('success','Success - HTML Block updated');
                return $this->redirectToRoute('control_htmlblocks_edit', array('id' => $htmlBlock->getId()));
            }else{
                $this->addFlash('error','Error - HTML Block not saved');
            }
        }

        return $this->render('PrototypePageBundle:htmlblocks:edit.html.twig', array(
            'htmlBlock' => $htmlBlock,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a HtmlBlocks entity.
     *
     * @Route("/{id}", name="control_htmlblocks_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, HtmlBlocks $htmlBlock)
    {
        $form = $this->createDeleteForm($htmlBlock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $htmlBlock->setDeleted(true);
            $em->persist($htmlBlock);
            $em->flush();
            $this->addFlash('success','Success - htmlBlock deleted');
        }

        return $this->redirectToRoute('control_htmlblocks_index');
    }

    /**
     * Creates a form to delete a HtmlBlocks entity.
     *
     * @param HtmlBlocks $htmlBlock The HtmlBlocks entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(HtmlBlocks $htmlBlock)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_htmlblocks_delete', array('id' => $htmlBlock->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
