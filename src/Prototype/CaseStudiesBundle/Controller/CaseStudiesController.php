<?php

namespace Prototype\CaseStudiesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\CaseStudiesBundle\Entity\CaseStudies;
//use Prototype\CaseStudiesBundle\Form\CaseStudiesType;
const CaseStudiesType ='Prototype\CaseStudiesBundle\Form\CaseStudiesType';

/**
 * CaseStudies controller.
 *
 * @Route("/control/case-studies")
 */
class CaseStudiesController extends Controller
{
    /**
    * Lists all CaseStudies entities.
    *
    * @Route("/", name="control_casestudies_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Case Studies", active=true, routeName="control_casestudies_index", role="ROLE_CASESTUDIES_EDITOR", icon="glyphicon glyphicon-list", menuPosition=40, parentRouteName="control_content_index")
    */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $caseStudies = $em->getRepository('PrototypeCaseStudiesBundle:CaseStudies')->findBy(array('deleted'=>false));

        return $this->render('PrototypeCaseStudiesBundle:casestudies:index.html.twig', array(
            'caseStudies' => $caseStudies,
        ));
    }

    /**
     * Creates a new CaseStudies entity.
     *
     * @Route("/new", name="control_casestudies_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $caseStudy = new CaseStudies();
        $form = $this->createForm(CaseStudiesType, $caseStudy);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $caseStudy->uploadFile();
                $em->persist($caseStudy);
                $em->flush();
                $this->addFlash('success','Success - Case Study created');
                return $this->redirectToRoute('control_casestudies_index');
            }else{
                $this->addFlash('error','Error - Case Study not saved');
            }
        }

        return $this->render('PrototypeCaseStudiesBundle:casestudies:new.html.twig', array(
            'caseStudy' => $caseStudy,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CaseStudies entity.
     *
     * @Route("/{id}", name="control_casestudies_show")
     * @Method("GET")
     */
    public function showAction(CaseStudies $caseStudy)
    {
        $deleteForm = $this->createDeleteForm($caseStudy);

        return $this->render('PrototypeCaseStudiesBundle:casestudies:show.html.twig', array(
            'caseStudy' => $caseStudy,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CaseStudies entity.
     *
     * @Route("/{id}/edit", name="control_casestudies_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CaseStudies $caseStudy)
    {
        $deleteForm = $this->createDeleteForm($caseStudy);
        $editForm = $this->createForm(CaseStudiesType, $caseStudy);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $caseStudy->uploadFile();
                $em->persist($caseStudy);
                $em->flush();
                $this->addFlash('success','Success - Case Study updated');
                return $this->redirectToRoute('control_casestudies_edit', array('id' => $caseStudy->getId()));
            }else{
                $this->addFlash('error','Error - Case Study not saved');
            }
        }

        return $this->render('PrototypeCaseStudiesBundle:casestudies:edit.html.twig', array(
            'caseStudy' => $caseStudy,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CaseStudies entity.
     *
     * @Route("/{id}", name="control_casestudies_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CaseStudies $caseStudy)
    {
        $form = $this->createDeleteForm($caseStudy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $caseStudy->setDeleted(true);
            $em->persist($caseStudy);
            $em->flush();
            $this->addFlash('success','Success - Case Study deleted');
        }

        return $this->redirectToRoute('control_casestudies_index');
    }

    /**
     * Creates a form to delete a CaseStudies entity.
     *
     * @param CaseStudies $caseStudy The CaseStudies entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CaseStudies $caseStudy)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_casestudies_delete', array('id' => $caseStudy->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
