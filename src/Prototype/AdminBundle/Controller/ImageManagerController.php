<?php

namespace Prototype\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Prototype\AdminBundle\Entity\ImageManager;
const ImageManagerType = 'Prototype\AdminBundle\Form\ImageManagerType';

class ImageManagerController extends Controller
{

    /**
     * @Route("/imagemanager-open", name="control_imagemanager_openfolder")
	 * @Security("has_role('ROLE_ADMIN')")
     */
    public function openFolderAction(Request $request)
    {
        $folder = $request->request->get('path');
        $dir    = getcwd().$folder;

        $fs = new Filesystem();

        if (!file_exists($dir)) {

            try {
                $fs->mkdir($dir);
            } catch (IOExceptionInterface $e) {
                $response = array('status'=> 'error', 'message'=> "An error occurred while creating your directory at ".$e->getPath());
                return new Response(json_encode($response));
            }

        }


        $folderContents = scandir($dir);

        $result = array();

        foreach ($folderContents as $value){
            if (!in_array($value,array(".",".."))) {
                if (strpos($value, 'tn_') === false) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
                        $result[] = array('name'=>$value,'folder'=>1, 'type'=>'folder', 'icon'=>'/control/folder-icon.png');
                    }else{
                        $file = explode('.',$value);
                        $result[] = array('name'=>$value,'folder'=>0, 'type'=>end($file), 'icon'=> $folder.'/tn_'.$value);
                    }
                }
            }
        }

        //$data = '<pre>'.print_r($result,true).'</pre>';
        $response = array('status'=> 'success', 'message'=> '', 'data' => $result);
        return new Response(json_encode($response));

    }



    /**
     * Ajax Image Upload
     *
     * @Route("/imagemanager-upload", name="control_imagemanager_uploadfile")
     */
    public function uploadFileAction(Request $request)
    {
        $imagemanager = new ImageManager();
        $form = $this->createForm(ImageManagerType, $imagemanager);
        $form->handleRequest($request);

        $response=array();

        if ($form->isSubmitted()){


            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $imagemanager->resizeImage($this->get('image.handling'));
                $filename = $imagemanager->getImage();
                $folder = $imagemanager->getFilePath();
                $file = explode('.',$filename);
                $result = array('name'=>$filename,'folder'=>0, 'type'=>end($file), 'icon'=> $folder.'tn_'.$filename);
                $response = array('status'=> 'success', 'message'=> 'File Ok!', 'data' => $result);
                //return new Response(json_encode($response));
            }else{
                $errors = $this->buildErrorArray($form);
                $response = array('status'=> 'error', 'message'=> 'Error Uploading file', 'errors'=>$errors);
                //return new Response(json_encode($response));

            }
       }else{
           $response = array('status'=> 'error', 'message'=> 'File not submitted');
           //return new Response(json_encode($response));
       }

        //$response = array('status'=> 'error', 'message'=> 'File not sent');
        return new Response(json_encode($response));

        // print_r($response);
        //
        // return $this->render('PrototypeAdminBundle:Form:imagetest.html.twig', array(
        //     'imagemanager' => $imagemanager,
        //     'form' => $form->createView(),
        // ));
    }

    public function buildErrorArray($form)
    {
        $errors = array();

        foreach ($form->all() as $child) {
            $errors = array_merge(
                $errors,
                $this->buildErrorArray($child)
            );
        }

        foreach ($form->getErrors() as $error) {
            //$errors[$error->getCause()->getPropertyPath()] = $error->getMessage();
            $errors[] = $error->getMessage();
        }

        return $errors;
    }


    /**
     * Ajax Image Upload
     * @Route("/redactor-upload", name="control_redactor_uploadfile")
     */
    public function redactorUploadFileAction(Request $request)
    {

        $response=array();
        $errors=0;

        $dir = '/userfiles/images/redactor/';
        $fileObj = $request->files->get('file');

        // $class_methods = get_class_methods($fileObj);
        // foreach ($class_methods as $method_name) {
        //     echo "$method_name\n";
        // }

        $allowedTypes = array('image/png','image/jpg','image/gif','image/jpeg','image/pjpeg');

        if (!in_array(strtolower($fileObj->getMimeType()), $allowedTypes)){
          $errors++;
          $response = array(
              'error' => 'Image type not recognized (png, jpg, gif allowed)'
          );
        }

        if($fileObj->getClientSize() > 1000000 ){
          $errors++;
          $response = array(
              'error' => 'Image too large (1MB+)',
              'anothermessage' => 'Please consider loading times'
          );
        }

        if($errors ==0){
          $filename = $fileObj->getClientOriginalName();
          $filenameChunks = explode('.', $filename);
          $file = $filenameChunks[0].'-'.date('dmyhi').'.'.end($filenameChunks);

          $fileObj->move(getcwd().$dir,$file);

          $response = array(
              'url' => $dir.$file
          );
        }

        return new Response(stripslashes(json_encode($response)));

    }





}
