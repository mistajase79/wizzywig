<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\PageBundle\Entity\Page;

use Symfony\Component\Form\FormError;
use Prototype\PageBundle\Entity\Locales;
use Prototype\PageBundle\Form\PageTranslationType;

const PageTranslationType = 'Prototype\PageBundle\Form\PageTranslationType';

/**
 * Page Translation controller.
 *
 * @Route("/")
 */
class PageTranslationController extends Controller
{
    /**
     * Creates a new Page entity.
     *
     * @Route("/page-translation/{id}/new", name="control_page_translation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Page $page)
    {

        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));

        $localsAndAttributesArray = $this->container->get('pcgc_translation_services')->fetchAvailableTranslations($page, $locales);
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();

        $form = $this->createForm(PageTranslationType, $page, array(
            'localsAndAttributesArray' =>$localsAndAttributesArray )
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()){

            // find and save translatable setters
            $formData = $request->get('page_translation');
            $page = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($page, $formData);
            $page->setTranslatableLocale($formData['translatableLocale']); // change locale

            ////////////////////////
            // handle Slug
            $newslug = $this->generateTranslatableSlug($page,$formData['translatableLocale']);
            $appendslug = $newslug.$this->appendComponentTranslatableSlug($page);
            $page->setSlug($appendslug);
            ////////////////////////

            $em->persist($page);
            $em->flush();

            $parentCheck = $em->getRepository('PrototypePageBundle:Page')->findOneByParent($page->getId());
            if($parentCheck){$this->updateParentTreeTranslatableSlugs($formData['translatableLocale']);}

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');
            $request->getSession()->set('_locale', 'en');
            $request->setLocale('en');
            return $this->redirectToRoute('control_page_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypePageBundle:page:saveTranslation.html.twig', array(
            'page' => $page,
            'cmsComponentArray' => $cmsComponentArray,
            'form' => $form->createView(),
        ));
    }


    /**
     *
     * @Route("/page-transaltion/{id}/edit/{locale}", name="control_page_translation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Page $page, $locale)
    {
        $defaultPage = $page;
        // load translation
        $em = $this->getDoctrine()->getManager();
        $locales = $em->getRepository('PrototypePageBundle:Locales')->findBy(array('active' => 1));
        $page->setTranslatableLocale($locale);
        $em->refresh($page);

        //create form
        $form = $this->createForm(PageTranslationType, $page, array('currentLocale' =>$locale ));
        $page->setTranslatableLocale($locale); // change locale

        $form->handleRequest($request);

        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
        //print_r($page->getComponents());

        if ($form->isSubmitted()){
            // find and save translatable setters
            $formData = $request->get('page_translation');
            $page = $this->container->get('pcgc_translation_services')->findAndSetTranslatableEntityData($page, $formData);
            $page->setTranslatableLocale($locale); // set locale

            ////////////////////////
            // handle Slug
            $newslug = $this->generateTranslatableSlug($page,$locale);
            $appendslug = $newslug.$this->appendComponentTranslatableSlug($page);
            $page->setSlug($appendslug);
            ////////////////////////

            $em->persist($page);
            $em->flush();

            $parentCheck = $em->getRepository('PrototypePageBundle:Page')->findOneByParent($page->getId());
            if($parentCheck){$this->updateParentTreeTranslatableSlugs($locale);}

            $this->addFlash('success', ucwords($formData['translatableLocale']).' translation Updated');

            $request->getSession()->set('_locale', 'en');
            $request->setLocale('en');
            return $this->redirectToRoute('control_page_index');
            ///////////////////////////////////////////
        }

        return $this->render('PrototypePageBundle:page:saveTranslation.html.twig', array(
            'page' => $page,
            'cmsComponentArray' => $cmsComponentArray,
            'form' => $form->createView(),
            'locale' => $locale
        ));

    }

    public function allTranslatableSlugHandlerAction($page){

        ////////////////////////
        // handle Slug
        $defaultPage = $page;
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($page);

        $localeMessageArray = array();

        foreach($translations as $locale => $valueArray){
            $page->setTranslatableLocale($locale);
            $em->refresh($page);
            $newslug = $this->generateTranslatableSlug($page,$locale);
            $appendslug = $newslug.$this->appendComponentTranslatableSlug($page);

            //echo "<br/>[".$locale." : ".$appendslug."]";
            $page->setSlug($appendslug);
            $em->persist($page);
            $em->flush();
            $localeMessageArray[] = $locale;
        }

        $localeMessageString = $this->pluralize_if_many('translation',$localeMessageArray);
        $message =  '('.count($localeMessageArray).' '.$localeMessageString.' may also have been updated)';
        $page = $defaultPage; //switch entity language back


        return new Response($message);

        ////////////////////////
    }

    function pluralize_if_many($text, $array, $plural_version = null){
        return count($array) > 1 ? ($plural_version ? $plural_version : ($text . 's')) : $text;
    }


    public function appendComponentTranslatableSlug($page){
        $cmsComponentArray = $this->container->get('pcgc_page_service')->fetchProtoCmsComponents();
        foreach($cmsComponentArray as $cmsComponent){
            foreach($page->getComponents() as $formComponent){
                if($cmsComponent['route'] !=""){
                    if($cmsComponent['route'] == $formComponent['route']){
                        if($cmsComponent['slug'] !=""){
                            return "/".$cmsComponent['slug'];
                        }
                    }
                }
            }
        }
    }

    function generateTranslatableSlug($page, $locale){

        $em = $this->getDoctrine()->getManager();
        $parentId = $page->getId();
        $defaultpage = $em->getRepository('PrototypePageBundle:Page')->find($page->getId());
        $parentEntity = $page->getParent();
        $sluggerFunction = $this->container->get('pcgc_sluggify');

        if($parentEntity != null){
            $parentEntity->setTranslatableLocale($locale);
            $em->refresh($parentEntity);
            $newslug = $this->checkForParent(
            $parentEntity,
            $sluggerFunction->makeSlugs(  $page->getUrl() ), $locale);
        }else{
            $newslug = $sluggerFunction->makeSlugs( $page->getUrl() );
        }

        $extras = $defaultpage->getExtraUrlsegments();
        if(count($extras)>0){
            foreach($extras as $extra){
                if($extra['urlsegment'] !=""){$newslug .= "/".$extra['urlsegment'];}
            }
        }

        return $newslug;
    }

    /**
     * @Route("/page/ajax-translatable-slug-preview")
     */
    function ajaxGenerateSlugPreviewAction(Request $request){
        $pagetitle = $request->get('pagetitle');
        $pageurl = $request->get('pageurl');
        $parentId = $request->get('parentId');
        $locale = $request->get('locale');
        $extraArray = $request->get('extraArray');

        $em = $this->getDoctrine()->getManager();
        $parentEntity = $em->getRepository('PrototypePageBundle:Page')->find($parentId);
        $sluggerFunction = $this->container->get('pcgc_sluggify');

        if($parentEntity != null){
            $parentEntity->setTranslatableLocale($locale);
            $em->refresh($parentEntity);
            $newslug = $this->checkForParent($parentEntity,	$sluggerFunction->makeSlugs( $pageurl), $locale);
        }else{
            $newslug = $sluggerFunction->makeSlugs($pageurl );
        }


        if(count($extraArray)>0){
            foreach($extraArray as $extra){
                if($extra !=""){$newslug .= "/".$extra;}
            }
        }

        return new Response($newslug);
    }

