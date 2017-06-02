<?php

namespace Prototype\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\PageBundle\Entity\Templates;
const TemplateType ='Prototype\AdminBundle\Form\TemplateType';

/**
 * Developer controller.
 *
 * @Route("/")
 * @Security("has_role('ROLE_DEVELOPER')")
 */
class DeveloperController extends Controller
{
    /* ************************* */
    /* DEVELOPER ONLY ACTIONS    */
    /* ************************* */

    /**
    * This is a simple menu placeholder function
    *
    * @Route("/", name="control_developer_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Developer", role="ROLE_DEVELOPER", active=true, routeName="control_developer_index", icon="fa fa-code", dontLink=true, menuPosition=9999999)
    */
    public function developerIndexAction(){ return false; }

    /**
    * Lists all Pages with Components with assigned attributes.
    *
    * @Route("/developer/component-info", name="control_developer_components_info")
    * @Method("GET")
    * @ProtoCmsAdminDash("Component Info",
    * role="ROLE_DEVELOPER",
    * active=true,
    * routeName="control_developer_components_info",
    * icon="glyphicon glyphicon-list",
    * parentRouteName="control_developer_index"
    * )
    */
    public function developerComponentInfoAction()
    {

        $data = array();
        $componentData = array();
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();

        foreach($cmsComponentArray as $cmsComponent){
            $componentData[$cmsComponent['route']] = $cmsComponent;
        }

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('PrototypePageBundle:Page')->findBy(array('deleted'=>false));

        //echo "<pre>".print_r($cmsComponentArray, true)."</pre>";
        $assigned = array();
        foreach($pages as $page){
            $pageComps = $page->getComponents();
            if(count($pageComps) >0){
                //echo "<pre>".print_r($pageComps, true)."</pre>";
                $assignComps = array();
                foreach($pageComps as $pageComp){
                    if((!empty($pageComp['route']) )||($pageComp['route'] !="")){
                        if(!in_array($pageComp['route'],$assigned)){ $assigned[] = $pageComp['route']; }
                        $action = $this->routeToControllerName($pageComp['route']);
                        $controllerFunction = explode('::',$action['_controller']);
                        $assignComps[] = array('position'=>$pageComp['position'], 'component' => $componentData[$pageComp['route']], 'action'=> end($controllerFunction));
                    }
                }
                if(count($assignComps) >0){
                    $data[] = array('page'=>$page, 'components'=>$assignComps);
                }
            }
        }

        //echo "<pre>".print_r($cmsComponentArray, true)."</pre>";
        //\Doctrine\Common\Util\Debug::dump($data);
        $cmsComponentArrayExtra = array();
        foreach($cmsComponentArray as $cmsComponent){
            $controllerFunction = "";
            if((!empty($cmsComponent['route']) )||($cmsComponent['route'] !="")){
                $action = $this->routeToControllerName($cmsComponent['route']);
                $actionArray = explode('::',$action['_controller']);
                $controllerFunction = end($actionArray);
                //echo "<pre>".print_r($action, true)."</pre>";
            }

            if(in_array($cmsComponent['route'],$assigned)){
                $inUse = true;
            }else{
                $inUse = false;
            }

            if($cmsComponent['componentType'] != "segment"){
                $cmsComponentArrayExtra[] = array(
                    'name' => $cmsComponent['name'],
                    'route' => $cmsComponent['route'],
                    'componentType' => $cmsComponent['componentType'],
                    'bundle' => $cmsComponent['bundle'],
                    'action' => $controllerFunction,
                    'inUse' => $inUse
                );
            }

        }

        return $this->render('PrototypeAdminBundle:Developer:component-info.html.twig', array(
            'pagedata' => $data,
            'cmsComponentArrayExtra' => $cmsComponentArrayExtra,
        ));
    }

    /**
    * Lists all templates for pages.
    *
    * @Route("/developer/templates/index", name="control_developer_templates_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Templates",
    * role="ROLE_DEVELOPER",
    * active=true,
    * routeName="control_developer_templates_index",
    * icon="glyphicon glyphicon-list",
    * parentRouteName="control_developer_index"
    * )
    */
    public function developerTemplatesIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $templates = $em->getRepository('PrototypePageBundle:Templates')->findBy(array('deleted'=>false));
        return $this->render('PrototypeAdminBundle:Developer:templates-index.html.twig', array(
            'templates' => $templates,
        ));
    }

    /**
     * Creates a new Template entity.
     *
     * @Route("/developer/templates/new", name="control_developer_templates_new")
     * @Method({"GET", "POST"})
     */
    public function newTemplateAction(Request $request)
    {
        $template = new Templates();

        $bundles = $this->container->get('pcgc_page_service')->fetchProtoCmsBundles();
        $form = $this->createForm(TemplateType, $template, array(
            'bundles' => $bundles)
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($template);
                $em->flush();
                $this->addFlash('success','Success - template created');
                return $this->redirectToRoute('control_developer_templates_index');
            }else{
                $this->addFlash('error','Error - news not saved');
            }
        }
        return $this->render('PrototypeAdminBundle:Developer:templates-modify.html.twig', array(
            'template' => $template,
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit Template entity.
     *
     * @Route("/developer/templates/{id}/edit", name="control_developer_templates_edit")
     * @Method({"GET", "POST"})
     */
    public function editTemplateAction(Request $request, \Prototype\PageBundle\Entity\Templates $template)
    {

        $bundles = $this->container->get('pcgc_page_service')->fetchProtoCmsBundles();

        $form = $this->createForm(TemplateType, $template, array(
            'bundles' =>$bundles)
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($template);
                $em->flush();
                $this->addFlash('success','Success - template updated');
                return $this->redirectToRoute('control_developer_templates_index');
            }else{
                $this->addFlash('error','Error - news not saved');
            }
        }
        return $this->render('PrototypeAdminBundle:Developer:templates-modify.html.twig', array(
            'template' => $template,
            'form' => $form->createView(),
        ));
    }


    //Helper function
    function routeToControllerName($routename) {
        $routes = $this->get('router')->getRouteCollection();
        return $routes->get($routename)->getDefaults();
    }


}
