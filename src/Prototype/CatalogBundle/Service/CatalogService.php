<?php

namespace Prototype\CatalogBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;


class CatalogService extends Controller
{

	public function __construct($kernel,RegistryInterface $doctrine){
	    $this->container = $kernel->getContainer();
		$this->doctrine = $doctrine;
	}

    //This function builds the parent page selector
    public function buildCategorySelector($currentSelected =null)
    {

        $pages = $this->doctrine->getRepository('PrototypeCatalogBundle:CatalogCategories')->findBy(array('deleted' => 0));
        $item = array();
        foreach($pages as $page){
            if($page->getParent() == null){
                $parent = null;
            }else{
                $parent =$page->getParent()->getId();
            }

            $items[] = array(
                'menu_item_id'	=> $page->getId(),
                'navtitle'		=> $page->getTitle(),
                'menu_parent_id' => $parent,
            );
        }

        $content  = "<ul>";
        $content .= $this->makeMenu($items, 0, 0, count($pages),$currentSelected);
        $content .= "</ul>";

        return $content;
    }


    function makeMenu($menuData, $no = 0, $currentcount, $length, $currentSelected) {

        $child = $this->hasMenuChildren($menuData, $no);
        if (empty($child)){	return "";}
        if($currentcount > 0 ){ $content = "<ul>\n"; }else{	$content = ""; }
        $currentcount++;
        foreach ( $child as $item ) {
            $content .= "<li id='". $item['menu_item_id']."' >";
            if($item['menu_item_id'] == $currentSelected){ $active ="class='jstree-clicked' ";}else{$active =""; }
            $content .= "<a $active href='#' >".$item['navtitle']."</a>";
            $content .=  $this->makeMenu($menuData, $item['menu_item_id'], $currentcount, $length, $currentSelected);
            $content .= "</li>";
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


}
