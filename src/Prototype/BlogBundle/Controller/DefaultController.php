<?php

namespace Prototype\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DefaultController extends Controller
{
    /**
     * @Route("/pcgc-blog-recentposts", name="embed_blog_recent_posts")
     * @ProtoCmsComponent("Recent Blog Posts", active=true, routeName="embed_blog_recent_posts")
     */
    public function embedBlogRecentPostsAction($request)
    {
        $perpage = 3;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT e FROM PrototypeBlogBundle:BlogPosts e WHERE e.deleted = 0 AND e.active = 1 ORDER BY e.datePosted DESC");
        $paginatedPosts = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

        return $this->render('@theme/blog/embedPostsOverview.html.twig', array(
          'blogposts' => $paginatedPosts
        ));
    }

    /**
     * @Route("/pcgc-blog-category", name="embed_blog_category")
     * @ProtoCmsComponent("Blog Category", componentType="segment", active=true, routeName="embed_blog_category",  slug="{blogcategory_slug}", slugEntity="BlogCategories")
     * @ProtoCmsComponent("Blog Category", componentType="standard", active=true, routeName="embed_blog_category",  slug="{blogcategory_slug}", slugEntity="BlogCategories")

     */
    public function embedBlogCategoryAction($request)
    {
      $perpage = 3;
      $em = $this->getDoctrine()->getManager();
      $query = $em->createQuery("SELECT e FROM PrototypeBlogBundle:BlogPosts e WHERE e.deleted = 0 AND e.active = 1 ORDER BY e.datePosted DESC");
      $paginatedPosts = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

      return $this->render('@theme/blog/embedPostsOverview.html.twig', array(
        'blogposts' => $paginatedPosts
      ));
      return $this->render('@theme/blog/embedBlogCategory.html.twig');
    }

    /**
     * @Route("/pcgc-blog-sidebar", name="embed_blog_sidebar")
     * @ProtoCmsComponent("Blog Sidebar", active=true, routeName="embed_blog_sidebar")
     */
    public function embedBlogSidebarAction()
    {
      $limit = 3;
      $offset = 0;
      $em = $this->getDoctrine()->getManager();
      $recentPosts = $em->getRepository('PrototypeBlogBundle:BlogPosts')->findBy(
        array('deleted'=>false, 'active'=>true),
        array('datePosted' => 'DESC'),
        $limit, $offset);

      $blogCategories = $em->getRepository('PrototypeBlogBundle:BlogCategories')->findBy(
        array('deleted'=>false, 'active'=>true)
      );

      return $this->render('@theme/blog/embedBlogSidebar.html.twig', array(
        'recentPosts' => $recentPosts,
        'blogCategories' => $blogCategories
      ));

    }

    /**
     * @Route("/pcgc-blog-post", name="embed_blog_post")
     * @ProtoCmsComponent("Blog Post", active=true, routeName="embed_blog_post", slug="{blogpost_slug}", slugEntity="BlogPosts" )
     */
    public function embedBlogPostAction($request, $blogpost_slug)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('PrototypeBlogBundle:BlogPosts')->findOneBy(
          array('slug'=>$blogpost_slug, 'deleted'=>false, 'active'=>true)
        );

        return $this->render('@theme/blog/embedBlogPost.html.twig', array('post'=>$post));
    }
}
