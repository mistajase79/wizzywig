<?php

namespace Prototype\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\BlogBundle\Entity\BlogPosts;
//use Prototype\BlogBundle\Form\BlogPostsType;
const BlogPostsType ='Prototype\BlogBundle\Form\BlogPostsType';

/**
 * BlogPosts controller.
 *
 * @Route("/control/blogposts")
 */
class BlogPostsController extends Controller
{
    /**
    * Lists all BlogPosts entities.
    *
    * @Route("/", name="control_blogposts_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Blog", active=true, routeName="control_blogposts_index", icon="fa fa-newspaper-o", menuPosition=35)
    */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $blogPosts = $em->getRepository('PrototypeBlogBundle:BlogPosts')->findBy(array('deleted'=>false));


        return $this->render('PrototypeBlogBundle:blogposts:index.html.twig', array(
            'blogPosts' => $blogPosts,
        ));
    }

    /**
     * Creates a new BlogPosts entity.
     *
     * @Route("/new", name="control_blogposts_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $blogPost = new BlogPosts();
        $form = $this->createForm(BlogPostsType, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($blogPost);
                $em->flush();
                $this->addFlash('success','Success - BlogPosts created');
                return $this->redirectToRoute('control_blogposts_index');
            }else{
                $this->addFlash('error','Error - BlogPosts not saved');
            }
        }

        return $this->render('PrototypeBlogBundle:blogposts:new.html.twig', array(
            'blogPost' => $blogPost,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a BlogPosts entity.
     *
     * @Route("/{id}", name="control_blogposts_show")
     * @Method("GET")
     */
    public function showAction(BlogPosts $blogPost)
    {
        $deleteForm = $this->createDeleteForm($blogPost);

        return $this->render('PrototypeBlogBundle:blogposts:show.html.twig', array(
            'blogPost' => $blogPost,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing BlogPosts entity.
     *
     * @Route("/{id}/edit", name="control_blogposts_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, BlogPosts $blogPost)
    {
        $deleteForm = $this->createDeleteForm($blogPost);
        $editForm = $this->createForm(BlogPostsType, $blogPost);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($blogPost);
                $em->flush();
                $this->addFlash('success','Success - blogPost updated');
                return $this->redirectToRoute('control_blogposts_edit', array('id' => $blogPost->getId()));
            }else{
                $this->addFlash('error','Error - blogPost not saved');
            }
        }

        return $this->render('PrototypeBlogBundle:blogposts:edit.html.twig', array(
            'blogPost' => $blogPost,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a BlogPosts entity.
     *
     * @Route("/{id}", name="control_blogposts_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, BlogPosts $blogPost)
    {
        $form = $this->createDeleteForm($blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $blogPost->setDeleted(true);
            $em->persist($blogPost);
            $em->flush();
            $this->addFlash('success','Success - blogPost deleted');
        }

        return $this->redirectToRoute('control_blogposts_index');
    }

    /**
     * Creates a form to delete a BlogPosts entity.
     *
     * @param BlogPosts $blogPost The BlogPosts entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(BlogPosts $blogPost)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_blogposts_delete', array('id' => $blogPost->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
