<?php

namespace Prototype\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Prototype\UserBundle\Entity\User;
use Prototype\UserBundle\Entity\SpecialUser;

//use Prototype\UserBundle\Form\UserType;
//use Prototype\UserBundle\Form\SpecialUserType;
//use Prototype\UserBundle\Form\ProfileType;

const UserType = 'Prototype\UserBundle\Form\UserType';
const SpecialUserType = 'Prototype\UserBundle\Form\SpecialUserType';
const ProfileType = 'Prototype\UserBundle\Form\ProfileType';
const textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
const RepeatedType = 'Symfony\Component\Form\Extension\Core\Type\RepeatedType';
const PasswordType = 'Symfony\Component\Form\Extension\Core\Type\PasswordType';


class SecurityController extends Controller
{


	////////////////////////////////////
	// CMS USER PAGES
	/////////////////////////////////////
	/**
	 * @Route("/control/", name="control_login")
	 */
	public function loginAction(Request $request){
		$authenticationUtils = $this->get('security.authentication_utils');
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		if($error){
			$this->addFlash('error', 'Invalid Username or Password');
		}
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		return $this->render('PrototypeUserBundle:Control:login-control.html.twig', array(
			'last_username' => $lastUsername
		));
	}

	/**
	 * @Route("/forgot-cms-password", name="forgot_cms_password")
	 */
	public function forgotCMSPasswordAction(Request $request){

		$error = null;
		$data = array();
   		$form = $this->createFormBuilder($data)
				->add('email', textType, array('label'=>'Email Address', 'attr'=> array('placeholder'=>'Email')))
				->getForm();

		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {
			$data = $form->getData();
			$em = $this->getDoctrine()->getManager();
			$user = $em->getRepository('PrototypeUserBundle:User')->findOneByEmail($data['email']);
			if(!$user){ $this->addFlash('error','Email Address not found'); }
			if($user){
				$url_encrypt = substr(md5(rand(0,999).'5h0rtt3rmm3m0ry1055?'.rand(0,999)),0,20);
				$user->setEmailresetkey($url_encrypt);
				$em->persist($user);
				$em->flush();

				$message = \Swift_Message::newInstance()
					->setSubject('CMS Password reset request for '. $this->container->getParameter('sitename'))
					->setFrom($this->container->getParameter('email_norely'))
					->setTo($user->getEmail())
					->setBody(
						$this->renderView('PrototypeUserBundle:Control:email-password-reset.html.twig', array(
							'url_encrypt' => $url_encrypt,
							'user' => $user
						)
					),'text/html');

				//	$this->get('mailer')->send($message);

				$this->addFlash('success','An email has been sent with instructions to reset your password '.$url_encrypt);
			}
			//return $this->redirect('/');
		}
		return $this->render('PrototypeUserBundle:Control:admin-forgot.html.twig', array(
			'form' => $form->createView(),
			'error' => $error
		));
	}

	/**
	 * @Route("/password-cms-reset/{url_encrypt}", name="password_cms_reset")
	 */
	public function passwordCMSResetAction(Request $request, $url_encrypt){

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('PrototypeUserBundle:User')->findOneByEmailresetkey($url_encrypt);
		if(!$user){
			$this->addFlash('error','Email reset key not valid or has expired - please try to reset your password again.');
			return $this->redirectToRoute('forgot_password');
		}
		if($user){
			$error = null;
			$data = array();
	   		$form = $this->createFormBuilder($data)
			->add('plainPassword', RepeatedType, array(
				'type' => PasswordType,
				'invalid_message' => 'The password fields must match.',
				'first_options'  => array('label' => 'Password'),
				'second_options' => array('label' => 'Repeat Password'),
			))
			->getForm();

			$form->handleRequest($request);
			if ($form->isValid() && $form->isSubmitted()) {
				$data = $form->getData();
				$em = $this->getDoctrine()->getManager();
				$password = $this->get('security.password_encoder')
					->encodePassword($user, $data['plainPassword']);
				$user->setEmailresetkey(null);
				$user->setPassword($password);
				$em->persist($user);
				$em->flush();
				$this->addFlash('success','Your password has been reset');
				return $this->redirectToRoute('control_login');
			}

			return $this->render('PrototypeUserBundle:Control:admin-reset.html.twig', array(
				'form' => $form->createView(),
				'error' => $error,
				'url_encrypt' => $url_encrypt
			));
		}


	}

