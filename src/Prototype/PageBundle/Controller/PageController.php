<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\PageBundle\Entity\Page;
use Prototype\PageBundle\Entity\Template;
use Prototype\PageBundle\Entity\UrlRouting;
use Prototype\PageBundle\Form\PageType;
use Symfony\Component\Form\FormError;

use Symfony\Component\DomCrawler\Crawler;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

const PageType = 'Prototype\PageBundle\Form\PageType';

/**
 * Page controller.
 *
 * @Route("/")
 */
class PageController extends Controller
{

    /**
    * Simple Parent Route for admin side-bar
    *
    * @Route("/", name="control_content_index")
    * @Method("GET")
    * @ProtoCmsAdminDash("Manage Content", active=true, role="ROLE_CMS_ACCESS", routeName="control_content_index", icon="fa fa-file-text-o", dontLink=true, menuPosition=10)
    */
    public function contentIndexAction(){ return false; }

    /**
     * Lists all Page entities.
     *
     * @Route("/page", name="control_page_index")
     * @Method("GET")
     * @ProtoCmsAdminDash("Manage Pages", active=true, routeName="control_page_index", icon="glyphicon glyphicon-list-alt", role="ROLE_PAGE_EDITOR", menuPosition=10, parentRouteName="control_content_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $pages = $em->getRepository('PrototypePageBundle:Page')->findBy(array('deleted' => false));

        return $this->render('PrototypePageBundle:page:index.html.twig', array(
            'pages' => $pages
        ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/page/new", name="control_page_new")
     * @Method({"GET", "POST"})
     * @ProtoCmsAdminDash("New Page", active=false, routeName="control_page_new", icon="glyphicon-plus", parentRouteName="control_page_index", role="ROLE_PAGE_EDITOR")
     */
    public function newAction(Request $request)
    {
        //reset locale in case user has 2 browser windows open
        $request->getSession()->set('_locale', 'en');
        $request->setLocale('en');

        // fetch all available components
        $em = $this->getDoctrine()->getManager();
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
        $cmsHtmlArray = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted' => 0));

        $page = new Page();
        $form = $this->createForm(PageType, $page, array(
            'cmsComponentArray' =>$cmsComponentArray,
            'cmsHtmlArray' => $cmsHtmlArray
            )
        );

        $form->handleRequest($request);
        $urlerror = false;

        $availableComponentSpaces['components'] = array();
        $availableComponentSpaces['htmlblocks'] = array();

        if ($form->isSubmitted()){

            $file = $em->getRepository('PrototypePageBundle:Templates')->find($form->get('template')->getData())->getTemplateFile();
            $availableComponentSpaces = $this->domCheckerFindAvailable($file);

            ////////////////////////
            // handle Slug - also check for unique slug (not via title)
            $newslug = $this->generateSlug($page);
            $appendedslug = $newslug.$this->appendComponentSlug($request->request->get('page'), $cmsComponentArray);
            //allow blank url keyword
            $appendedslug = str_replace('//', '/',$appendedslug);
            $uniqueSlugCheck = $em->getRepository('PrototypePageBundle:Page')->findOneBySlug(rtrim($appendedslug,"/"));
            if($uniqueSlugCheck && $uniqueSlugCheck != $page){
                $uniqueError = new FormError("Page must have an unique url ( URL Preview matches PageID# ".$uniqueSlugCheck->getId()." )");
                $form->get('url')->addError($uniqueError);
                $urlerror = true;
            }
            $page->setSlug($appendedslug);
            ////////////////////////

            if ($form->isValid()) {
                $em->persist($page);
                $em->flush();

                $this->addFlash('success','Page created');
                return $this->redirectToRoute('control_page_index');
            }else{
                $this->addFlash('error','Page not saved - check for errors');
            }


        }

        if($page->getParent() != null){
            $parentTreeHTML = $this->buildParentPageSelector($page->getParent()->getId());
        }else{
            $parentTreeHTML = $this->buildParentPageSelector();
        }


        return $this->render('PrototypePageBundle:page:modifyPage.html.twig', array(
            'page' => $page,
            'form' => $form->createView(),
            'availableComponentSpaces'  => $availableComponentSpaces['components'],
            'availableHtmlSpaces'       => $availableComponentSpaces['htmlblocks'],
            'cmsComponentArray' => $cmsComponentArray,
            'urlerror' => $urlerror,
            'parentTreeHTML' => $parentTreeHTML
        ));
    }

