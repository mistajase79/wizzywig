<?php

namespace Prototype\MeetTheTeamBundle\Controller;

use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\MeetTheTeamBundle\Entity\TeamMember;
//use Prototype\MeetTheTeamBundle\Form\TeamMemberType;
const TeamMemberType ='Prototype\MeetTheTeamBundle\Form\TeamMemberType';

/**
 * TeamMember controller.
 *
 * @Route("/control/meettheteam/teammember")
 */
class TeamMemberController extends Controller
{
    /**
    * Lists all TeamMember entities.
    *
    * @Route("/", name="control_meettheteam_teammember_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Team Members", active=true, routeName="control_meettheteam_teammember_index", icon="glyphicon glyphicon-list", menuPosition=9999,   parentRouteName="control_content_index")
    */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $teamMembers = $em->getRepository('PrototypeMeetTheTeamBundle:TeamMember')->findBy(array('deleted'=>false));


        return $this->render('PrototypeMeetTheTeamBundle:teammember:index.html.twig', array(
            'teamMembers' => $teamMembers,
        ));
    }

    /**
     * Creates a new TeamMember entity.
     *
     * @Route("/new", name="control_meettheteam_teammember_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $teamMember = new TeamMember();
        $form = $this->createForm(TeamMemberType, $teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($teamMember);
                $em->flush();
                $this->addFlash('success','Success - Team Member created');
                return $this->redirectToRoute('control_meettheteam_teammember_index');
            }else{
                $this->addFlash('error','Error - TeamMember not saved');
            }
        }

        return $this->render('PrototypeMeetTheTeamBundle:teammember:new.html.twig', array(
            'teamMember' => $teamMember,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a TeamMember entity.
     *
     * @Route("/{id}", name="control_meettheteam_teammember_show")
     * @Method("GET")
     */
    public function showAction(TeamMember $teamMember)
    {
        $deleteForm = $this->createDeleteForm($teamMember);

        return $this->render('PrototypeMeetTheTeamBundle:teammember:show.html.twig', array(
            'teamMember' => $teamMember,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing TeamMember entity.
     *
     * @Route("/{id}/edit", name="control_meettheteam_teammember_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TeamMember $teamMember)
    {
        $deleteForm = $this->createDeleteForm($teamMember);
        $editForm = $this->createForm(TeamMemberType, $teamMember);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($teamMember);
                $em->flush();
                $this->addFlash('success','Success - Team Member updated');
                return $this->redirectToRoute('control_meettheteam_teammember_edit', array('id' => $teamMember->getId()));
            }else{
                $this->addFlash('error','Error - teamMember not saved');
            }
        }

        return $this->render('PrototypeMeetTheTeamBundle:teammember:edit.html.twig', array(
            'teamMember' => $teamMember,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a TeamMember entity.
     *
     * @Route("/{id}", name="control_meettheteam_teammember_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, TeamMember $teamMember)
    {
        $form = $this->createDeleteForm($teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $teamMember->setDeleted(true);
            $em->persist($teamMember);
            $em->flush();
            $this->addFlash('success','Success - Team Member deleted');
        }

        return $this->redirectToRoute('control_meettheteam_teammember_index');
    }

    /**
     * Creates a form to delete a TeamMember entity.
     *
     * @param TeamMember $teamMember The TeamMember entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TeamMember $teamMember)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_meettheteam_teammember_delete', array('id' => $teamMember->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
