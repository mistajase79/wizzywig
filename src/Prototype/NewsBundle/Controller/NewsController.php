<?php

namespace Prototype\NewsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\NewsBundle\Entity\News;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

//use Prototype\NewsBundle\Form\NewsType;
const NewsType ='Prototype\NewsBundle\Form\NewsType';

/**
 * News controller.
 *
 * @Route("/control")
 */
class NewsController extends Controller
{
    /**
     * Lists all News entities.
     *
     * @Route("/news", name="control_news_index")
     * @Method("GET")
     * @ProtoCmsAdminDash("News Articles",
     *                     active=true,
     *                     routeName="control_news_index",
     *                     role="ROLE_NEWS_EDITOR",
     *                     icon="glyphicon glyphicon-comment",
     *                     menuPosition=20,
     *                     parentRouteName="control_content_index"
     *                    )
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $perpage =  $request->query->getInt('perpage', 25);
        $parameters = array('search' => "%".$request->query->get('search')."%" );
        $dql = "SELECT n FROM PrototypeNewsBundle:News n WHERE n.deleted = 0 AND (n.title LIKE :search OR n.subtitle LIKE :search OR n.article LIKE :search) ORDER BY n.id DESC";
        $query = $em->createQuery($dql)->setParameters($parameters);
        $news = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

        return $this->render('PrototypeNewsBundle:news:index.html.twig', array(
            'news' => $news,
            'perpage' => $perpage,
            'search' => $request->query->get('search')
        ));
    }

    /**
     * Creates a new News entity.
     *
     * @Route("/news/new", name="control_news_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $news = new News();
        $form = $this->createForm(NewsType, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                //replaced by imagemanager
                //$news->resizeImage($this->get('image.handling'));
                $em->persist($news);
                $em->flush();
                $this->addFlash('success','Success - news created');
                return $this->redirectToRoute('control_news_index');
                //return $this->redirectToRoute('control_news_show', array('id' => $news->getId()));
            }else{
                $this->addFlash('error','Error - news not saved');
            }
        }


        return $this->render('PrototypeNewsBundle:news:new.html.twig', array(
            'news' => $news,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/news/{id}", name="control_news_show")
     * @Method("GET")
     */
    public function showAction(News $news)
    {
    //$deleteForm = $this->createDeleteForm($news);

        return $this->render('PrototypeNewsBundle:news:show.html.twig', array(
        'news' => $news
        //    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing News entity.
     *
     * @Route("/news/{id}/edit", name="control_news_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, News $news)
    {
        //$deleteForm = $this->createDeleteForm($news);
        $editForm = $this->createForm(NewsType, $news);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                //replaced by imagemanager
                //$news->resizeImage($this->get('image.handling'));
                $em->persist($news);
                $em->flush();
                $this->addFlash('success','Success - news updated');
                return $this->redirectToRoute('control_news_index');
            }else{
                $this->addFlash('error','Error - news not saved');
            }
        }


        return $this->render('PrototypeNewsBundle:news:edit.html.twig', array(
            'news' => $news,
            'edit_form' => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a News entity.
     *
     * @Route("/news/delete/{id}", name="control_news_delete")
     */
    public function deleteAction(Request $request, News $news)
    {
        $em = $this->getDoctrine()->getManager();
        $news->setDeleted(true);
        $this->addFlash('success','Success - news deleted');
        $em->flush();

        return $this->redirectToRoute('control_news_index');
    }

    /**
     * Creates a form to delete a News entity.
     *
     * @param News $news The News entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(News $news)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_news_delete', array('id' => $news->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
