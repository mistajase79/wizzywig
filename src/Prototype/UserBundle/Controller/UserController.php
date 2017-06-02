<?php

namespace Prototype\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Prototype\UserBundle\Entity\User;
//use Prototype\UserBundle\Form\UserType;
const UserAdminType ='Prototype\UserBundle\Form\UserAdminType';
const UserAdminCreateType ='Prototype\UserBundle\Form\UserAdminCreateType';
const UserAdminChangePasswordType ='Prototype\UserBundle\Form\UserAdminChangePasswordType';

/**
 * User controller.
 *
 * @Route("/control/user")
 */
class UserController extends Controller
{

    public function getAllRoles(){
        $roles =  array(
            'ROLE_CMS_ACCESS' => 'ROLE_CMS_ACCESS (Basic Access - dash only)',
            'ROLE_PAGE_EDITOR' => 'PAGE_EDITOR (Edit/Create Pages)',
            'ROLE_MENU_EDITOR' => 'MENU_EDITOR (Modify Menus)',
            'ROLE_NEWS_EDITOR' => 'NEWS_EDITOR (Post News)',
            'ROLE_ENQUIRIES_ACCESS' =>'ENQUIRIES ACCESS (View Enquiries)',
            //'ROLE_CASESTUDIES_EDITOR' => 'CASESTUDY_EDITOR (Add/Edit Case Studies)',
            //'ROLE_SLIDER_EDITOR' => 'SLIDER_EDITOR (Modify Sliders)',
            //'ROLE_CATALOG_EDITOR' => 'CATALOG_EDITOR (Modify Products & Categories)',
            'ROLE_ADMIN' => 'ROLE_ADMIN (All Areas -except admin)',
            'ROLE_SUPER_ADMIN' => 'SUPER_ADMIN (Unrestricted Access)',
        );

        return array_flip($roles);
    }
	/**
	* Lists all User entities.
	*
	* @Route("/", name="control_user_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Admin Management", active=true, routeName="control_user_index", icon="glyphicon glyphicon-user", menuPosition=80)
	*/
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$users = $em->getRepository('PrototypeUserBundle:User')->getUsersWithoutDeveloperRole();

		return $this->render('PrototypeUserBundle:user:index.html.twig', array(
			'users' => $users,
		));
	}

    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="control_user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $user = new User();
        $form = $this->createForm(UserAdminCreateType, $user, array('roles' => $this->getAllRoles()) );
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()) {
                $data = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $password = $this->get('security.password_encoder')->encodePassword($user, $data->getPlainPassword());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success','Success - User created');
                return $this->redirectToRoute('control_user_index');
            }else{
                $this->addFlash('error','Error - User not saved');
            }
        }

        return $this->render('PrototypeUserBundle:user:new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="control_user_show")
     * @Method("GET")
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('PrototypeUserBundle:user:show.html.twig', array(
            'user' => $user
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="control_user_edit")
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm(UserAdminType, $user, array('roles' => $this->getAllRoles()) );
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash('success','Success - User updated');
                return $this->redirectToRoute('control_user_index');
            }else{
                $this->addFlash('error','User not saved (invalid)');
            }
        }

        return $this->render('PrototypeUserBundle:user:edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit-password", name="control_user_edit_password")
     * @Method({"GET", "POST"})
     */
    public function editPasswordAction(Request $request, User $user)
    {

        if($user->getId() !=  $this->getUser()->getId()){
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }

        $editForm = $this->createForm(UserAdminChangePasswordType, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()){
            if ($editForm->isValid()) {
                $data = $editForm->getData();
                $em = $this->getDoctrine()->getManager();
                $password = $this->get('security.password_encoder')->encodePassword($user, $data->getPlainPassword());
                $user->setEmailresetkey(null);
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success','Success - User Credentials updated');
                return $this->redirectToRoute('control_user_index');
            }else{
                $this->addFlash('error','Error - User not saved');
            }
        }

        return $this->render('PrototypeUserBundle:Control:admin-password.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="control_user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setDeleted(true);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success','Success - user deleted');
        }

        return $this->redirectToRoute('control_user_index');
    }

    /**
     * Creates a form to delete a User entity.
     *
     * @param User $user The User entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('control_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
