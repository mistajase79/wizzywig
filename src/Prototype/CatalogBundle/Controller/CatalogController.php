<?php

namespace Prototype\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\AdminBundle\Annotation\ProtoCmsAdminDash;


/**
 * Catalog controller.
 *
 * @Route("/control/catalog")
 */
class CatalogController extends Controller
{

    /**
	* Simple Parent Route for admin side-bar
	*
	* @Route("/", name="control_catalog_index")
	* @Method("GET")
	* @ProtoCmsAdminDash("Catalog", active=true, routeName="control_catalog_index", role="ROLE_CATALOG_EDITOR", icon="fa fa-shopping-cart", dontLink=true, menuPosition=60)
	*/
	public function indexAction(){ return false; }
}
