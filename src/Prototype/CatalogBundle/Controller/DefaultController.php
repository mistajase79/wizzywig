<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;

/**
 * Store Bundle Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/pcgc-catalog", name="embed_test_catalog")
     * @Route("/store/{slug}", name="en_store_router", requirements={"slug"=".+"})
     * @Route("/almacenar/{slug}", name="es_store_router", requirements={"slug"=".+"})
     * @Route("/geschaft/{slug}", name="de_store_router", requirements={"slug"=".+"})
     */
    public function storeRouter(Request $request, $slug)
    {
        if($request->query->has('_locale')){
            if($request->query->get('_locale') != $request->getLocale() ){
                $request->setLocale($request->query->get('_locale'));
                $request->getSession()->set('_locale', $request->query->get('_locale'));
                return $this->redirect($request->getUri());
            }
        }
        //$this->container->get('pcgc_page_service')->refreshPageifLocalChanged($request);

        $locale = $request->getLocale();

        $productsArray = array();
        $categoryTree = array();
        $subcategoriesArray = array();
        $breadcrumbs = array();

        $em = $this->getDoctrine()->getManager();

        //get inherited page data
        $pageEntity = $this->container->get('pcgc_page_service')->getInheritedCMSPage($request);
        $urlSegments = explode('/',$slug);

        //get category tree
        foreach($urlSegments as $slug){
            $category = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findActiveSlugWithLocale($locale, $slug);
            if($category){
                $categoryTree[] = $category;
            }
        }


        //check if this is a product page
        $productCheck = $em->getRepository('PrototypeCatalogBundle:CatalogProducts')->findActiveSlugWithLocale($locale, $slug);
        if($productCheck){

            $fetchSubcategories = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findBy(array('parent'=> $productCheck->getCategory()->getId(), 'active' => 1, 'deleted' => 0));
            foreach($fetchSubcategories as $category){
                $subcategoriesArray[$categoryCheck->getId()] = $category;
            }

            return $this->render('@theme/catalog/product.html.twig', array(
                'subcategoriesArray' => $subcategoriesArray,
                'product' => $productCheck,
                'page' => $pageEntity,
                'categoryTree' => array_reverse($categoryTree)
            ));
        }

        //check if this is a category page
        $categoryCheck = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findActiveSlugWithLocale($locale, $slug);
        if($categoryCheck){
            //get subcategories - we can fetch products via relations
            $fetchSubcategories = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findBy(array('parent'=> $categoryCheck->getId(), 'active' => 1, 'deleted' => 0));
            foreach($fetchSubcategories as $category){
                $subcategoriesArray[$categoryCheck->getId()] = $category;
            }

            return $this->render('@theme/catalog/catalog.html.twig', array(
                'category' => $categoryCheck,
                'subcategoriesArray' => $subcategoriesArray,
                'page' => $pageEntity,
                'categoryTree' => array_reverse($categoryTree)
            ));
        }


    }


    public function showBasketPage($request, $currentUrl)
    {
    }

    public function showCheckoutPage($request, $currentUrl)
    {
    }

    public function fetchCategoryMenuAction(Request $request)
    {

        $locale = $request->getLocale();
        $currentUrl = $request->getUri();

        $em = $this->getDoctrine()->getManager();
        $menuData = $em->getRepository('PrototypeCatalogBundle:CatalogCategories')->findBy(array('active' => 1, 'deleted' => 0), array('title'=>'ASC'));

        if($currentUrl != null){
            $url = explode('/',$currentUrl);
            $page_slug = $url[1];
        }else{
            $page_slug = null;
        }

        $pageEntity = $this->container->get('pcgc_page_service')->getInheritedCMSPage($request);
        if($pageEntity != null){ $page_slug = $pageEntity->getSlug(); }

        $items = array();
        foreach($menuData as $menuItem){

            if($menuItem->getParent() !== null){
                $parent = $menuItem->getParent()->getId();
            }else{
                $parent = 0;
            }

            $items[] = array(
                'menu_item_id'	=> $menuItem->getId(),
                'navtitle'		=> $menuItem->getTitle(),
                'slug' 			=> $menuItem->getSlug(),
                'product_count' => count($menuItem->getProducts()),
                'menu_parent_id' => $parent,
            );
        }

        $content = "<ul>";
        $content .= $this->makeMenu($items, 0, 0, count($menuData),$currentUrl, $page_slug);
        $content .= "</ul>";

        return new Response($content);
    }


    function makeMenu($menuData, $no = 0, $currentcount, $length, $currentUrl, $page_slug, $categoryPath=array()) {

        $child = $this->hasMenuChildren($menuData, $no);

        if (empty($child)){ return "";}

        if($currentcount > 0 ){ $content = "<ul>\n"; }else{	$content = ""; }
        $currentcount++;

        foreach ( $child as $item ) {

            $categoryPath[] = $item['slug'];

            //if("/".$item['slug'] == $currentUrl){ $active ="class='active' ";}else{$active =""; }
            if(in_array($item['slug'], explode('/', $currentUrl)) ){ $active ="class='active' ";}else{$active =""; }
            $parentpath = "/".implode('/',$categoryPath);
            $content .= "<li $active >\n";
            $content .= "<a href='/".$page_slug.$parentpath."' >".$item['navtitle']." <span>(".$item['product_count'].")</span></a>\n";
            $content .=  $this->makeMenu($menuData, $item['menu_item_id'], $currentcount, $length, $currentUrl, $page_slug, $categoryPath);
            $content .= "</li>\n";
            array_pop($categoryPath);
            $currentcount++;
        }

        $content .= "</ul>\n";

        return $content;
    }


    function hasMenuChildren($menuData, $id) {
        return array_filter($menuData, function ($var) use($id) {
            return $var['menu_parent_id'] == $id;
        });
    }


    function makeCatMenu($menuData, $no = 0, $currentcount, $length, $currentUrl, $page_slug, $categoryPath=array()) {

        $child = $this->hasCatChildren($menuData, $no);

        if (empty($child)){ return "";}



        foreach ( $child as $item ) {

            $categoryPath[] = $item['slug'];

            //if("/".$item['slug'] == $currentUrl){ $active ="class='active' ";}else{$active =""; }
            if(in_array($item['slug'], explode('/', $currentUrl)) ){ $active ="class='active' ";}else{$active =""; }
            $parentpath = "/".implode('/',$categoryPath);
            $content .= "<a href='/".$page_slug.$parentpath."' >".$item['navtitle']." <span>(".$item['product_count'].")</span></a>\n";
            $content .=  $this->makeCatMenu($menuData, $item['menu_item_id'], $currentcount, $length, $currentUrl, $page_slug, $categoryPath);
            array_pop($categoryPath);
        }


        return $content;
    }

    function hasCatChildren($menuData, $id) {
        return array_filter($menuData, function ($var) use($id) {
            return $var['parent_id'] == $id;
        });
    }

    

}
