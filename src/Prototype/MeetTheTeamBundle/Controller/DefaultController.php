<?php

namespace Prototype\MeetTheTeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DefaultController extends Controller
{


    /**
     * @Route("/pcgc-meettheteam", name="embed_meettheteam")
     * @ProtoCmsComponent("Embed Meet the Team", active=true, routeName="embed_meettheteam")
     */
    public function embedNewsOverviewAction($request)
    {
        $perpage = 10;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT e FROM PrototypeMeetTheTeamBundle:TeamMember e WHERE e.deleted = 0 AND e.active = 1");
        $teamMembers = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

        return $this->render('@theme/meettheteam/meettheteam.html.twig', array(
            'teamMembers' => $teamMembers
        ));
    }

    /**
     * @Route("/pcgc-meettheteam/{teammember_slug}", name="embed_teammember")
     * @ProtoCmsComponent("Embed Team Member", slug="{teammember_slug}", slugEntity="TeamMember", active=true, routeName="embed_teammember")
     */
    public function embedTeamMemberAction($request, $teammember_slug)
    {

        $em = $this->getDoctrine()->getManager();
        $teamMember = $em->getRepository('PrototypeMeetTheTeamBundle:TeamMember')->findOneBy(array('slug'=> $teammember_slug, 'deleted' => false, 'active' => true));

        if($this->container->getParameter('multilingual')){
            $teamMember = $em->getRepository('PrototypeMeetTheTeamBundle:TeamMember')->findSlugWithLocale($request->getLocale(), $teammember_slug);
        }

        if(!$teamMember){
            return new Response('Not Found');
        }
        return $this->render('@theme/meettheteam/embedTeamMember.html.twig', array(
            'teamMember' => $teamMember,
        ));
    }
}