    /**
    * Finds and displays a Page entity.
    *
    * @Route("page/{id}", name="control_page_show")
    * @Method("GET")
    */
     public function showAction(Page $page)
    {
        $deleteForm = $this->createDeleteForm($page);

        return $this->render('PrototypePageBundle:page:show.html.twig', array(
            'page' => $page,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * AJAX function - update dom after user switches the template to show available spaces
    * @Route("/page/ajax/fetch-template", name="control_page_ajax_fetch_template")
     */
    public function fetchTemplateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $templateID = $request->request->get('template');

        if($templateID == null){ return new Response(json_encode(array('status'=>'error', 'message'=>'You must select a template.'))); }
        $template = $em->getRepository('PrototypePageBundle:Templates')->find($templateID);

        $availableComponentSpaces = $this->domCheckerFindAvailable($template->getTemplateFile());
        if($availableComponentSpaces == 'file not found'){ return new Response(json_encode(array('status'=>'error', 'message'=>'Template not found'))); }
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();

        $em = $this->getDoctrine()->getManager();
        $cmsHtmlArray = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted' => 0));

        $pageID = $request->request->get('page');
        if($pageID == null){
            $page = new Page();
        }else{
            $page = $em->getRepository('PrototypePageBundle:Page')->find($pageID);
        }

        $editForm = $this->createForm(PageType, $page, array(
            'cmsComponentArray' =>$cmsComponentArray,
            'cmsHtmlArray' => $cmsHtmlArray
            )
        );

        $com_general = array();
        $com_urlbased = array();
        $componentselect = array();
        foreach($cmsComponentArray as $com){
            if($com['componentType'] == 'standard'){
                if($com['slug'] !=""){
                    $componentselect['urlbased'][] = $com;
                }else{
                    $componentselect['general'][] = $com;
                }
            }
        }



        $componentsHtml =  $this->renderView('PrototypePageBundle:page:templateComponents.html.twig', array(
            'page' => $page,
            'edit_form' => $editForm->createView(),
            'componentselect' => $componentselect,
            'availableComponentSpaces'  => $availableComponentSpaces['components'],
            'availableHtmlSpaces'       => $availableComponentSpaces['htmlblocks'],
            'cmsComponentArray'         => $cmsComponentArray,
            'cmsHtmlArray'              => $cmsHtmlArray,
        ));

        $htmlBlocksHtml =  $this->renderView('PrototypePageBundle:page:templateHtmlblocks.html.twig', array(
            'page' => $page,
            'edit_form' => $editForm->createView(),
            'componentselect' => $componentselect,
            'availableComponentSpaces'  => $availableComponentSpaces['components'],
            'availableHtmlSpaces'       => $availableComponentSpaces['htmlblocks'],
            'cmsComponentArray'         => $cmsComponentArray,
            'cmsHtmlArray'              => $cmsHtmlArray,
        ));


        return new Response(json_encode(array('status'=>'success', 'comTemplate'=>$componentsHtml, 'htmlTemplate'=>$htmlBlocksHtml)));
    }


    /**
     * Displays a form to edit an existing Page entity.
     *
     * @Route("/page/{id}/edit", name="control_page_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Page $page)
    {
        //reset locale in case user has 2 browser windows open
        $request->getSession()->set('_locale', 'en');
        $request->setLocale('en');
        // check template for available component spaces
        $file = $page->getTemplate()->getTemplateFile();
        $availableComponentSpaces = $this->domCheckerFindAvailable($file);

        // fetch all available components
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
        $em = $this->getDoctrine()->getManager();
        $cmsHtmlArray = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted' => 0));

        //echo "<pre>".print_r($cmsComponentArray, true)."</pre>";
        $editForm = $this->createForm(PageType, $page, array(
            'cmsComponentArray' =>$cmsComponentArray,
            'cmsHtmlArray' => $cmsHtmlArray
            )
        );

        $editForm->handleRequest($request);

        $urlerror = false;

        if ($editForm->isSubmitted()){

            if(($page->getParent() != null ) && ($page->getId() == $page->getParent()->getId() ) ){
                $parentError = new FormError("Page can't be a parent of itself");
                $editForm->get('parent')->addError($parentError);
            }else{
                ////////////////////////
                // handle Slug - also check for unique slug (not via title)
                $newslug = $this->generateSlug($page);
                $appendedslug = $newslug.$this->appendComponentSlug($request->request->get('page'), $cmsComponentArray);
                //allow blank url keyword
                $appendedslug = str_replace('//', '/',$appendedslug);
                $uniqueSlugCheck = $em->getRepository('PrototypePageBundle:Page')->findOneBySlug(rtrim($appendedslug,"/"));
                if($uniqueSlugCheck && $uniqueSlugCheck != $page){
                    $uniqueError = new FormError("Page must have an unique url ( URL Preview matches PageID# ".$uniqueSlugCheck->getId()." )");
                    $editForm->get('url')->addError($uniqueError);
                    $urlerror = true;
                }
                $page->setSlug($appendedslug);
                ////////////////////////
            }

            if ($editForm->isValid()) {
                $em->persist($page);
                $em->flush();
                $parentCheck = $em->getRepository('PrototypePageBundle:Page')->findOneByParent($page->getId());
                if($parentCheck){
                    $this->updateParentTreeSlugs();
                }

                ////////////////////////
                //update all translation - as component or parent may of changed
                $activeTranslations = $this->container->getParameter('multilingual');
                if($activeTranslations){
                    $response = $this->forward('PrototypePageBundle:PageTranslation:allTranslatableSlugHandler', array('page'=>$page));
                    $this->addFlash('success','Success - page updated '.$response->getContent());
                }else{
                    $this->addFlash('success','Page Updated');
                }
                ////////////////////////
                return $this->redirectToRoute('control_page_index');
            }else{
                $this->addFlash('error','Page not saved - check for errors');
            }
        }

        if($page->getParent() != null){
            $parentTreeHTML = $this->buildParentPageSelector($page->getParent()->getId());
        }else{
            $parentTreeHTML = $this->buildParentPageSelector();
        }

        return $this->render('PrototypePageBundle:page:modifyPage.html.twig', array(
            'page' => $page,
            'form' => $editForm->createView(),
            'availableComponentSpaces'  => $availableComponentSpaces['components'],
            'availableHtmlSpaces'       => $availableComponentSpaces['htmlblocks'],
            'cmsComponentArray'         => $cmsComponentArray,
            'cmsHtmlArray'              => $cmsHtmlArray,
            'urlerror'                  => $urlerror,
            'parentTreeHTML'            => $parentTreeHTML
        ));
    }

    //appends slug if any URL based component component is used
    public function appendComponentSlug($formData, $cmsComponentArray){

        //echo "<pre>".print_r($formData, true)."</pre>";
        //echo "<pre>".print_r($cmsComponentArray, true)."</pre>";

        if(array_key_exists('components', $formData)){
            foreach($formData['components'] as $formComponent){
                if($formComponent['route'] !=""){
                    foreach($cmsComponentArray as $cmsComponent){
                        if($cmsComponent['route'] == $formComponent['route']){
                            if($cmsComponent['slug'] !=""){
                                return "/".$cmsComponent['slug'];
                            }
                        }
                    }
                }
            }
        }

    }

    // checks template for available component spaces
    // (basically opens the template and inserts dummy data which is then read by the domcrawler)
    public function domCheckerFindAvailable($file)
    {
        $available = array();
        //get default entity
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('PrototypePageBundle:Page')->find(1);
        //create array
        $componentArray = array();
        $componentArray[] = array('position' => 'domcheck' );
        $htmlblocksArray = array();
        $htmlblocksArray[] = array('position' => 'domcheck' );

        if ( !$this->get('templating')->exists('@theme/templates/'.$file) ) {
            return 'file not found';
        }
        //generate html for the domcrawler
        $html =  $this->renderView('@theme/templates/'.$file, array(
            'page' => $page,
            'pageComponents' => $componentArray,
            'pageHtmlBlocks' => $htmlblocksArray
        ));

        $crawler = new Crawler($html);
        $components = $crawler->filter('div[data-pcgc="domcheck"]')->each(function ($node, $i) {
            return $node->text();
        });
        $htmlblocks = $crawler->filter('div[data-pcgc="domcheckhtml"]')->each(function ($node, $i) {
            return $node->text();
        });

        return array('components'=>$components, 'htmlblocks'=>$htmlblocks);
        //return $available;
    }


    /**
    * Deletes a Page entity.
    * @Route("/page/delete/{id}", name="control_page_delete")
    */
    public function deleteAction(Request $request, Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $page->setDeleted(true);
        $this->addFlash('success','Success - page deleted');
        $em->flush();
        return $this->redirectToRoute('control_page_index');
    }

    /**
    * AJAX function which shows a preview of the slug
    * @Route("/page/ajax-slug-preview")
    */
    function ajaxGenerateSlugPreviewAction(Request $request){
        $pagetitle = $request->get('pagetitle');
        $parentId = $request->get('parentId');
        $extraArray = $request->get('extraArray');


        $em = $this->getDoctrine()->getManager();
        $parentEntity = $em->getRepository('PrototypePageBundle:Page')->find($parentId);

        $sluggerFunction = $this->container->get('pcgc_sluggify');

        if($parentEntity != null){
            //get page parents and generate slug from the title
            $newslug = $this->checkForParent($parentEntity,$sluggerFunction->makeSlugs($pagetitle));
        }else{
            $newslug = $sluggerFunction->makeSlugs($pagetitle );
        }

        if(count($extraArray) >0){
            foreach($extraArray as $extra){
                if($extra !=""){ $newslug .= "/".$extra;}
            }
        }

        return new Response($newslug);
    }


    function generateSlug($page){
        $parentEntity = $page->getParent();
        $sluggerFunction = $this->container->get('pcgc_sluggify');

        if($parentEntity != null){
            //get page parents and generate slug from the title
            $newslug = $this->checkForParent(
            $parentEntity,
            $sluggerFunction->makeSlugs( $page->getUrl() ));
        }else{
            $newslug = $sluggerFunction->makeSlugs( $page->getUrl() );
        }

        $extras = $page->getExtraUrlsegments();
        if(count($extras)>0){
            foreach($extras as $extra){
                if($extra['urlsegment'] !=""){ $newslug .= "/".$extra['urlsegment']; }
            }

        }

        return str_replace('//', '/',$newslug);
    }

    // This rebuilds all page slugs - if a parent changes then update its children
    function updateParentTreeSlugs(){
        $em = $this->getDoctrine()->getManager();
        $pagesSlugsToUpdate = $em->getRepository('PrototypePageBundle:Page')->findAll();
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();

        foreach($pagesSlugsToUpdate as $page){
            $parentEntity = $page->getParent();
            if($parentEntity != null){
                //get page parents and generate slug from the title
                // $newslug = $this->checkForParent(
                // $parentEntity,
                // $this->container->get('pcgc_sluggify')->makeSlugs( $page->getUrl() ));

                $newslug = $this->generateSlug($page);
                $data['components'] = $page->getComponents();
                $appendedslug = $newslug.$this->appendComponentSlug($data, $cmsComponentArray);

                $page->setSlug(str_replace('//', '/',$appendedslug));
                $em->persist($page);
            }
        }
        $em->flush();
    }


    function checkForParent($parent, $slug){

        $sluggerFunction = $this->container->get('pcgc_sluggify');
        $slug = $sluggerFunction->makeSlugs($parent->getUrl() )."/".$slug;

        if($parent->getParent() === null){
            return $slug;
        }else{
            return $this->checkForParent($parent->getParent(), $slug);
        }
    }

    /**
     * @Route("/page/debug/fetch-components", name="control_page_components")
     */
    public function fetchComponentsAction()
    {
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
        print_r($cmsComponentArray);
        exit;
    }



    /**
     * @Route("/page/ajax-inline-editor-update", name="control_page_ajax_inline_editor_update")
     */
    public function ajaxInlineEditorUpdate(Request $request){

        $namespace   = $request->request->get('entitynamespace');
        $field       = $request->request->get('field');
        $locale      = $request->request->get('locale');
        $id          = $request->request->get('id');
        $data        = $request->request->get('data');
        $currentUrl = $request->request->get('currentUrl');

        $em = $this->getDoctrine()->getManager();
        $entityToUpdate = $em->getRepository($namespace)->find($id);
        if(!$entityToUpdate){throw $this->createNotFoundException('PCGC - '.$namespace.' ID#'.$id.' Not Found'); }
        $setterMethod = "set".ucwords($field);

        //slug change check
        $preSlug ="";
        if(method_exists($entityToUpdate,'getSlug')){ $preSlug = $entityToUpdate->getSlug(); }

        if(method_exists($entityToUpdate,$setterMethod)){
            $entityToUpdate->$setterMethod($data);
            if($locale !='en'){
                $entityToUpdate->setTranslatableLocale($locale);
            }
            $em->persist($entityToUpdate);
            $em->flush();
            $json = json_encode(array('status' => 'success', 'message'=>'this is a message', 'returnedContent' => $data));

            //slug change check
            $postSlug ="";
            if(method_exists($entityToUpdate,'getSlug')){ $postSlug = $entityToUpdate->getSlug(); }

            if($preSlug != $postSlug){
                $existingRouteSegments = explode('/', $currentUrl);
                array_pop($existingRouteSegments);
                $oldpath = $existingRouteSegments;
                $newPath = $existingRouteSegments;
                $oldpath[] = $preSlug;
                $newPath[] = $postSlug;
                $oldAddress = implode('/',$oldpath);
                $newAddress = implode('/',$newPath);
                $html  = "<p>Changing the current field has altered the current page URL<br/>Refreshing the current page may result in a 404 or a 'Not Found' message<br/>- dont worry, this is normal!</p>";
                $html .= "<p>Old/Current Address:<br/><strong>".$oldAddress."</strong></p>";
                $html .= "<p>New Address:<br/><a href='".$newAddress."' >".$newAddress."</a></p>";
                $html .= "<p>Its recommended that you navigate to the new address</p>";
                $html .= "<a href='".$newAddress."' class='btn btn-success'><span class='glyphicon glyphicon-ok'></span> Goto New URL</a>";
                $html .= "<a id='redactor-modal-close2' class='btn btn-primary pull-right'><span class='glyphicon glyphicon-remove'></span> Stay Here</a>";

                $json = json_encode(array('status' => 'notice', 'title'=>'Notice: You have Changed the Page URL', 'message'=>$html));
            }
        }else{
            throw $this->createNotFoundException('PCGC - setterMethod '.$setterMethod.' on '.$namespace.'  Not Found');
        }


        return new Response($json);
    }


    //This function builds the parent page selector
    public function buildParentPageSelector($currentSelected =null)
    {

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('PrototypePageBundle:Page')->findBy(array('deleted' => 0));
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
        $content = "<ul>";
        $content .= "<li id='null'>Root";
        $content .= "<ul>";
        $content .= $this->makeMenu($items, 0, 0, count($pages),$currentSelected);
        $content .= "</ul>";
        $content .= "</li>";
        $content .= "</ul>";
        return $content;
    }


    function makeMenu($menuData, $no = 0, $currentcount, $length, $currentSelected) {

        $child = $this->hasMenuChildren($menuData, $no);
        if (empty($child)){	return "";}
        if($currentcount > 0 ){ $content = "<ul>\n"; }else{	$content = ""; }
        $currentcount++;
        foreach ( $child as $item ) {
            //if($item['menu_parent_id'] == $currentSelected){ $active ="class='jstree-open' ";}else{$active =""; }
            $content .= "<li id='". $item['menu_item_id']."' >";
            // $content .= "<a href='/".$item['slug']."' >".$item['navtitle']."</a>";
            //echo "<br/>".$item['menu_item_id']. " - ".$currentSelected." - ".$item['navtitle'];
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
