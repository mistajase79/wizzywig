<?php

namespace Prototype\MenuBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{

    public function fetchMenuAction(Request $request, $identifier, $currentUrl)
    {

        $locale = $request->getLocale();
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('PrototypeMenuBundle:Menu')->findOneBy(array('identifier' => $identifier));
        if(!$menu){
            throw $this->createNotFoundException('PCGC: The menu \''.$indentifier.'\' does not exist');
        }

        $menuData = $menu->getMenuItems();

        $item = array();
        foreach($menuData as $menuItem){

            $page =  $menuItem->getPageId();

            $navTitle = $page->getNavTitle();

            if($locale == 'en'){//no navtitle override for translations
                if($menuItem->getNameOverride() !=""){ $navTitle = $menuItem->getNameOverride(); }
            }

            $items[] = array(
                'menu_item_id'	=> $menuItem->getMenuItemId(),
                'navtitle'		=> $navTitle,
                'slug' 			=> $page->getSlug(),
                'menu_parent_id' => $menuItem->getMenuParentId(),
            );
        }

        $content = "<ul>";
        $content .= $this->makeMenu($items, 0, 0, count($menuData),$currentUrl);
        $content .= "</ul>";

        return new Response($content);
    }


    function makeMenu($menuData, $no = 0, $currentcount, $length, $currentUrl) {

        $child = $this->hasMenuChildren($menuData, $no);

        if (empty($child)){	return "";}

        if($currentcount > 0 ){ $content = "<ul>\n"; }else{	$content = ""; }
        $currentcount++;

        foreach ( $child as $item ) {

            if("/".$item['slug'] == $currentUrl){ $active ="class='active' ";}else{$active =""; }

            $content .= "<li $active >";
            $content .= "<a href='/".$item['slug']."' >".$item['navtitle']."</a>";
            $content .=  $this->makeMenu($menuData, $item['menu_item_id'], $currentcount, $length, $currentUrl);
            $content .= "</li>";
            $currentcount++;
        }

        $content .= "</ul>\n";

        return $content;
    }


    public function fetchBootstrapMenuAction(Request $request, $identifier, $currentUrl)
    {

        $locale = $request->getLocale();
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('PrototypeMenuBundle:Menu')->findOneBy(array('identifier' => $identifier));
        if(!$menu){
            throw $this->createNotFoundException('PCGC: The menu \''.$indentifier.'\' does not exist');
        }

        $menuData = $menu->getMenuItems();

        $item = array();
        foreach($menuData as $menuItem){

            $page =  $menuItem->getPageId();

            $navTitle = $page->getNavTitle();
            if($locale == 'en'){ //no navtitle override for translations
                if($menuItem->getNameOverride() !=""){ $navTitle = $menuItem->getNameOverride(); }
            }

            $items[] = array(
                'menu_item_id'	=> $menuItem->getMenuItemId(),
                'navtitle'		=> $navTitle,
                'slug' 			=> $page->getSlug(),
                'menu_parent_id' => $menuItem->getMenuParentId(),
            );
        }

        $content = '<ul id="horz-nav-ul" class="nav navbar-nav sf-menu sf-js-enabled sf-shadow">';
        $content .= $this->makeBootstrapMenu($items, 0, 0, count($menuData),$currentUrl);
        $content .= "</ul>\n\r";

        return new Response($content);
    }


    function makeBootstrapMenu($menuData, $no = 0, $currentcount, $length, $currentUrl) {

        $child = $this->hasMenuChildren($menuData, $no);

        if (empty($child)){	return '';}

        if($currentcount > 0 ){ $content = "<ul class=\"dropdown-menu\" >\n\r"; }else{$content = ""; }
        $currentcount++;

        foreach ( $child as $item ) {

            if("/".$item['slug'] == $currentUrl){ $active ="active";}else{$active =""; }

            $children = $this->hasMenuChildren($menuData, $item['menu_item_id']);
            if (empty($children)){
                $content .= "<li class='".$active." ".count($children)."'>\n\r";
                $content .= "   <a href='/".$item['slug']."' >".$item['navtitle']."</a>\n\r";
            }else{
                $content .= "<li class='".$active." dropdown'>";
                $content .= "   <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>".$item['navtitle']."<span class='caret'></span></a>\n\r";
            }

            $content .=  $this->makeBootstrapMenu($menuData, $item['menu_item_id'], $currentcount, $length, $currentUrl);
            $content .= "</li>\n\r";
            $currentcount++;
        }

        if($currentcount > 0 ){ $content .= "</ul>\n\r"; }else{$content = ""; }

        return $content;
    }



    function hasMenuChildren($menuData, $id) {

        return array_filter($menuData, function ($var) use($id) {
            return $var['menu_parent_id'] == $id;
        });
    }
}
