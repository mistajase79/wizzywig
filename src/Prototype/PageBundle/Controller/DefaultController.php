<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;
use Prototype\PageBundle\ProtoCmsComponentConverter;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DefaultController extends Controller
{


    /**
     * @Route("/members/dash", name="members_dash")
     */
    public function membersDashAction()
    {
        return $this->render('@theme/members/members-dash.html.twig');
    }

    /**
     * @Route("/special-user/dash", name="special_user_dash")
     */
    public function specialUserDashAction()
    {
        return $this->render('@theme/security/special-dash.html.twig');
    }


    public function generateAndModifyMetaDataTags($page, $componentEnity){
        //print_r(get_class_methods($componentEnity));
        $possibleMetaTitles = array('getHeadline', 'getHeading', 'getTitle', 'getName', 'getMetaTitle');
        $possibleMetaDescription = array('getContent', 'getDescription', 'getExert', 'getMetaDescription');

        // fallbacks
        if($page->getMetatitle() == ""){ $page->setMetatitle($page->getTitle()); }
        if($page->getMetadescription() == ""){ $page->setMetadescription($page->getContent()); }

        $metaDataArray = array();

        foreach($possibleMetaTitles as $metaTitle){
            if(method_exists($componentEnity,$metaTitle)){
                $metaDataArray['title'] = call_user_func(array($componentEnity, $metaTitle));
            }
        }
        foreach($possibleMetaDescription as $metaDescription){
            if(method_exists($componentEnity,$metaDescription)){
                $data = call_user_func(array($componentEnity, $metaDescription));
                if($data != ""){ $metaDataArray['description'] = $data; }
            }
        }

        if(array_key_exists('title', $metaDataArray)){
            $page->setMetatitle($metaDataArray['title']." | ".$page->getMetatitle());
        }

        if(array_key_exists('description', $metaDataArray)){
            $page->setMetadescription(strip_tags($metaDataArray['description']));
        }

        if($page->getParent() != null ){ $extra = $page->getParent()->getTitle()." | "; }else{ $extra =""; }
        $page->setMetatitle($page->getMetatitle()." | ".$extra.$this->container->getParameter('sitename'));

        return $page;
    }

    function routeToControllerName($routename) {
        $routes = $this->get('router')->getRouteCollection();
        return $routes->get($routename)->getDefaults();
    }

    function findPageSlugParameters($slug){
        $matches = array();
        $regex = "/{([a-zA-Z0-9_]*)}/";
        preg_match_all($regex, $slug, $matches);
        return $matches[1];
    }

    function pageChecks($page){

        if($page->getActive() == 0 ){ return false; }
        if(new \DateTime() < $page->getViewableFrom() ){ return false; }

        return true;
    }


    /**
    * @Route("/switch-locale/{_locale}", name="switch_locale")
    */
    public function switchLocale(Request $request, $_locale){
        return $this->redirect('/');
        //return $this->redirect($request->headers->get('referer'));
    }


    /**
    * // @Route("/{_locale}", name="home", defaults={"slug" = "home"}))
     * // @Route("/{_locale}/{slug}", name="change_locale", requirements={"slug"=".+"})
     */
    // public function changeLocale(Request $request, $_locale, $slug){
    // 	$pageData = $this->routeMatcher($request, $slug);
    // 	return $pageData;
    // }

    /**
    * @Route("/page.php", name="page_php_home")
    */
    public function getMethodRouterAction(Request $request){

        $params = $request->query->all();
        if($request->query->has('_debug')){$debug=true;}else{$debug=false;}
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();

        // Using doctrine cache to store dynamic page slugs
        // Used in twig function generatePath (converts id to slug) and assists links with translations
        $slugCache= $em->getRepository('PrototypePageBundle:Page')->findAllByLocale($request->getLocale());
        $cacheDriver = $this->container->get('doctrine_cache.providers.arraycache');
        $cacheDriver->save('slugCache', $slugCache);

        //	if($debug){echo "<pre>".print_r($cmsComponentArray,true)."</pre>";}

        if($request->query->has('_locale')){
            if($debug){echo "<p>CHANGING LOCALE</p>";}
            if($request->getLocale() != $request->query->get('_locale')){
                $session->set('_locale', $request->query->get('_locale'));
                $request->setLocale($session->get('_locale', $request->query->get('_locale')));
                return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));
            }
        }

        $structureEntities = array();
        $pageID = $request->query->get('page');
        if(!is_numeric($pageID)){ throw $this->createNotFoundException('PCGC - No PageID passed'); }
        $pageEntity = $em->getRepository('PrototypePageBundle:Page')->find($pageID);
        if(!$pageEntity){ throw $this->createNotFoundException('PCGC - No Page found for id#'.$pageID); }
        $pageSlugArray = explode('/',$pageEntity->getSlug());
        $fullSegmentOrder = array();

        //print_r($pageSlugArray);

        if(count($pageSlugArray)>1){

            for($i=0; $i<(count($pageSlugArray)+1); $i++ ){
                if(array_key_exists($i, $pageSlugArray)){
                    if(substr_count($pageSlugArray[$i], '{') >0){
                        $fullSegmentOrder[] =$pageSlugArray[$i];
                        unset($pageSlugArray[$i]);
                    }
                }
            }
        }

        if($debug){ echo "<p>". $pageEntity->getSlug()."</p>"; }
        if(substr_count($pageEntity->getSlug(), '{') >0){
            foreach($params as $key=>$id){
                $key = strtolower($key);
                if(($key !='_locale')||($key !='page')||($key !='_debug')){
                    $comkey = $this->searchArrayKeyVal('slugEntity', $key, $cmsComponentArray);
                    if($debug){ "<p>Searching for '".$key."' against slugEntity in cmsComponentArray</p>";}
                    if(is_numeric($comkey)){
                        $order = array_search($cmsComponentArray[$comkey]['slug'], $fullSegmentOrder);
                        if($debug){echo "<p>".$order." - ".$cmsComponentArray[$comkey]['slugEntity']."</p>";}
                        $bundle = str_replace('\\', '',$cmsComponentArray[$comkey]['bundle']).":".$cmsComponentArray[$comkey]['slugEntity'];
                        $structureEntities[$order] = $em->getRepository($bundle)->find($id);

                    }
                }
            }
        }

        //print_r($structureEntities);
        $slugfixer = implode('/',$pageSlugArray);
        $slug=$slugfixer;
        ksort($structureEntities);
        foreach($structureEntities as $urlelement){
            $slug .="/".$urlelement->getSlug();
        }

        if($debug){

            echo "<p>Path re-built to <strong>".$slug."</strong></p>";

            $pageData = $this->routeMatcherV2($request, ltrim($slug, '/'));
            //print_r($pageData['pageComponents']);

            if(is_array($pageData)){

                $pageAllowed = $this->pageChecks($pageData['page']);
                if($pageAllowed == false){ throw $this->createNotFoundException('PCGC: Page view checks for PageID# "'.$pageData['page']->getId().'" has failed (disabled, before viewdate ect...) - so showing 404'); }

                // HTML Blocks
                $htmlblocks = array();
                $assignedHtmlblocks = $pageData['page']->getHtmlblocks();
                if(count($assignedHtmlblocks)>0){
                    $allHtmlBlocks = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted' => false));
                    foreach( $assignedHtmlblocks as $assignedblock){
                        foreach( $allHtmlBlocks as $allHtmlBlock){
                            if($assignedblock['blockId'] == $allHtmlBlock->getId()){
                                $htmlblocks[] = array(
                                    'blockId' => $allHtmlBlock->getId(),
                                    'position' => $assignedblock['position'],
                                    'data' => $allHtmlBlock->getHtml(),
                                );
                            }
                        }
                    }
                }

                $pageMeta = $pageData['page'];
                foreach($pageData['pageComponents'] as $pageComp){
                    $pageMeta = $this->generateAndModifyMetaDataTags($pageData['page'], $pageComp['entity']);
                }

                return $this->render('@theme/templates/'.$pageData['page']->getTemplate()->getTemplateFile(), array(
                    'page' => $pageMeta,
                    'slug' => $pageData['slug'],
                    'pageComponents' => $pageData['pageComponents'],
                    'pageHtmlBlocks' => $htmlblocks,
                    'longUrl' => strtolower(implode('/',$params))
                ));

            }else{ // this will be a redirect
                return $pageData;
            }

        }

        if(!$debug){ //refresh page with new URL - to prevent google seeing duplicate content
            return $this->redirect(str_replace('//', '/',$slug));
        }

        return new Response('EOF');
    }

    /**
    * @Route("/", name="home", defaults={"slug" = "home"}))
    * @Route("/{slug}", name="router", requirements={"slug"=".+"})
    */
    public function routerAction(Request $request, $slug){

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        // Using doctrine cache to store dynamic page slugs
        // Used in twig function generatePath (converts id to slug) and assists links with translations
        $slugCache= $em->getRepository('PrototypePageBundle:Page')->findAllByLocale($request->getLocale());
        $cacheDriver = $this->container->get('doctrine_cache.providers.arraycache');
        $cacheDriver->save('slugCache', $slugCache);

        if($request->query->has('_debug')){$debug=true;}else{$debug=false;}

        if($debug){ echo "Current Locale -".$request->getLocale() ." | request Locale -". $request->query->get('_locale'); }

        if($request->query->has('_locale')){
            if($debug){echo "<p>CHANGING LOCALE</p>";}
            if($request->getLocale() != $request->query->get('_locale')){
                $session->set('_locale', $request->query->get('_locale'));
                $request->setLocale($session->get('_locale', $request->query->get('_locale')));
                return $this->redirect("/".$slug);
            }
        }


        $pageData = $this->routeMatcherV2($request, $slug);


        if(is_array($pageData)){

            // echo "<pre>";
            // \Doctrine\Common\Util\Debug::dump($pageData);
            // echo "</pre>";
            $longUrl = 'page.php?Page='.$pageData['page']->getId();
            if(array_key_exists('pageComponents', $pageData) && $pageData['pageComponents'] != null){
                foreach($pageData['pageComponents'] as $component){
                    if(array_key_exists('urlKey',$component ) && $component['urlKey'] != null){
                        $longUrl .="&".$component['urlKey']."=".$component['urlValue'];
                    }
                }
            }

            // echo "<br/>".$slug;
            // echo "<br/>".strtolower($longUrl);
            // print_r($pageData['pageComponents']);

            $pageAllowed = $this->pageChecks($pageData['page']);
            if($pageAllowed == false){ throw $this->createNotFoundException('PCGC: Page Checks for pageId "'.$pageData['page']->getId().'" has failed (disabled, before viewdate ect...) - showing 404'); }

            // HTML Blocks
            $htmlblocks = array();
            $assignedHtmlblocks = $pageData['page']->getHtmlblocks();
            if(count($assignedHtmlblocks)>0){
                $allHtmlBlocks = $em->getRepository('PrototypePageBundle:HtmlBlocks')->findBy(array('deleted' => false));
                foreach( $assignedHtmlblocks as $assignedblock){
                    foreach( $allHtmlBlocks as $allHtmlBlock){
                        if($assignedblock['blockId'] == $allHtmlBlock->getId()){
                            $htmlblocks[] = array(
                                'blockId' => $allHtmlBlock->getId(),
                                'position' => $assignedblock['position'],
                                'data' => $allHtmlBlock->getHtml(),
                            );
                        }
                    }
                }
            }


            $pageMeta = $pageData['page'];
            foreach($pageData['pageComponents'] as $pageComp){
                $pageMeta = $this->generateAndModifyMetaDataTags($pageData['page'], $pageComp['entity']);
            }


            return $this->render('@theme/templates/'.$pageData['page']->getTemplate()->getTemplateFile(), array(
                'page' => $pageMeta,
                'slug' => $pageData['slug'],
                'pageComponents' => $pageData['pageComponents'],
                'pageHtmlBlocks' => $htmlblocks,
                'longUrl' => strtolower($longUrl)
            ));

        }else{ // this will be a redirect
            return $pageData;
        }
        //return $pageData;
    }




        function routeMatcherV2($request, $slug)
        {

            $multilingual = $this->container->getParameter('multilingual');

            $locale = $request->getLocale();
            $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
            $pcgcComponents = array();
            $em = $this->getDoctrine()->getManager();

            if($request->query->has('_debug')){$debug=true;}else{$debug=false;}

            if($debug){
                echo "<p>ROUTER DEBUG<br/>This will show you feedback during the route match process</p>";
                echo "<br/>Current Locale [".$request->getLocale()."]";
                echo "<br/>Looking for: <strong>".$slug."</strong>";
            }

            //////////////////////////
            // SIMPLE MATCHES
            //////////////////////////

            // simple direct match
            //this will match the home page in any locale and any default 'en' page
            $page = $em->getRepository('PrototypePageBundle:Page')->findOneBy(array('slug'=> $slug, 'deleted' => false, 'active' => true));
            if($page){
                $pageComponents = $this->getComponentData($debug, $cmsComponentArray, $page->getComponents(), $request, $page->getId());
                if($debug){
                    echo "<br/><span style='color:green' >(STEP1) Found - ".$page->getTitle()." (ID#".$page->getId().")<br/>Will render page out of debug mode</span>";
                }
                return array(
                    'page' => $page,
                    'slug' => $slug,
                    'pageComponents' => $pageComponents
                );
            }

            // simple direct match for SELECTED TRANSLATION
            //this route will match translations - locales matched after query
            $pageAll = $em->getRepository('PrototypePageBundle:Page')->findAll();
            foreach($pageAll as $page){
                if($page->getSlug() == $slug){
                    if($page->getDeleted() || !$page->getActive() ){
                        throw $this->createNotFoundException('PCGC: Page Route for "'.$slug.'" has been deactived or deleted');
                    }
                    $pageComponents = $this->getComponentData($debug, $cmsComponentArray, $page->getComponents(), $request, $page->getId());
                    if($debug){
                        echo "<br/><span style='color:green' >(STEP2- transaltion) Found - ".$page->getTitle()." (ID#".$page->getId().")<br/>Will render page out of debug mode</span>";
                    }

                    return array(
                        'page' => $page,
                        'slug' => $slug,
                        'pageComponents' => $pageComponents
                    );
                }
            }

            if($debug){
                echo "<p>No direct matches found - looking for complex matches  (has a url component was used?)<br/>Checking All Pages:</p>";
            }

            // if no direct matches found  ( usually means a url component was used )
            //////////////////////////
            // COMPLEX MATCHES
            //////////////////////////

            $urlSegments = explode('/', $slug);

            /////////////////////////////////////////
            // this is for debug reasons only ( logic repeated after this loop )
            if($debug){
                echo "<pre>";
                echo "<p><strong>COMPLEX ROUTE MATCHING</strong></p>";
                foreach($pageAll as $page){

                    $pageSlugParametersArray = $this->findPageSlugParameters($page->getSlug());
                    $possiblePageSegments =  explode('/', $page->getSlug());
                    $slugMatches = array_intersect($urlSegments, $possiblePageSegments);

                    $count =0;

                    echo "Looking for: <strong>".$slug."</strong><br/>";
                    echo "Looking at : <strong>".$page->getSlug()."</strong> - ID#".$page->getId()."<br/>";
                    if(count($possiblePageSegments) == count($urlSegments) ){ echo "<span style='color:green; font-weight:bold' >Passed phase 1</span>"; $count++; }
                    echo " - ".count($possiblePageSegments)."/".count($urlSegments)." URL Segment Count";

                    if(count($pageSlugParametersArray) > 0){
                        $count++;
                        echo "<br/><span style='color:green; font-weight:bold' >Passed phase 2</span> - ".count($pageSlugParametersArray)." URL Parameter Components";
                    }else{
                        echo "<br/>No URL components on this page";
                    }
                    if(count($slugMatches) > 0){
                        $count++;
                        echo "<br/><span style='color:green; font-weight:bold' >Passed phase 3</span> - ".count($slugMatches)." URL slug matches";
                    }else{
                        echo "<br/>No URL slug matches on this page";
                    }

                    if(count($pageSlugParametersArray) + count($slugMatches) == count($urlSegments) ){
                        $count++;
                        echo "<br/><span style='color:green; font-weight:bold' >Passed phase 4</span> - slugParameters + slugMatches = urlSegments";
                    }else{
                        echo "<br/>slugParameters + slugMatches dont add up to ".count($urlSegments);
                    }

                    if($count == 4){
                        echo "<br/><span style='color:green; font-weight:bold' >SUCCESS!! - full match</span>";
                        $confirmedDebugPage = $page;
                        echo "<br/><span style='color:green' >(STEP3- complex) Found - ".$confirmedDebugPage->getTitle()." (ID#".$confirmedDebugPage->getId().")<br/>Will render page out of debug mode</span>";
                    }else{
                        echo "<br/>Not this page";
                    }
                    echo "<p>-----------------------------------</p>";

                }// end of pages loop

                if(!$confirmedDebugPage){
                    echo "<br/>Doh! - Route Not Matched ";
                }

                echo "</pre>";

            }//end of debug
            ///////////////////////////////////////


            foreach($pageAll as $page){
                $pageSlugParametersArray = $this->findPageSlugParameters($page->getSlug());
                $possiblePageSegments =  explode('/', $page->getSlug());
                $slugMatches = array_intersect($urlSegments, $possiblePageSegments);
                $count =0;

                if(count($possiblePageSegments) == count($urlSegments) ){ $count++;	}

                if(count($pageSlugParametersArray) > 0){ $count++;}

                if(count($slugMatches) > 0){ $count++;}

                if(count($pageSlugParametersArray) + count($slugMatches) == count($urlSegments) ){ $count++;}

                // Passed all 4 checks
                if($count == 4){ $confirmedPage = $page;}

            }// end of pages loop

            if(isset($confirmedPage)){
                if($debug){ echo "<p>Calling renderPageWithURLComponents</p>";}
                return $this->renderPageWithURLComponents($request, $confirmedPage, $slug, $cmsComponentArray);
            }

            if($debug){
                echo "<p><strong>STILL NO MATCH</strong> - START CHECKING TRANSLATIONS - with seperate indervidual url segments</p>";
            }
            //exit;


            ////////////////////////////
            // Lacale auto switcher
            ////////////////////////////
            // if page still not found check translations then change locale to match
            // Note: for the switcher to work you have to refresh the page
            if($multilingual){
                $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
                foreach($pageAll as $page ){
                    $translations = $repository->findTranslations($page);
                    foreach($translations as $locale => $fields ){
                        foreach($urlSegments as $segment){
                            $transSlug = explode('/', $fields['slug']);
                            foreach($transSlug as $transSlugSegment){

                                if($debug){echo "<br/>[".$locale."]".$segment. ":" .$transSlugSegment;}

                                if($segment == $transSlugSegment  ){
                                    if($debug){echo " <strong> - Match ***</strong>";}
                                    $diffrentLanguagesWithSameSlug[$locale] =0;
                                    $setLocale = $locale;
                                    //wasnt sure which was the correct method - keeps changing!
                                    $request->getSession()->set('_locale', $setLocale);
                                    $request->setLocale($setLocale);
                                    if($debug){
                                        echo "<p><strong>*** REFRESHING PAGE IN [".$setLocale."] - autoswitching ***</strong></p>";
                                    }else{
                                        if($request->query->has('_locale')){
                                            if($debug){
                                                echo "<br/>Already Redirected - preventing loop";
                                            }
                                        }else{
                                            return $this->redirect("/".$slug."?_locale=".$setLocale);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                //check if en from different locale
                foreach($pageAll as $page ){
                    $page->setTranslatableLocale('en');
                    $em->refresh($page);
                    foreach($urlSegments as $segment){
                        $locale = "en";
                        $transSlug = explode('/', $page->getSlug());
                        foreach($transSlug as $transSlugSegment){

                            if($debug){ echo "<br/>[".$locale."]".$segment. ":" .$transSlugSegment; }

                            if($segment == $transSlugSegment  ){
                                if($debug){ echo " <strong> - Match ***</strong>";}

                                $diffrentLanguagesWithSameSlug[$locale] = 0;
                                $setLocale = $locale;
                                //wasnt sure which was the correct method - keeps changing!
                                $request->getSession()->set('_locale', $setLocale);
                                $request->setLocale($setLocale);
                                if($debug){
                                    echo "<p><strong>*** REFRESHING PAGE IN [".$setLocale."] - autoswitching ***</strong></p>";
                                }else{
                                    if($request->query->has('_locale')){
                                        if($debug){	echo "<br/>Already Redirected - preventing loop";}
                                    }else{
                                        return $this->redirect("/".$slug."?_locale=".$setLocale);
                                    }
                                }
                            }
                        }
                    }
                }
            } // end if($multilingual)

            //return new Response('<p>End - showing 404 page</p>');
            throw $this->createNotFoundException('PCGC - Route not matched: showing 404 page');

        }


        function renderPageWithURLComponents($request, $confirmedPage, $slug, $cmsComponentArray){

            $em = $this->getDoctrine()->getManager();
            if($request->query->has('_debug')){$debug=true;}else{$debug=false;}

            if($debug){
                echo "<br/>looking for components:<br/>";
            }
            $pageSlugParametersArray = $this->findPageSlugParameters($confirmedPage->getSlug());

            if($debug){
                echo "Current URL components to match<br/>";
                print_r($pageSlugParametersArray);
                echo "<p>If more than one URL component then the last one will be checked by default (".count($pageSlugParametersArray)." Found)</p>";
            }

            //extra check
            $slugPieces = explode('/',$slug);
            $confirmedPagePieces =explode('/',$confirmedPage->getSlug());

            foreach($cmsComponentArray as $cmsComponent){
                if($cmsComponent['slug'] !=""){
                    $slugCheck = str_replace(" ", "", $cmsComponent['slug']);
                    if($debug){echo "<br/>Lookin at: ".$slugCheck;}

                    if($slugCheck == "{".end($pageSlugParametersArray)."}"){
                        if($debug){	echo " - Matched<br/>";	print_r($cmsComponent);	}

                        $slugKey = array_search("{".end($pageSlugParametersArray)."}", $confirmedPagePieces);
                        if(!is_numeric($slugKey)){
                            if($debug){	echo '<p>Oh No! -Key not found for "{'.end($pageSlugParametersArray).'}" in '.$confirmedPage->getSlug()."</p>";}
                        }else{
                            if($debug){	echo "<p>Using the slug '".$slugPieces[$slugKey]."' on ".stripslashes($cmsComponent['bundle']).":".$cmsComponent['slugEntity']." </p>";}
                        }

                        $component_find_slug = $slugPieces[$slugKey];
                        $pageComponents = $this->getComponentData($debug, $cmsComponentArray, $confirmedPage->getComponents(), $request, $confirmedPage->getId(), $component_find_slug, $slugPieces, $pageSlugParametersArray );

                        return array(
                            'page' => $confirmedPage,
                            'slug' => $slug,
                            'pageComponents' => $pageComponents
                        );
                    }
                }
            }
        }


    function getComponentData($debug, $cmsComponentArray, $pageComponents, $request, $confirmedPageId, $component_find_slug=null, $slugPieces=array(), $pageSlugParametersArray=array() ){

        if($debug){
            echo "<pre>";
            echo "<strong>AVAILABLE COMPONENTS:</strong><br/>";
           // \Doctrine\Common\Util\Debug::dump($cmsComponentArray);
            echo "<table cellpadding='10' width='100%'>";
            echo "<tr style='border-bottom:1px solid #666; font-size:12px'><th>Name</th><th>Slug</th><th>SlugEntity</th><th>Route</th><th>Type</th><th>Bundle</th><tr>";
            foreach($cmsComponentArray as $com){
                echo "<tr style='border-top:1px dashed #666; font-size:12px'>";
                echo "<td style='white-space:nowrap; padding-top:5px; padding-bottom:5px'>".$com['name']."</td>";
                echo "<td style='white-space:nowrap'>".$com['slug']."</td>";
                echo "<td style='white-space:nowrap'>".$com['slugEntity']."</td>";
                echo "<td style='white-space:nowrap'>".$com['route']."</td>";
                echo "<td style='white-space:nowrap'>".$com['componentType']."</td>";
                echo "<td style='white-space:nowrap'>".$com['bundle']."</td>";
                echo "<tr>";
            }

            echo "</table>";
            echo "</pre>";
            echo "<pre>";
            echo "<strong>ACTIVE PAGE COMPONENTS:</strong><br/>";
            //\Doctrine\Common\Util\Debug::dump($pageComponents);
            echo "<table cellpadding='10' width='100%'>";
            echo "<tr style='border-bottom:1px solid #666; font-size:12px'><th>Position</th><th>Route</th><tr>";
            foreach($pageComponents as $com){
                echo "<tr style='border-top:1px dashed #666;  font-size:12px'>";
                echo "<td style='white-space:nowrap; padding-top:5px; padding-bottom:5px'>".$com['position']."</td>";
                echo "<td style='white-space:nowrap'>".$com['route']."</td>";
            }

            echo "</table>";
            echo "</pre>";
         }

        $pageComponentsReturn = array();

        // Find out which URL segments are dynamic by removing
        // preceeding segments (parent and page)
        $totalSlug = count($slugPieces);
        $totalPara = count($pageSlugParametersArray);
        $diff = $totalSlug - $totalPara;
        for($i=0; $i<$diff; $i++){
            unset($slugPieces[$i]);
        }
        $slugPieces = array_values($slugPieces);


        if($debug){
            echo "<pre>";
            echo "<p><strong>COMPONENT LINKING</strong></p>";
        }
        // Workout extra segments - these will have no route
        // and componentType = 'segment'
        $extraUrlSegments = $pageSlugParametersArray;
        if(count($pageSlugParametersArray)>1){
            unset($extraUrlSegments[count($extraUrlSegments) - 1]);
            unset($slugPieces[count($slugPieces) - 1]);
            $extraUrlSegments = array_values($extraUrlSegments);
            $slugPieces = array_values($slugPieces);

            if($debug){
                echo "<p>SEGMENT ONLY (".count($extraUrlSegments)." found)</p>";
                // echo "<br/>The next 2 array keys and values should match up";
                // echo "<br/>The next 2 array keys and values should match up";
                // echo "<br/>".print_r($extraUrlSegments, true);
                // echo "<br/>".print_r($slugPieces, true);
            }

            foreach($extraUrlSegments as $index => $segment){
                $comkey = $this->searchArrayKeyVal('slug', "{".$segment."}", $cmsComponentArray);
                if(is_numeric($comkey)){
                    $entity = $this->getEntityData( array('component'=>$cmsComponentArray[$comkey], 'slug'=>$slugPieces[$index]), $request, $debug );
                    $entityId = $entity->getId();
                    if($entityId){
                        $pageComponentsReturn[] = array(
                            'position' => null,
                            'urlKey' => $cmsComponentArray[$comkey]['slugEntity'],
                            'urlValue' => $entityId,
                            'data' => null,
                            'entity' => $entity
                        );
                    }
                }else{
                    if($debug){	echo "<p>Dynamic Slug '".$slugPieces[$index]."' for URL Annotation '".$segment."' NOT FOUND - but can continue, you should check your routes/link</p>";}
                }
            }
        }

        // Check all page components
        $comCount = 0;
        foreach($pageComponents as $pageComponent){
            if($pageComponent['route'] != null){
                $comCount++;
                if($debug){	echo '<p>------------------------------</p>';}
                if($debug){	echo '<p>COMPONENT '.$comCount.'</p>';}
                // found pagecomponent
                $comkey = $this->searchArrayKeyVal('route', $pageComponent['route'], $cmsComponentArray);

                if(!is_numeric($comkey)){
                    if($debug){	echo '<p>The component <strong>'.$pageComponent['route'].'</strong> not found - has it been deleted?</p>'; }
                    $pageComponentsReturn[] = array(
                        'position' => $pageComponent['position'],
                        'urlKey' => null,
                        'urlValue' => null,
                        'data' => '',
                        'entity' => null
                    );

                }else{

                    if($debug){	echo '<p>Comkey (#'.$comkey.') found for = '.$pageComponent['route'];}

                    $action = $this->routeToControllerName($cmsComponentArray[$comkey]['route']);
                    if($debug){	echo '<br/>Controller = '.$cmsComponentArray[$comkey]['route'].'</p>';}

                    //Non-URL reliant (slugless) component
                    if($cmsComponentArray[$comkey]['slug'] == null){
                        if($debug){	echo '<p>SLUGLESS:<br/>RENDERING CONTROLLER into  = <strong>'.$action['_controller'].'</strong></p>';}
                        // fetch component data
                        $response = $this->forward($action['_controller'],  array('request' => $request, 'pageId'=>$confirmedPageId));
                        $pageComponentsReturn[] = array(
                            'position' => $pageComponent['position'],
                            'urlKey' => null,
                            'urlValue' => null,
                            'data' => $response->getContent(),
                            'entity' => null
                        );

                    }else{
                        // URL Component found
                        $removal = array("{", "}"); //used for str_replace
                        $controllerSlug = str_replace($removal, "", $cmsComponentArray[$comkey]['slug']);

                        //Get entityID for getMethodRouterAction - used to assist with locale/translation switching
                        $entity = $this->getEntityData( array('component'=>$cmsComponentArray[$comkey], 'slug'=>$component_find_slug), $request, $debug );
                        if(!$entity){  throw $this->createNotFoundException('PCGC: EntityID not found - has the slug (\''.$component_find_slug.'\') changed?'); }

                        $entityId = $entity->getId();
                        // fetch component data
                        if($debug){	echo '<p>SLUG REQUIRED:<br/>RENDERING CONTROLLER = <strong>'.$action['_controller'].'</strong></p>';}
                        $response = $this->forward($action['_controller'],  array('request' => $request,  'pageId'=>$confirmedPageId, $controllerSlug => $component_find_slug) );

                        $pageComponentsReturn[] = array(
                            'position' => $pageComponent['position'],
                            'urlKey' => $cmsComponentArray[$comkey]['slugEntity'],
                            'urlValue' => $entityId,
                            'data' => $response->getContent(),
                            'entity' => $entity
                        );
                    }


                } //end if(!is_numeric($comkey)
            }
        }

        if($debug){echo "</pre>";}
        return $pageComponentsReturn;
    }


    public function getEntityData($data, $request, $debug){
        $em = $this->getDoctrine()->getManager();

        $locale = $request->getLocale();

		$queryEntity = str_replace('\\', '',$data['component']['bundle'].":".$data['component']['slugEntity']);
		$queryEntity = str_replace('/', '',$queryEntity);
        if($debug){	echo '<p>ENTITY QUERY - '.$queryEntity;}
        if($locale !='en') {
            $entity = $em->getRepository($queryEntity)->findSlugWithLocale($locale, $data['slug']);
            if($debug){	echo '->findSlugWithLocale(<strong>'.$locale.'</strong>, <strong>'.$data["slug"].'</strong>)';}
        }else{
            $entity = $em->getRepository($queryEntity)->findOneBySlug($data['slug']);
            if($debug){	echo '->findOneBySlug(<strong>'.$data["slug"].'</strong>)';}
        }

        if($entity){
            if($debug){	echo '<br/>RESULT: EntityId =<strong>'.$entity->getId().'</strong></p>';}
            return $entity;
        }else{
            return false;
        }
    }

    public function searchArrayKeyVal($sKey, $id, $array) {
       foreach ($array as $key => $val) {
           if ($val[$sKey] == $id) {
               return $key;
           }
       }
       foreach ($array as $key => $val) {
          if (strtolower($val[$sKey]) == strtolower($id)) {
              return $key;
          }
      }

       return false;
    }

}
