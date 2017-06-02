<?php

namespace Prototype\MeetTheTeamBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\MeetTheTeamBundle\Entity\TeamMember;
use Symfony\Component\Form\FormError;
//use Prototype\MeetTheTeamBundle\Form\TeamMemberTranslationType;
const TeamMemberTranslationType = 'Prototype\MeetTheTeamBundle\Form\TeamMemberTranslationType';

/**
 * TeamMemberTranslation controller.
 *
 * @Route("/control/meettheteam/teammember")
 */
class TeamMemberTranslationController extends Controller
{

    /**
     * Creates a new TeamMemberTranslation entity.
     *
     * @Route("/team-member-translation/{id}/new", name="control_meettheteam_teammember_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, TeamMember $teamMember)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($teamMember, $locales);

        $form = $this->createForm(TeamMemberTranslationType, $teamMember, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('team_member_translation');
            $teamMember = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($teamMember, $formData);
            $teamMember->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($teamMember);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_meettheteam_teammember_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeMeetTheTeamBundle:teammember:saveTranslation.html.twig', array(
            'teamMember' => $teamMember,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing TeamMemberTranslation entity.
     *
     * @Route("/team-member-translation/{id}/edit/{locale}", name="control_meettheteam_teammember_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TeamMember $teamMember, $locale)
    {
        //$deleteForm = $this->createDeleteForm($teamMember, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $teamMember->setTranslatableLocale($locale);
        $em->refresh($teamMember);

        //create form
        $form = $this->createForm(TeamMemberTranslationType, $teamMember, array('currentLocale' =>$locale ));
        $teamMember->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('team_member_translation');
            $teamMember = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($teamMember, $formData);
            $teamMember->setTranslatableLocale($locale); // set locale

            $em->persist($teamMember);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_meettheteam_teammember_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeMeetTheTeamBundle:teammember:saveTranslation.html.twig', array(
            'teamMember' => $teamMember,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
