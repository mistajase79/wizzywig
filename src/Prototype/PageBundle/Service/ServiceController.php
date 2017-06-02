<?php

namespace Prototype\PageBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * ProtoCMS Services - accessable anywhere
 * USAGE in Controller :  $this->container->get('pcgc_page_service')->testFunction();
 */

class ServiceController extends Controller
{

    public function __construct($kernel,RegistryInterface $doctrine){
        $this->container = $kernel->getContainer();
        $this->doctrine = $doctrine;
    }

    //TODO: redirect will not work in a service - needs a rethink
    public function refreshPageifLocalChanged($request){
        if($request->query->has('_locale')){
            if($request->query->get('_locale') != $request->getLocale() ){
                $request->setLocale($request->query->get('_locale'));
                $request->getSession()->set('_locale', $request->query->get('_locale'));
                return $this->redirect($request->getUri());
            }
        }
    }

    //helper function for getInheritedCMSPage() function
    //get bundle namespace for current controller
    public function getBundleNamespace($request){

        $controllerpath = $request->attributes->get('_controller');

        if (strpos($controllerpath, '::') !== false) {
            $params = explode('::',$controllerpath);
            $bundleStructure = explode('\\',$params[0]);
            return $bundleStructure[0].$bundleStructure[1];
        }else{
            $bundleStructure = explode(':',$controllerpath);
            return $bundleStructure[0];
        }

    }

    //finds page entity containing the inherited_bundle_name field relating to the bundle found in the request
    //used to link none CMS pages to a CMS page (inherits page data i.e. content, meta, ect...)
    public function getInheritedCMSPage($request){
        $bundleName = $this->getBundleNamespace($request);
        $pageEntity = $this->doctrine->getRepository('PrototypePageBundle:Page')->getInheritedBundleName($bundleName);
        return $pageEntity;
    }

    //Fetch cms component vars for all controllers containing the @ProtoCmsComponent annotation
    public function fetchProtoCmsComponents(){
        $cmsComponentArray = array();
        $annotationReader = new AnnotationReader();
        // Load all registered bundles
        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $name => $class) {
            // Check these are really your bundles, not the vendor bundles
            $bundlePrefix = 'Prototype';
            if (substr($name, 0, strlen($bundlePrefix)) != $bundlePrefix) continue;

            $namespaceParts = explode('\\', $class);
            // remove class name
            array_pop($namespaceParts);
            $bundleNamespace = implode('\\', $namespaceParts);
			$bundleNamespace = str_replace('\\', '/', $bundleNamespace);
            $rootPath = $this->container->get('kernel')->getRootDir().'/../src/';
            $controllerDir = $rootPath.$bundleNamespace.'/Controller';
            $files = scandir($controllerDir);

            foreach ($files as $file) {
                list($filename, $ext) = explode('.', $file);
                if ($ext != 'php'){ continue; }

                $class = $bundleNamespace.'\\Controller\\'.$filename;

				$class = str_replace('/', '\\', $class);
                $reflectedClass = new \ReflectionClass($class);

                foreach ($reflectedClass->getMethods() as $reflectedMethod) {
                    // the annotations
                    $annotations = $annotationReader->getMethodAnnotations($reflectedMethod);
                    if(!empty($annotations)){

                        foreach($annotations as $annotation){
                            $annotationName = get_class($annotation);
                            $pos = strpos(strtolower($annotationName), strtolower('ProtoCmsComponent'));
                            if($pos !== false) {
                                //var_dump($annotation);
                                if( $annotation->active == true){
                                    $cmsComponentArray[] = array(
                                        'name' => $annotation->propertyName,
                                        'slug' => $annotation->slug,
                                        'route' => $annotation->routeName,
                                        'slugEntity' => $annotation->slugEntity,
                                        'bundle' => $bundleNamespace,
                                        'componentType' => $annotation->componentType
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $cmsComponentArray;
    }



    public function getBundleNameFromEntity($entity, $field=null)
    {
        //get entity properties
        $reflClass = new \ReflectionClass($entity);
        $refMeth = $reflClass->getMethods();
        $refProp = $reflClass->getProperties();
        //get namespace
        $segments = explode('\\', $refProp[0]->class);
        $namespace = $segments[0].$segments[1].":".$segments[3];

        //get field data
        $nameMetadata = null;
        if($field !=null){
            $metadata = $this->doctrine->getManager()->getClassMetadata($refProp[0]->class);
            $nameMetadata = $metadata->fieldMappings[$field];
        }
        //print_r($nameMetadata);
        return array('full'=>$namespace,'short'=>$segments[3], 'fieldmeta'=>$nameMetadata);

    }


    //Fetch cms bundles registered with the kernel
    public function fetchProtoCmsBundles(){
        // Load all registered bundles
        $prototypeBundles = array();
        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $name => $class) {
            // echo "<br/>". $name ." : ". $class;
            // Check these are really your bundles, not the vendor bundles
            $bundlePrefix = 'Prototype';
            if (strpos($name, 'Prototype') !== false) {
                $prototypeBundles[] = $name;
            }
        }
        return $prototypeBundles;
    }



}
