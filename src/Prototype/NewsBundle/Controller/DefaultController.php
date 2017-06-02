<?php

namespace Prototype\NewsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\NewsBundle\Entity\News;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;
use Doctrine\ORM\Tools\Pagination\Paginator;


class DefaultController extends Controller
{
    /**
     * @Route("/test-news/overview", name="embed_news_overview")
     * @ProtoCmsComponent("News Overview", active=true, routeName="embed_news_overview")
     */
    public function embedNewsOverviewAction($request)
    {
        $perpage = 10;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT e FROM PrototypeNewsBundle:News e WHERE e.deleted = 0 AND e.active = 1 ORDER BY e.publishDate DESC");
        $paginatedNews = $this->get('knp_paginator')->paginate($query, $request->query->getInt('page', 1), $perpage);

        return $this->render('@theme/news/embedNewsOverview.html.twig', array(
            'articles' => $paginatedNews
        ));
    }

    /**
     * @Route("/test-news/{news_slug}", name="embed_news_article")
     * @ProtoCmsComponent("News Article", slug="{news_slug}", slugEntity="News", active=true, routeName="embed_news_article")
     */
    public function embedNewsArticleAction($request, $news_slug)
    {

        $em = $this->getDoctrine()->getManager();
        $news = $em->getRepository('PrototypeNewsBundle:News')->findOneBy(array('slug'=> $news_slug, 'deleted' => false, 'active' => true));

        if($this->container->getParameter('multilingual')){
            $news = $em->getRepository('PrototypeNewsBundle:News')->findSlugWithLocale($request->getLocale(), $news_slug);
            if(!$news){ return new Response('Not Found'); }
        }

        if(!$news){
            return new Response('Not Found');
        }

        //$news->setTranslatableLocale($request->getLocale());
        //$em->refresh($news);

        return $this->render('@theme/news/embedNewsArticle.html.twig', array(
            'news' => $news,
        ));
    }
}
