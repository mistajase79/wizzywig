<?php
namespace Prototype\PageBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Gregwar\Cache\Cache;
use Gregwar\Image\Image;


class TwigExtension extends \Twig_Extension
{
    protected $authorizationChecker;

    public function __construct($kernel, Router $router, RegistryInterface $doctrine, AuthorizationCheckerInterface $authorizationChecker){
        $this->container = $kernel->getContainer();
        $this->router = $router;
        $this->doctrine = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getName()
    {
        return 'twig_extension';
    }

    public function getFilters()
    {
        return array(
            // new \Twig_SimpleFilter('filter', array($this, 'imageCache')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('renderPcgcComponents', array($this, 'renderPcgcComponents')),
            new \Twig_SimpleFunction('removeBracesFromSlug', array($this, 'removeBracesFromSlug')),
            new \Twig_SimpleFunction('generatePath', array($this, 'generatePath')),
            new \Twig_SimpleFunction('imageCache', array($this, 'imageCache')),
            new \Twig_SimpleFunction('fetchLocales', array($this, 'fetchLocales')),
            new \Twig_SimpleFunction('breadcrumbs', array($this, 'breadcrumbs')),
            new \Twig_SimpleFunction('renderPcgcHtmlBlock', array($this, 'renderPcgcHtmlBlock')),
            new \Twig_SimpleFunction('allowInlineEditor', array($this, 'allowInlineEditor')),
            new \Twig_SimpleFunction('showAdminControlLinks', array($this, 'showAdminControlLinks')),
            new \Twig_SimpleFunction('moneyFormat', array($this, 'moneyFormat')),
            new \Twig_SimpleFunction('domCheckIgnore', array($this, 'domCheckIgnore')),
            new \Twig_SimpleFunction('pcgcComponentEntities', array($this, 'pcgcComponentEntities')),
            new \Twig_SimpleFunction('pcgcComponentEntity', array($this, 'pcgcComponentEntity')),
            new \Twig_SimpleFunction('replaceIfComponentDataExists', array($this, 'replaceIfComponentDataExists')),
        );
    }

    public function pcgcComponentEntities($entityName=null, $field=null, $pageComponents){

        $checkfield = substr($field, 0, 3);
        if($checkfield !='get'){
          $field = 'get'.ucwords($field);
        }
        if(isset($component['urlKey'])){
          foreach($pageComponents as $component){
            if(strtolower($component['urlKey']) == strtolower($entityName)){
              if(method_exists($component['entity'], $field)){
                  $data = call_user_func(array($component['entity'], $field));
                  echo $data;
              }
            }
          }
        }
    }

    public function replaceIfComponentDataExists($pageComponents,$field=null, $fallback=null){

        $data = null;
        $checkfield = substr($field, 0, 3);
        if($checkfield !='get'){
          $field = 'get'.ucwords($field);
        }
        foreach($pageComponents as $component){
          if((isset($component['urlKey']) && $component['urlKey'] != null) && $component['data'] != null){
            if(method_exists($component['entity'], $field)){
                $data = call_user_func(array($component['entity'], $field));
            }
          }
        }

        if($data == null){
          echo $fallback;
        }else{
          echo $data;
        }

    }

    public function pcgcComponentEntity($field=null, $pageComponents){

        $checkfield = substr($field, 0, 3);
        if($checkfield !='get'){
          $field = 'get'.ucwords($field);
        }
        foreach($pageComponents as $component){
          if((isset($component['urlKey']) && $component['urlKey'] != null) && $component['data'] != null){
            if(method_exists($component['entity'], $field)){
                $data = call_user_func(array($component['entity'], $field));
                echo $data;
            }
          }
        }
    }

    public function moneyFormat($number){
        return number_format($number,2,',','.');
    }

	public function domCheckIgnore($value){

		if( is_array($value)){
			return null;
		}
	}


    //fetch image or generate new depending on parameter provided
    public function imageCache($pathtofile, $filter, $width=0, $height=0, $background='transparent')
    {
        //echo getcwd().$pathtofile." - ".$filter." W=".$width." H=".$height."<br/>";
        $showFallback = false;
        if($width ==0 || $height==0 ){
            echo "( Error: width or height not set )";
            return false;
        }

        if(!file_exists(getcwd().$pathtofile)){
            $fallback = $this->container->getParameter('gregwar_image.fallback_image');
            $pathtofile =  getcwd()."/".$fallback;
        }else{
            $pathtofile = getcwd().$pathtofile;
        }

        $file = explode('/', $pathtofile);
        $filename = end($file);
        $folder = $file[(count($file)-2)];
        $folder.= "/".$width."x".$height;
        $cacheDir = getcwd().'/userfiles/image_cache/'.$folder;

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


    //generate page url which translates to the current locale
    //get all slugs stored in the array cache to generate the path
    //example : <a href="{{generatePath( app.request, 7, {'blog_category': post.category.slug, 'blog_post_slug': post.slug}  )}}">
    public function generatePath($request, $pageID, $parametersArray = array())
    {
        $locale = $request->getLocale();
        $session = $request->getSession();
        $localePages = $session->get('localePages');
        $not_found = array();

        $cacheDriver = $this->container->get('doctrine_cache.providers.arraycache');
        $slugCache = $cacheDriver->fetch('slugCache');

        foreach(unserialize($slugCache) as $page){
            if($page['id'] == $pageID ){
                $finalUrl =  $page['slug'];
                $confirmedPagePieces =explode('/', $page['slug']);
                foreach($parametersArray as $key => $parameter){

                    $slugCheck = str_replace(" ", "", $key);
                    $slugKey = array_search("{".$slugCheck."}", $confirmedPagePieces);
                    if(!is_numeric($slugKey)){
                        $not_found[$key] = $parameter;
                    }else{
                        $finalUrl = str_replace("{".$slugCheck."}", $parameter, $finalUrl);
                    }
                }

                $getparams ="";
                if(count($not_found) > 0){
                    $getparams = "?";
                    foreach($not_found as $extraParam => $extraParamValue){
                        $getparams .= $extraParam."=".$extraParamValue."&";
                    }
                    $getparams = rtrim($getparams, "&");
                }
                return '/'.str_replace('//', '/', $finalUrl.$getparams);
            }

        }

        $getparams ="";
        if(count($not_found) > 0){
            $getparams = "?path=notfound&";
            foreach($not_found as $extraParam => $extraParamValue){
                $getparams .= $extraParam."=".$extraParamValue."&";
            }
            $getparams = rtrim($getparams, "&");
        }

        return "#".$getparams;

    }

    //used to tidy page {slugs}
    public function removeBracesFromSlug($string)
    {
        $new = preg_replace('/{[\s\S]+?}/', '', $string);
        return $new;
    }


    //Inserts the relevant component into the page template
    //Also assists the new/edit page component selector (domcrawler picks up on the domcheck attribute)
    public function renderPcgcComponents($positionId, $pageComponents)
    {

        if($pageComponents){
            if($pageComponents[0]['position'] == 'domcheck'){
                echo "<div data-pcgc='domcheck'>".$positionId."</div>";
            }

            foreach($pageComponents as $component){
                if($component['position'] == $positionId){
                    if(array_key_exists('data', $component)){ return $component['data']; }
                }
            }
        }

    }

    //Inserts the relevant htmlblock into the page template
    //Also assists the new/edit page component selector (domcrawler picks up on the domcheck attribute)
    public function renderPcgcHtmlBlock($positionId, $pageHtmlblocks)
    {
        if($pageHtmlblocks){
            if($pageHtmlblocks[0]['position'] == 'domcheck'){
                echo "<div data-pcgc='domcheckhtml'>".$positionId."</div>";
            }

            foreach($pageHtmlblocks as $block){
                if($block['position'] == $positionId){
                    if(array_key_exists('data', $block)){

                        if($this->authorizationChecker->isGranted('ROLE_PAGE_EDITOR')){
                            $editor = $this->getInlineEditorHTML('PrototypePageBundle:HtmlBlocks', 'html', $block['data'], $block['blockId'], 'HtmlBlock');
                            return $editor;
                        }else{
                            return $block['data'];
                        }

                    }
                }
            }
        }

    }


    function allowInlineEditor($entity, $field){

        $namespaceMeta = $this->container->get('pcgc_page_service')->getBundleNameFromEntity($entity, $field);
        $getterMethod = "get".ucwords($field);
        $editText = $entity->$getterMethod();
        if($editText == ""){return null;}
        if($this->authorizationChecker->isGranted('ROLE_PAGE_EDITOR')){
            $editor = $this->getInlineEditorHTML($namespaceMeta['full'], $field, $editText, $entity->getId(), $namespaceMeta['short'], $namespaceMeta['fieldmeta']);
            return $editor;
        }else{
            return $editText;
        }

    }


    function showAdminControlLinks($entity, $route){
        $namespaceMeta = $this->container->get('pcgc_page_service')->getBundleNameFromEntity($entity);

        $url = $this->router->generate($route, array('id' => $entity->getId()));

        if($this->authorizationChecker->isGranted('ROLE_ADMIN')){
            $buttons  = "<div class='adminControlButtons'>";
            $buttons .= "    <div class='inlineEditorToolboxContainer'>Admin Control (".$namespaceMeta['short'].")</div>";
            $buttons .= "    <div class='inlineEditorToolboxLink'><a href='".$url."' data-toggle='tooltip' title='View/Edit' ><span class='glyphicon glyphicon-pencil'></span>&nbsp;View/Edit</a></div>";
            $buttons .= "</div>";

            return $buttons;
        }

    }


    function getInlineEditorHTML($namespace, $field, $content, $id, $entityname=null, $fieldmeta = null){
        // show inline editor
        $locale = $this->container->get('request')->getLocale();
        $showFullEditor = 1;
        if($fieldmeta != null){ if($fieldmeta['type'] == 'string'){ $showFullEditor = 0; } }
        //Redactor required uniqueIDs - classes conflicted if multiple editors were used
        $uniqueID = substr(md5(rand(1,9999)),0, 7);
        $editor  = "<div class='inlineEditorContainer'>";
        $editor .= "    <div id='inlineEditor-message-".$uniqueID."' class='inlineEditorToolboxContainer'>Editable (".$entityname.":".$field.")</div>";
        $editor .= "    <div class='inlineEditorToolboxSave'><a data-toggle='tooltip' title='Save Text' id='btn-save-".$uniqueID."' style='display:none'><span class='glyphicon glyphicon-floppy-disk'></span>&nbsp;Save</a></div>";
        // $editor .= "    <div class='inlineEditorToolboxClose'><a data-toggle='tooltip' title='Close Editor' id='btn-cancel-".$uniqueID."' style='display:none'><span class='glyphicon glyphicon-remove-sign'></span></a></div>";

        $editor .= "    <div class='inlineEditor' data-fulleditor='".$showFullEditor."' id='".$uniqueID."' data-entitynamespace='".$namespace."'  data-entityfield='".$field."' data-id='".$id."' data-locale='".$locale."' >";
        if($showFullEditor ==0){ $editor .=  "<p>"; }
        $editor .=  $content;
        if($showFullEditor ==0){ $editor .=  "</p>"; }
        $editor .= "    </div>";

        // if($showFullEditor ==1){
        //     $editor .= "    <textarea class='inlineEditor' data-fulleditor='".$showFullEditor."' id='".$uniqueID."' data-entitynamespace='".$namespace."'  data-entityfield='".$field."' data-id='".$id."' data-locale='".$locale."' >";
        //     $editor .= $content;
        //     $editor .= "    </textarea>";
        // }else{
        //     $editor .= "    <input type='text' class='inlineEditor' data-fulleditor='".$showFullEditor."' value='".$content."' id='".$uniqueID."' data-entitynamespace='".$namespace."'  data-entityfield='".$field."' data-id='".$id."' data-locale='".$locale."' />";
        // }


        $editor .= "</div>";
        return $editor;
    }

    //simple function to fetch all locales
    //done this way to ensure locale switch buttons work on non cms pages
    function fetchLocales(){
        $locales = $this->doctrine->getRepository('PrototypePageBundle:Locales')->findBy(array('active'=>true));
        return $locales;
    }

    function breadcrumbs($pageEntity=null, $currentUrl=null){
        // // this function generates a full url category path

        //$currentUrl = $this->container->get('request')->getUri();

		if($pageEntity != null && $currentUrl != null ){
	        $exploded = explode('/', $currentUrl);
	        unset($exploded[0]);
	        $exploded = array_values($exploded);

	        $structure = array();
	        for($i=0; $i<count($exploded); $i++){
	            if(array_key_exists(($i-1), $structure)){
	                $structure[$i] = $structure[($i-1)].'/'.$exploded[$i];
	            }else{
	                $structure[$i] = '/'.$exploded[$i];
	            }

	            if(array_key_exists(($i-1), $structure)){
	                $url = $structure[($i-1)]['url'].'/'.$exploded[$i];
	            }else{
	                $url = '/'.$exploded[$i];
	            }

	            $structure[$i] = array('url' => $url, 'title' =>$exploded[$i]);

	        }
	        //print_r($structure);
	        $seperater = " > ";
	        $html = '<div class="breadcrumb">';
	        $html .='<span><a href="/">Home</a>'.$seperater.'</span>';
	        $count =0;
	        foreach( $structure as $item){
	            $count++;
	            if(count($structure) == $count){ $seperater =""; }
	            $html .='<span><a href="'.$item["url"].'">'.str_replace('-', ' ', ucfirst($item["title"])).'</a>'.$seperater.'</span>';
	        }
	        $html .='</div>';

	        echo $html;
		}
    }


    //simple function to pluralise text string (adds 's' if array count >1 )
    function pluralize($text, $array, $plural_version = null)
    {
        return count($array) > 1 ? ($plural_version ? $plural_version : ($text . 's')) : $text;
    }


}