    function checkForParent($parent, $slug, $locale){
        $em = $this->getDoctrine()->getManager();
        $parent->setTranslatableLocale($locale);
        $em->refresh($parent);

        $sluggerFunction = $this->container->get('pcgc_sluggify');
        $slug = $sluggerFunction->makeSlugs($parent->getUrl() )."/".$slug;

        if($parent->getParent() === null){
            return $slug;
        }else{
            return $this->checkForParent($parent->getParent(), $slug, $locale);
        }
    }

    function updateParentTreeTranslatableSlugs($locale){
        $em = $this->getDoctrine()->getManager();

        $pagesSlugsToUpdate = $em->getRepository('PrototypePageBundle:Page')->findAll();
        foreach($pagesSlugsToUpdate as $page){
            $parentEntity = $page->getParent();
            if($parentEntity != null){
                $data = $page;
                $parentEntity->setTranslatableLocale($locale);
                $em->refresh($parentEntity);
                $page->setTranslatableLocale($locale);
                $em->refresh($page);

                // $newslug = $this->checkForParent(
                // $parentEntity,
                // $this->container->get('pcgc_sluggify')->makeSlugs( $page->getUrl()), $locale);
                $newslug = $this->generateTranslatableSlug($page, $locale);

                $appendedslug = $newslug.$this->appendComponentTranslatableSlug($data);

                $page->setSlug(str_replace('//', '/',$appendedslug));

                $em->persist($page);
            }
        }
        $em->flush();
    }



}
