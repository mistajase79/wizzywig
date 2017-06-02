<?php

namespace Prototype\MenuBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\MenuBundle\Entity\Menu;
use Prototype\MenuBundle\Entity\MenuItems;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;
//use Prototype\MenuBundle\Form\MenuType;
const MenuType ='Prototype\MenuBundle\Form\MenuType';

/**
 * Menu controller.
 *
 * @Route("/control")
 */
class MenuController extends Controller
{
    /**
    * Lists all Menu entities.
    *
    * @Route("/menu", name="control_menu_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Menus",
    *					active=true,
    *					routeName="control_menu_index",
    *					role="ROLE_MENU_EDITOR",
    *                   icon="glyphicon glyphicon-list",
    *                   menuPosition=30
    *					)
    */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $menus = $em->getRepository('PrototypeMenuBundle:Menu')->findBy(array('deleted' => false));

        return $this->render('PrototypeMenuBundle:menu:index.html.twig', array(
            'menus' => $menus,
        ));
    }

    /**
     * Creates a new Menu entity.
     *
     * @Route("/menu/new", name="control_menu_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType, $menu);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('PrototypePageBundle:Page')->findBy(array('deleted' => false));

        $jsonItems = array();

        if ($form->isSubmitted()){

            $menuData =  $request->request->get('menu');
            $jsonItems = json_decode($menuData['menu_json'], true);
            //print_r($jsonItems);

            if ($form->isValid()) {
                $em->persist($menu);

                foreach($jsonItems as $jsonItem){
                    $page = $em->getRepository('PrototypePageBundle:Page')->find($jsonItem['pageId']);
                    //$page->setNavTitle($jsonItem['navtitle']);
                    $menuItem = new MenuItems();
                    $menuItem->setPageId($page);
                    $menuItem->setMenuItemId($jsonItem['menu_item_id']);
                    $menuItem->setMenuParentId($jsonItem['menu_parent_id']);
                    $menuItem->setNameOverride($jsonItem['navtitle']);
                    $menuItem->setMenuId($menu);
                    $em->persist($menuItem);

                }

                $em->persist($menu);
                $em->flush();
                $this->addFlash('success','Menu Created');
                return $this->redirectToRoute('control_menu_index');
                //return $this->redirectToRoute('control_menu_show', array('id' => $menu->getId()));
            }else{
                $this->addFlash('error','Menu not saved - check for errors');
            }
        }

        return $this->render('PrototypeMenuBundle:menu:new.html.twig', array(
            'menu' => $menu,
            'pages' => $pages,
            'jsonItems' => $jsonItems,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Menu entity.
     *
     * @Route("/menu/{id}", name="control_menu_show")
     * @Method("GET")
     */
    public function showAction(Menu $menu)
    {
    //$deleteForm = $this->createDeleteForm($menu);

        return $this->render('PrototypeMenuBundle:menu:show.html.twig', array(
        'menu' => $menu
        //	'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Menu entity.
     *
     * @Route("/menu/{id}/edit", name="control_menu_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Menu $menu)
    {
        //$deleteForm = $this->createDeleteForm($menu);
        $form = $this->createForm(MenuType, $menu);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('PrototypePageBundle:Page')->findBy(array('deleted' => false));

        $jsonItems = $menu->getMenuItems()->toArray();

        if ($form->isSubmitted()){

            $menuData =  $request->request->get('menu');
            $jsonItems = json_decode($menuData['menu_json'], true);


            if ($form->isValid()) {
                $em->persist($menu);

                $menuItems = $em->getRepository('PrototypeMenuBundle:MenuItems')->findBy(array('menuId'=>$menu->getId()) );
                foreach($menuItems as $menuItem){
                    $em->remove($menuItem);
                }

                foreach($jsonItems as $jsonItem){
                    $page = $em->getRepository('PrototypePageBundle:Page')->find($jsonItem['pageId']);
                    //$page->setNavTitle($jsonItem['navtitle']);
                    $menuItem = new MenuItems();
                    $menuItem->setPageId($page);
                    $menuItem->setMenuItemId($jsonItem['menu_item_id']);
                    $menuItem->setMenuParentId($jsonItem['menu_parent_id']);
                    $menuItem->setNameOverride($jsonItem['navtitle']);
                    $menuItem->setMenuId($menu);
                    $em->persist($menuItem);

                }

                $em->persist($menu);
                $em->flush();
                $this->addFlash('success','Menu updated');
                return $this->redirectToRoute('control_menu_index');
            }else{
                $this->addFlash('error','Menu not saved - check for errors');
            }
        }



        return $this->render('PrototypeMenuBundle:menu:new.html.twig', array(
            'menu' => $menu,
            'pages' => $pages,
            'jsonItems' => $jsonItems,
            'form' => $form->createView(),
        ));
    }

    /**
     * Deletes a Menu entity.
     *
     * @Route("/menu/delete/{id}", name="control_menu_delete")
     */
    public function deleteAction(Request $request, Menu $menu)
    {
        $em = $this->getDoctrine()->getManager();
        $menu->setDeleted(true);
        $this->addFlash('success','Menu deleted');
        $em->flush();

        return $this->redirectToRoute('control_menu_index');
    }

    /**
     * Creates a form to delete a Menu entity.
     *
     * @param Menu $menu The Menu entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Menu $menu)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_menu_delete', array('id' => $menu->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
