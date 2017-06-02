<?php

namespace Prototype\SliderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;

use Prototype\SliderBundle\Entity\Slider;
use Prototype\SliderBundle\Entity\SliderImages;
//use Prototype\SliderBundle\Form\SliderType;
const SliderType ='Prototype\SliderBundle\Form\SliderType';
const SliderImageType ='Prototype\SliderBundle\Form\SliderImageType';
/**
 * Slider controller.
 *
 * @Route("/control/slider")
 */
class SliderController extends Controller
{
	/**
	* Lists all Slider entities.
	*
	* @Route("/", name="control_slider_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Image Sliders", active=true, routeName="control_slider_index", role="ROLE_SLIDER_EDITOR", icon="glyphicon glyphicon-picture", menuPosition=30, parentRouteName="control_content_index")
	*/
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$sliders = $em->getRepository('PrototypeSliderBundle:Slider')->findAllWithActiveSlides();

		return $this->render('PrototypeSliderBundle:slider:index.html.twig', array(
			'sliders' => $sliders,
		));
	}

    /**
     * Creates a new Slider entity.
     *
     * @Route("/new", name="control_slider_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $slider = new Slider();
        $form = $this->createForm(SliderType, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($slider);
                $em->flush();
                $this->addFlash('success','Success - Slider created');
                return $this->redirectToRoute('control_slider_edit', array( 'id'=> $slider->getId()) );
            }else{
                $this->addFlash('error','Error - Slider not saved');
            }
        }

        return $this->render('PrototypeSliderBundle:slider:new.html.twig', array(
            'slider' => $slider,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Slider entity.
     *
     * @Route("/{id}", name="control_slider_show")
     * @Method("GET")
     */
    public function showAction(Slider $slider)
    {
        $deleteForm = $this->createDeleteForm($slider);

        return $this->render('PrototypeSliderBundle:slider:show.html.twig', array(
            'slider' => $slider,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Slider entity.
     *
     * @Route("/{id}/edit", name="control_slider_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Slider $slider)
    {
        $deleteForm = $this->createDeleteForm($slider);
        $editForm = $this->createForm(SliderType, $slider);
        $editForm->handleRequest($request);

        $sliderImage = new SliderImages();
        $sliderImageForm = $this->createForm(SliderImageType, $sliderImage);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($slider);
                $em->flush();
                $this->addFlash('success','Success - slider updated');
                // return $this->redirectToRoute('control_slider_edit', array('id' => $slider->getId()));
                return $this->redirectToRoute('control_slider_index');
            }else{
                $this->addFlash('error','Error - slider not saved');
            }
        }

        return $this->render('PrototypeSliderBundle:slider:edit.html.twig', array(
            'slider' => $slider,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'slider_image_form' => $sliderImageForm->createView()
        ));
    }

    /**
     * Deletes a Slider entity.
     *
     * @Route("/{id}", name="control_slider_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Slider $slider)
    {
        $form = $this->createDeleteForm($slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $slider->setDeleted(true);
            $em->persist($slider);
            $em->flush();
            $this->addFlash('success','Success - slider deleted');
        }

        return $this->redirectToRoute('control_slider_index');
    }

    /**
     * Creates a form to delete a Slider entity.
     *
     * @param Slider $slider The Slider entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Slider $slider)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_slider_delete', array('id' => $slider->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Add slide to slier via ajax - return updated layout
     *
     * @Route("/control/slider/ajax_add_slide", name="control_slider_ajax_add_slide")
     * @Method({"GET", "POST"})
     */
    public function ajaxAddSlideAction(Request $request)
    {
        $sliderImage = new SliderImages();
        $sliderImageForm = $this->createForm(SliderImageType, $sliderImage);
        $sliderImageForm->handleRequest($request);

        $data = array();

        if ($sliderImageForm->isSubmitted()){
            if ($sliderImageForm->isValid()) {
                $sliderID = $sliderImage->getSlider()->getId();
                $em = $this->getDoctrine()->getManager();
                $existingSlides = $em->getRepository('PrototypeSliderBundle:SliderImages')->findBy(array(
                    'deleted' => false,
                    'slider'  => $sliderID
                ));
                $sliderImage->setPosition((count($existingSlides)+1));
                $em->persist($sliderImage);
                $em->flush();

                $html = $this->fetchSlidesAction($sliderID);
                return new Response(json_encode(array('status'=>'success', 'message'=>'Slide Added', 'html'=> $html)));
            }
        }

        return new Response(json_encode(array('status'=>'error', 'message'=>'Slide Invalid', 'html'=> '')));

    }



    /**
     *
     * @Route("/control/slider/ajax_update_slide", name="control_slider_ajax_update_slide")
     */
    public function ajaxUpdateSlideAction(Request $request)
    {
        $slideId = $request->query->get('slideId');
        $em = $this->getDoctrine()->getManager();
        $sliderImage = $em->getRepository('PrototypeSliderBundle:SliderImages')->find($slideId);

        // if($sliderImage){ echo "found";}else{ echo "nope";}
        // echo " [".$slideId."] sliderImage->getTitle() = ".$sliderImage->getTitle();
        // exit;

        $sliderImageForm = $this->createForm(SliderImageType, $sliderImage);
        $sliderImageForm->handleRequest($request);

        $data = array();

        if ($sliderImageForm->isSubmitted()){
            if ($sliderImageForm->isValid()) {
                $sliderID = $sliderImage->getSlider()->getId();
                $existingSlides = $em->getRepository('PrototypeSliderBundle:SliderImages')->findBy(array(
                    'deleted' => false,
                    'slider'  => $sliderImage->getSlider()->getId(),
                ));
                //$sliderImage->setPosition((count($existingSlides)+1));
                $em->persist($sliderImage);
                $em->flush();

                $html = $this->fetchSlidesAction($sliderID);
                return new Response(json_encode(array('status'=>'success', 'message'=>'Slide Added', 'html'=> $html)));
            }else{
                return new Response(json_encode(array('status'=>'error', 'message'=>'Slide Invalid', 'html'=> '')));
            }
        }

        $html = $this->renderView('PrototypeSliderBundle:slider:ajaxSlideForm.html.twig', array(
                'ajax_slide_form' => $sliderImageForm->createView(),
                'sliderImage' => $sliderImage
            ));

        return new Response(json_encode(array('status'=>'success', 'message'=>'Slide Found', 'html'=> $html, 'htmltextarea'=> $sliderImage->getHtml() )));

    }


    /**
     *
     * @Route("/control/slider/ajax_remove_slide", name="control_slider_ajax_remove_slide")
     */
    public function ajaxRemoveSlide(Request $request)
    {
        $slideId = $request->query->get('slideId');
        $em = $this->getDoctrine()->getManager();
        $sliderImage = $em->getRepository('PrototypeSliderBundle:SliderImages')->find($slideId);
        $sliderImage->setDeleted(1);
        $sliderImage->setActive(0);
        $em->persist($sliderImage);
        $em->flush();
        $html = $this->fetchSlidesAction($sliderImage->getSlider()->getId());
        return new Response(json_encode(array('status'=>'success', 'message'=>'Slide Removed', 'html'=> $html)));
    }


    public function fetchSlidesAction($sliderID)
    {
        $em = $this->getDoctrine()->getManager();
        $sliderImages = $em->getRepository('PrototypeSliderBundle:SliderImages')->findBy(array('deleted'=>false, 'active'=>true, 'slider'=>$sliderID), array('position'=>'ASC'));
        return $this->renderView('PrototypeSliderBundle:slider:slideRow.html.twig', array(
            'sliderImages' => $sliderImages,
        ));
    }


        /**
     * Add slide to slier via ajax - return updated layout
     *
     * @Route("/control/slider/ajax_change_slide_order", name="control_slider_ajax_change_slide_order")
     * @Method({"GET", "POST"})
     */
    public function ajaxChangeSlidePositionAction(Request $request)
    {

        $slideorder = $request->request->get('slideorder');
        if(!$slideorder){
             return new Response(json_encode(array('status'=>'error', 'message'=>'Slide Reorder Error', 'html'=> '')));
        }

        $em = $this->getDoctrine()->getManager();
        $order = 0;
        foreach($slideorder as $slide){
            $order++;
            $sliderImage = $em->getRepository('PrototypeSliderBundle:SliderImages')->find($slide);
            $sliderImage->setPosition($order);
            $em->persist($sliderImage);
            $sliderID = $sliderImage->getSlider()->getId();
        }

        $em->flush();

        $html = $this->fetchSlidesAction($sliderID);
        return new Response(json_encode(array('status'=>'success', 'message'=>'Slide Added', 'html'=> $html)));


    }



}
