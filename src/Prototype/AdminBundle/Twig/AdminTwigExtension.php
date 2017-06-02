<?php
namespace Prototype\AdminBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Gregwar\Cache\Cache;
use Gregwar\Image\Image;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminTwigExtension extends \Twig_Extension
{

    public function __construct($kernel, Router $router, RegistryInterface $doctrine,RequestStack $requestStack){
        $this->container = $kernel->getContainer();
        $this->router = $router;
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
    }

    public function getName()
    {
        return 'admin_twig_extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pluralisation', array($this, 'pluralisation')),
            new \Twig_SimpleFunction('showCurrentLocale', array($this, 'showCurrentLocale')),
            new \Twig_SimpleFunction('cmsBreadcrumb', array($this, 'cmsBreadcrumb')),
            new \Twig_SimpleFunction('generateAdminDashMenu', array($this, 'generateAdminDashMenu')),
            new \Twig_SimpleFunction('viewAvailableTranslations', array($this, 'viewAvailableTranslations')),
            new \Twig_SimpleFunction('buildNestedMenu', array($this, 'buildNestedMenu')),
            new \Twig_SimpleFunction('humanTiming', array($this, 'humanTiming')),
            new \Twig_SimpleFunction('wordLimit', array($this, 'wordLimit')),
            new \Twig_SimpleFunction('imageCache', array($this, 'imageCache')),
            new \Twig_SimpleFunction('underscore', array($this, 'underscore')),
            new \Twig_SimpleFunction('dashes', array($this, 'dashes')),
        );
    }

    function pluralisation($str, $units){

        if(substr($str, 0, -3) == 'ies'){ $str = substr($str, 0, -3).'y';}
        if(substr($str, -1) == 's'){ $str = substr($str, 0, -1); }

        $lastletter = substr($str, -1);

        if($units != 1){ return $str.'s'; }
        if($lastletter == 'y' && $units != 1){ return substr($str, 0, -1).'ies'; }

        return $str;
    }

    function underscore($str){
        //convert camelcase to underscores
        return strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $str));
    }
    function dashes($str){
        //convert camelcase to dash split
        return strtolower(preg_replace('/(.)([A-Z])/', '$1-$2', $str));
    }

    function wordLimit($text, $limit) {
      if (str_word_count($text, 0) > $limit) {
          $words = str_word_count($text, 2);
          $pos = array_keys($words);
          $text = substr($text, 0, $pos[$limit]) . '...';
      }
      return $text;
    }


    function humanTiming($datetime){

            if(is_object($datetime)){
                $time = $datetime->getTimestamp();
            }else{
                $time = strtotime($datetime);
            }

            $time = time() - $time; // to get the time since that moment
            $time = ($time<1)? 1 : $time;
            $tokens = array (
                31536000 => 'year',
                2592000 => 'month',
                604800 => 'week',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second'
            );

            foreach ($tokens as $unit => $text) {
                if ($time < $unit) continue;
                $numberOfUnits = floor($time / $unit);
                return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
            }

    }

    public function viewAvailableTranslations($entity){

        $repository = $this->doctrine->getRepository('Gedmo\Translatable\Entity\Translation');
        $findroute = explode('\\', get_class($entity) );
        //var_dump(get_object_vars($entity));
        $entityName = end($findroute);

        //$routeName = "control_".strtolower($entityName)."_translation_edit";
        //add underscores before capital letters to match generator
        $routeName = "control_".strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $entityName))."_translation_edit";

        $router = $this->container->get('router');
        if($router->getRouteCollection()->get($routeName) === null){ $found1 = 0;}else{ $found1 = 1;}
        if(!$found1){
        //    return null;
            $routeName = "control_".strtolower($entityName)."_translation_edit";
            if($router->getRouteCollection()->get($routeName) === null){ $found2 = 0;}else{ $found2 = 1;}
            if(!$found2){
                //another route match
                $currentRouteName =  $this->requestStack->getCurrentRequest()->get('_route');
                $currentArray = explode('_',$currentRouteName);
                array_pop($currentArray);
                $guessRoute = implode('_',$currentArray);
                $routeName2 = $guessRoute."_translation_edit";
                if($router->getRouteCollection()->get($routeName2) === null){ $found3 = 0;}else{ $found3 = 1;}
                if(!$found3){
                    throw new \InvalidArgumentException('PCGC: Route name "'.$routeName.'" was generated by "viewAvailableTranslations" but didnt exist ALSO tried to match "'.$routeName2.'"- you must create this route or rename (or fix a typo) an existing route to edit translations for '. get_class($entity));
                }else{
                    $routeName= $routeName2;
                }
            }
        }

        $translations = $repository->findTranslations($entity);
        foreach($translations as $locale => $fields ){
            echo "<a href='". $this->router->generate($routeName, array('id'=>$entity->getId(), 'locale' =>$locale  ))."'><img src='/control/flags/png/".$locale.".png' /></a>&nbsp;";
        }
    }

    public function showCurrentLocale($locale){
        $localeEntity = $this->doctrine->getRepository('PrototypePageBundle:Locales')->findOneBy(array('locale' => $locale));
        if($localeEntity){
            echo "<div class='form-group'>";
            echo "  <label class='form-lable'>Current Locale</label>";
            echo "  <div class='form-control'>";
            echo "      <img alt='".$locale."' src='/control/flags/png/".$locale.".png' />";
            echo "      <span>&nbsp; ".$localeEntity->getLanguage()." (".$locale.") </span>";
            echo "  </div>";
            echo "</div>";
        }

    }



    public function cmsBreadcrumb($path)
    {
        if($location = substr($path, 1)){
           $dirlist = explode('/', $location);
        }else{
           $dirlist = array();
        }
        $count = array_push($dirlist,$path);
        $link ="";
        for($i = 0; $i < $count-1; $i++){
            $link .= '/'.$dirlist[$i];
            $class = ( $i == ($count-1) ) ? 'active' : '';
            if(!is_numeric($dirlist[$i]) && $i >0){
                if($dirlist[$i] !="" && strpos($dirlist[$i] , '?') === false ){
                    $title = str_replace('-', ' ', $dirlist[$i]);
                    if (strpos($title, 'translation') !== false) { $showlink = "/".$location; }else{ $showlink = $link; }
                    echo '<li class="'.$class.'"><a href="'.$showlink.'">'.ucwords($title).'</a></li>';
                }
            }
        }
    }

    public function generateAdminDashMenu($request = null){

        if($request != null){$currentRoute = $request->get('_route');}else{$currentRoute= null; }
        $menuItems = $this->fetchAdminDashAnnotions($currentRoute);

        //sort menu items
        usort($menuItems, function($a, $b) {
            return $a['menuPosition'] - $b['menuPosition'];
        });

        $html = $this->build_menu($menuItems, $parent=null, $currentRoute, $depth=0);

        return $html;

    }

    function has_children($menuItems,$route) {
        foreach ($menuItems as $menuItem) {
            if ($menuItem['parent'] == $route)
            return true;
        }
        return false;
    }



    function build_menu($menuItems,$parent=null, $currentRoute, $depth=0){
        if($depth==0){
            $result = '<ul class="sidebar-menu">';
            $result.= '<li class="header">SITE MANAGEMENT</li>';
        }else{
            $result = "<ul class='treeview-menu'>";
        }
        $authChecker = $this->container->get('security.authorization_checker');
        foreach ($menuItems as $menuItem){
            $depth++;
            $url = $this->router->generate($menuItem['route']);
            $treeIcon = '';
            if($menuItem['dontLink'] == true){ $url = '#'; $treeIcon = '<i class="fa fa-angle-left pull-right"></i>';}

            if( $authChecker->isGranted($menuItem['role']) ){
                $link = '<a href="'.$url.'"><i class="'.$menuItem['icon'].' "></i> <span>'.$menuItem['name'].'</span>'.$treeIcon.'</a>';
                if($currentRoute == $menuItem['route']){ $active ="active"; }else{ $active=""; }
                if ($this->has_children($menuItems,$menuItem['route'])){ $isTree ="treeview"; }else{ $isTree=""; }
                if ($menuItem['parent'] == $parent){
                    $result.= "<li class='$active $isTree' >".$link;
                    if ($this->has_children($menuItems,$menuItem['route'])){
                        $result.= $this->build_menu($menuItems,$menuItem['route'],$currentRoute, $depth);
                    }
                    $result.= "</li>";
                }
            }
        }
        $result.= "</ul>";
        return $result;
    }


    function fetchAdminDashAnnotions(){

        $menuItemsArray = array();
        $annotationReader = new AnnotationReader();
        // Load all registered bundles
        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $name => $class) {
            // Check these are really our bundles, not the vendor bundles
            $bundlePrefix = 'Prototype';
            if (substr($name, 0, strlen($bundlePrefix)) != $bundlePrefix) continue;
            $namespaceParts = explode('\\', $class);
            // remove class name
            array_pop($namespaceParts);
            $bundleNamespace = implode('\\', $namespaceParts);
            $rootPath = $this->container->get('kernel')->getRootDir().'/../src/';
            $controllerDir = str_replace('\\', '/', $rootPath.$bundleNamespace.'/Controller');

            $files = scandir($controllerDir);

            foreach ($files as $file) {
                list($filename, $ext) = explode('.', $file);
                if ($ext != 'php') continue;

                $class = $bundleNamespace.'\\Controller\\'.$filename;
                $reflectedClass = new \ReflectionClass($class);

                foreach ($reflectedClass->getMethods() as $reflectedMethod) {
                    // the annotations
                    $annotations = $annotationReader->getMethodAnnotations($reflectedMethod);
                    if(!empty($annotations)){

                        foreach($annotations as $annotation){
                            $annotationName = get_class($annotation);
                            $pos = strpos(strtolower($annotationName), strtolower('ProtoCmsAdminDash'));
                            if($pos !== false) {

                                //var_dump($annotation);
                                if( $annotation->active == true){
                                    $menuItemsArray[] = array(
                                        'name' => $annotation->propertyName,
                                        'route' => $annotation->routeName,
                                        'icon' => $annotation->icon,
                                        'parent' => $annotation->parentRouteName,
                                        'role' => $annotation->role,
                                        'dontLink' => $annotation->dontLink,
                                        'menuPosition' => $annotation->menuPosition
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $menuItemsArray;
    }


    public function buildNestedMenu($array, $no = 0) {
        $template = $this->container->get('templating')->render("PrototypeMenuBundle:menu:menuitem-template.html.twig");
        $content = "<ol id=\"nestedMenu\" class=\"sortable ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded\">";
            $content .= $this->makeMenu($array, $no, $template, 0, count($array));
        $content .= "</ol>";

        return $content;
    }

    // {% set num = menuitem.menuItemId %}
    // {% set pageId = menuitem.pageId.id %}
    // {% set navTitle = menuitem.pageId.navTitle %}
    // {% set slug = menuitem.pageId.slug %}
    // {% set num = "__num__" %}
    // {% set pageId = "__pageId__" %}
    // {% set navTitle = "__navtitle__" %}
    // {% set slug = "__slug__" %}

    public function makeMenu($array, $no = 0, $template, $currentcount, $length) {

        $child = $this->hasMenuChildren($array, $no);
        if (empty($child)){	return "";}

        if($currentcount > 0 ){ $content = "<ol>\n";}else{	$content = ""; }
        $currentcount++;
        foreach ( $child as $value ) {

            if(!is_array($value)){
                //check if array( unpersisted - form error ) or object (on edit page)
                $item['menu_item_id'] =  $value->getMenuItemId();
                $item['pageId'] =  $value->getPageId()->getId();
                $item['navtitle'] =  $value->getPageId()->getNavTitle();
                $item['slug'] =  $value->getPageId()->getSlug();
                $item['name_override'] =  $value->getNameOverride();
            }else{
                $item = $value;
            }

            $originals = array("__num__", "__pageId__", "__navtitle__", "__slug__","__nameoverride__", "</li>");
            $replacements = array($item['menu_item_id'], $item['pageId'], $item['navtitle'], $item['slug'], $item['name_override'], "");
            $newTemplate = str_replace($originals, $replacements, $template);
            $content .= $newTemplate;
            $content .= $this->makeMenu($array, $item['menu_item_id'], $template, $currentcount, $length)."</li>";
            $currentcount++;
        }

        $content .= "</ol>\n";

        return $content;
    }


    function hasMenuChildren($array, $id) {
        return array_filter($array, function ($var) use($id) {
            if(is_array($var)){
                return $var['menu_parent_id'] == $id;
            }else{
                return $var->getMenuParentId() == $id;
            }
        });
    }


    public function imageCache($pathtofile, $filter, $width=0, $height=0, $background='transparent')
    {
        //echo getcwd().$pathtofile." - ".$filter." W=".$width." H=".$height."<br/>";

        $showFallback = false;
        if($width ==0 || $height==0 ){
            echo "( Error: width or height not set )";
            return false;
        }

        //echo  getcwd().$this->container->getParameter('gregwar_image.fallback_image')."----";
        $fallback = $this->container->getParameter('gregwar_image.fallback_image');

        if(!is_file(getcwd().$pathtofile)){
            $pathtofile =  getcwd().$fallback;
        }else{
            $pathtofile = getcwd().$pathtofile;
        }

        $pathtofile = str_replace('\\', '/',$pathtofile );

        $file = explode('/', $pathtofile);
        $filename = end($file);
        $folder = $file[(count($file)-2)];
        $folder.= "/".$width."x".$height;
        $cacheDir = getcwd().'/userfiles/image_cache/control/'.$folder;

        //echo "[".$pathtofile."]";

        switch (strtolower($filter)) {
            case 'scaleresize':
                $generateFile = Image::open($pathtofile)->scaleResize($width,$height)->fixOrientation()
                    ->fillBackground($background)->setCacheDir($cacheDir.'-sr')
                    ->setPrettyName($filename,false)->guess(80);
                break;
            case 'cropresize':
                $generateFile = Image::open($pathtofile)->cropResize($width,$height)->fixOrientation()
                    ->fillBackground($background)->setCacheDir($cacheDir.'-cr')
                    ->setPrettyName($filename,false)->guess(80);
                break;
            case 'zoomcrop':
                $generateFile = Image::open($pathtofile)->zoomCrop($width,$height)->fixOrientation()
                    ->fillBackground($background)->setCacheDir($cacheDir.'-zc')
                    ->setPrettyName($filename,false)->guess(80);
                break;
            default:
                $generateFile = Image::open($pathtofile)->scaleResize($width,$height)->fixOrientation()
                    ->fillBackground($background)->setCacheDir($cacheDir.'-sr')
                    ->setPrettyName($filename,false)->guess(80);
        }
        //echo "/userfiles/image_cache/".$folder."/".$filename;
        //echo $generateFile;

        echo str_replace(getcwd(),"",$generateFile);

    }



}
