<?php

namespace Prototype\EnquiryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\EnquiryBundle\Entity\Enquiry;
//use Prototype\EnquiryBundle\Form\EnquiryType;
const EnquiryType ='Prototype\EnquiryBundle\Form\EnquiryType';

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Enquiry controller.
 *
 * @Route("/control/enquiries")
 */
class EnquiryController extends Controller
{
	/**
	* Lists all Enquiry entities.
	*
	* @Route("/", name="control_enquiry_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Enquiries",active=true, routeName="control_enquiry_index", icon="glyphicon glyphicon-envelope", menuPosition=60)
	*/
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

        $perpage =  $request->query->getInt('perpage', 25);
        $parameters = array('search' => "%".$request->query->get('search')."%" );
        $dql = "SELECT e FROM PrototypeEnquiryBundle:Enquiry e WHERE e.deleted = 0 AND (e.name LIKE :search OR e.email LIKE :search OR e.subject LIKE :search OR e.message LIKE :search) ORDER BY e.id DESC";
        $query = $em->createQuery($dql)->setParameters($parameters);
        $pagination = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

		return $this->render('PrototypeEnquiryBundle:enquiry:index.html.twig', array(
            'pagination' => $pagination,
            'perpage' => $perpage,
            'search' => $request->query->get('search')
		));
	}

    /**
     * Creates a new Enquiry entity.
     *
     * @Route("/new", name="control_enquiry_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(EnquiryType, $enquiry);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($enquiry);
                $em->flush();
                $this->addFlash('success','Success - Enquiry created');
                return $this->redirectToRoute('control_enquiry_index');
            }else{
                $this->addFlash('error','Error - Enquiry not saved');
            }
        }

        return $this->render('PrototypeEnquiryBundle:enquiry:new.html.twig', array(
            'enquiry' => $enquiry,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Enquiry entity.
     *
     * @Route("/{id}", name="control_enquiry_show")
     * @Method("GET")
     */
    public function showAction(Enquiry $enquiry)
    {
        $deleteForm = $this->createDeleteForm($enquiry);
        $em = $this->getDoctrine()->getManager();
        $enquiry->setViewed(true);
        $em->persist($enquiry);
        $em->flush();

        return $this->render('PrototypeEnquiryBundle:enquiry:show.html.twig', array(
            'enquiry' => $enquiry,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Enquiry entity.
     *
     * @Route("/{id}/edit", name="control_enquiry_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Enquiry $enquiry)
    {
        $deleteForm = $this->createDeleteForm($enquiry);
        $editForm = $this->createForm(EnquiryType, $enquiry);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($enquiry);
                $em->flush();
                $this->addFlash('success','Success - Enquiry updated');
                return $this->redirectToRoute('control_enquiry_edit', array('id' => $enquiry->getId()));
            }else{
                $this->addFlash('error','Error - Enquiry not saved');
            }
        }

        return $this->render('PrototypeEnquiryBundle:enquiry:edit.html.twig', array(
            'enquiry' => $enquiry,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Enquiry entity.
     *
     * @Route("/{id}/delete", name="control_enquiry_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Enquiry $enquiry)
    {
        $form = $this->createDeleteForm($enquiry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $enquiry->setDeleted(true);
            $em->persist($enquiry);
            $em->flush();
            $this->addFlash('success','Success - Enquiry deleted');
        }

        return $this->redirectToRoute('control_enquiry_index');
    }

    /**
     * Creates a form to delete a Enquiry entity.
     *
     * @param Enquiry $enquiry The Enquiry entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Enquiry $enquiry)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_enquiry_delete', array('id' => $enquiry->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Deletes multiple Enquiry entities.
     *
     * @Route("/multidelete", name="control_enquiry_multidelete")
     * @Method("POST")
     */
    public function multiDeleteAction(Request $request)
    {
        $items = $request->request->get('selected');
        $em = $this->getDoctrine()->getManager();

        foreach($items as $id=>$item){
            $enquiry = $em->getRepository('PrototypeEnquiryBundle:Enquiry')->find($id);
            $enquiry->setDeleted(true);
            $em->persist($enquiry);
        }
        $em->flush();
        $this->addFlash('success','Success - '.count($items).' Enquiries deleted');

        return $this->redirectToRoute('control_enquiry_index');
    }

    /**
     * Deletes multiple Enquiry entities.
     *
     * @Route("/mark-all-as-read", name="control_enquiry_mark_all_as_read")
     * @Method("POST")
     */
    public function markAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $enquiries = $em->getRepository('PrototypeEnquiryBundle:Enquiry')->findBy(
            array(
                'delete'=>false,
                'viewed'=>false
            ));

        foreach($enquiry as $enquiries){
            $enquiry->setViewed(true);
            $em->persist($enquiry);
        }
        $em->flush();
        $this->addFlash('success','Success - All Enquiries marked as read');

        return $this->redirectToRoute('control_enquiry_index');
    }
}