	/**
	 * @Route("/control/user/create", name="admin_create")
	 */
	public function adminCreateAction(Request $request){

		$user = new User();
		$form = $this->createForm(UserType , $user);

		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {
			$password = $this->get('security.password_encoder')
				->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);
			$user->setRoles(array(
				'ROLE_ADMIN'
			));
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			$this->addFlash('success','User Created Successfully');
			return $this->redirectToRoute('control_dash');
		}
		return $this->render('PrototypeUserBundle:Control:admin-create.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/control/user/profile", name="control_profile")
	 */
	public function userProfileAction(Request $request){
		// 1) build the form
		$user = $this->getUser();
		$form = $this->createForm(ProfileType, $user);

		// 2) handle the submit (will only happen on POST)
		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {

			$user->resizeImage($this->get('image.handling'));

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			$this->addFlash('success','Profile Updated Successfully');
			return $this->redirectToRoute('control_profile');
		}
		return $this->render('PrototypeUserBundle:Control:admin-profile.html.twig', array(
			'form' => $form->createView(),
            'user' => $user
		));
	}

	/**
	 * @Route("/control/admin_check", name="control_check")
	 */
	public function adminCheckAction(){ } // route is handled by the Security system


	////////////////////////////////////
	// MEMBER PAGES
	/////////////////////////////////////
	/**
	 * @Route("/members", name="member_login")
	 */
	public function loginMemberAction(Request $request){
		$authenticationUtils = $this->get('security.authentication_utils');
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		return $this->render('@theme/members/members-login.html.twig', array(
			'last_username' => $lastUsername,
			'error' => $error,
		));
	}

	/**
	 * @Route("/members/member_check", name="member_check")
	 */
	public function memberCheckAction(){ } // route is handled by the Security system

	/**
	 * @Route("/member-register", name="member_create")
	 */
	public function memberCreateAction(Request $request){

		$user = new User();
		$form = $this->createForm(UserType, $user);

		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {
			$password = $this->get('security.password_encoder')
				->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);
			$user->setRoles(array('ROLE_USER'));

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			$this->addFlash('success','User Created Successfully');
			return $this->redirect('/');
		}
		return $this->render('@theme/members/members-register.html.twig', array(
			'form' => $form->createView()
		));
	}


	/**
	 * @Route("/forgot-password", name="forgot_password")
	 */
	public function forgotPasswordAction(Request $request){

		$error = null;
		$data = array();
   		$form = $this->createFormBuilder($data)
				->add('email', textType, array('label'=>'Email Address', 'attr'=> array('placeholder'=>'Email')))
				->getForm();

		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {
			$data = $form->getData();
			$em = $this->getDoctrine()->getManager();
			$user = $em->getRepository('PrototypeUserBundle:User')->findOneByEmail($data['email']);
			if(!$user){ $this->addFlash('error','Email Address not found'); }
			if($user){
				$url_encrypt = substr(md5(rand(0,999).'5h0rtt3rmm3m0ry1055?'.rand(0,999)),0,20);
				$user->setEmailresetkey($url_encrypt);
				$em->persist($user);
				$em->flush();

				$message = \Swift_Message::newInstance()
					->setSubject('Password reset request for '. $this->container->getParameter('sitename'))
					->setFrom($this->container->getParameter('email_norely'))
					->setTo($user->getEmail())
					->setBody(
						$this->renderView('@theme/emails/forgot-password.html.twig', array(
							'url_encrypt' => $url_encrypt,
							'user' => $user
						)
					),'text/html');

					$this->get('mailer')->send($message);

				$this->addFlash('success','An email has been sent with instructions to reset your password');
			}
			//return $this->redirect('/');
		}
		return $this->render('@theme/members/members-forgot.html.twig', array(
			'form' => $form->createView(),
			'error' => $error
		));
	}

