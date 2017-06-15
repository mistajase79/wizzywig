<?php

namespace Prototype\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\BlogBundle\Entity\BlogPosts;
use Symfony\Component\Form\FormError;
//use Prototype\BlogBundle\Form\BlogPostsTranslationType;
const BlogPostsTranslationType = 'Prototype\BlogBundle\Form\BlogPostsTranslationType';

/**
 * BlogPostsTranslation controller.
 *
 * @Route("/control/blogposts")
 */
class BlogPostsTranslationController extends Controller
{

    /**
     * Creates a new BlogPostsTranslation entity.
     *
     * @Route("/blog-post-translation/{id}/new", name="control_blogposts_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, BlogPosts $blogPost)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($blogPost, $locales);

        $form = $this->createForm(BlogPostsTranslationType, $blogPost, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('blog_post_translation');
            $blogPost = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($blogPost, $formData);
            $blogPost->setTranslatableLocale($formData['translatableLocale']); // change locale
            $em->persist($blogPost);
            $em->flush();
            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_blogposts_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypeBlogBundle:blogposts:saveTranslation.html.twig', array(
            'blogPost' => $blogPost,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing BlogPostsTranslation entity.
     *
     * @Route("/blog-post-translation/{id}/edit/{locale}", name="control_blogposts_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, BlogPosts $blogPost, $locale)
    {
        //$deleteForm = $this->createDeleteForm($blogPost, $locale);

        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $blogPost->setTranslatableLocale($locale);
        $em->refresh($blogPost);

        //create form
        $form = $this->createForm(BlogPostsTranslationType, $blogPost, array('currentLocale' =>$locale ));
        $blogPost->setTranslatableLocale($locale); // change locale
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('blog_post_translation');
            $blogPost = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($blogPost, $formData);
            $blogPost->setTranslatableLocale($locale); // set locale

            $em->persist($blogPost);
            $em->flush();

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            return $this->redirectToRoute('control_blogposts_index');
            ///////////////////////////////////////////
        }


        return $this->render('PrototypeBlogBundle:blogposts:saveTranslation.html.twig', array(
            'blogPost' => $blogPost,
            'form' => $form->createView(),
            'locale' => $locale
        ));
    }

}