	/**
	 * @Route("/password-reset/{url_encrypt}", name="password_reset")
	 */
	public function passwordResetAction(Request $request, $url_encrypt){

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('PrototypeUserBundle:User')->findOneByEmailresetkey($url_encrypt);
		if(!$user){
			$this->addFlash('error','Email reset key not valid or has expired - please try to reset your password again.');
			return $this->redirectToRoute('forgot_password');
		}
		if($user){
			$error = null;
			$data = array();
	   		$form = $this->createFormBuilder($data)
			->add('plainPassword', RepeatedType, array(
				'type' => PasswordType,
				'invalid_message' => 'The password fields must match.',
				'first_options'  => array('label' => 'Password'),
				'second_options' => array('label' => 'Repeat Password'),
			))
			->getForm();

			$form->handleRequest($request);
			if ($form->isValid() && $form->isSubmitted()) {
				$data = $form->getData();
				$em = $this->getDoctrine()->getManager();
				$password = $this->get('security.password_encoder')
					->encodePassword($user, $data['plainPassword']);
				$user->setEmailresetkey(null);
				$user->setPassword($password);
				$em->persist($user);
				$em->flush();
				$this->addFlash('success','Your password has been reset');
				return $this->redirectToRoute('member_login');
			}

			return $this->render('@theme/members/members-reset.html.twig', array(
				'form' => $form->createView(),
				'error' => $error,
				'url_encrypt' => $url_encrypt
			));
		}


	}

	/**
	 * @Route("/member/user/profile", name="member_profile")
	 */
	public function memberProfileAction(Request $request){
		// 1) build the form
		$user = $this->getUser();
		$form = $this->createForm(ProfileType, $user);

		// 2) handle the submit (will only happen on POST)
		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			$this->addFlash('success','Profile Updated Successfully');
			return $this->redirectToRoute('control_dash');
		}
		return $this->render('@theme/members/members-profile.html.twig', array(
			'form' => $form->createView()
		));
	}






	///////////////////////////////////////////
	// SECONDARY USER (MEMBER) PAGES : Seperated from admin and member users!
	// Uses a completly different firewall setup
	// Only used in very few circumstances - uses a seperate user table.
	// I took the liberty to setup multiple security providers just in case we
	// ever needed them - i think theres only been a couple of
	// instances where this feature could of been used.
	// THIS IS NOT FOR CMS USERS SO DONT MIX THEM WITH ADMIN OR MEMBER USERS!
	//
	// This should only be used for seperate public access to a site,
	// i.e comments section profiles, register-to-access content or shop customers
	// USE ONLY FOR NON CMS USERS - (due to foreign key constraints)
	//////////////////////////////////////////
	/**
	 * @Route("/special-user-login", name="special_login")
	 */
	public function loginSpecialUserAction(Request $request){

		$authenticationUtils = $this->get('security.authentication_utils');
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('@theme/security/special-login.html.twig', array(
			'last_username' => $lastUsername,
			'error' => $error,
		));
	}

	/**
	 * @Route("/special-user-register", name="special_registration")
	 */
	public function registerSpecialAction(Request $request){
		// 1) build the form
		$user = new SpecialUser();
		$form = $this->createForm(SpecialUserType , $user);

		// 2) handle the submit (will only happen on POST)
		$form->handleRequest($request);
		if ($form->isValid() && $form->isSubmitted()) {
			// 3) Encode the password (you could also do this via Doctrine listener)
			$password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);
			$user->setRoles(array(
				'ROLE_SPECIAL'
			));
			// 4) save the User!
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			$this->addFlash('success','User Created Successfully');
			return $this->redirectToRoute('home');
		}
		return $this->render('@theme/security/special-register.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/special-user/special-check", name="special_login_check")
	 */
	public function loginCheckAction(){ }// route is handled by the Security system

	/**
	 * @Route("/logout", name="logout")
	 */
	public function logoutAction(){ } // route is handled by the Security system

}
