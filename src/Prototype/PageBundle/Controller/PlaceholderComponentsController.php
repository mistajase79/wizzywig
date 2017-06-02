<?php

namespace Prototype\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Prototype\PageBundle\Annotation\ProtoCmsComponent;

/*

 I noticed frontend devs were creating static special blocks and leave them
 for a backend dev to wire up to dynamic content.
 I've created this is controller for them to put their HTML templates, making it
 easier for a dev to pickup at a later date

*/

class PlaceholderComponentsController extends Controller
{
// THIS IS AN EXAMPLE COMPONENT FOR A FRONTEND DEV TO DUPLICATE AND REPLACE AS NECESSARY
// NOTE: PLEASE ENSURE THE ANNOTATION PARAMETERS ARE UNIQUE
//
// ITS BEEN COMMENTED OUT TO STOP IT AUTOMATICALLY APPEARING WHEN CREATING/EDITING A PAGE

//     /**
//       * @Route("/test-placeholder/overview", name="embed_test_placeholder")
//       * @ProtoCmsComponent("Test Placeholder", active=true, routeName="embed_test_placeholder")
//       */
//      public function embedTestPlaceholderAction(){
//          return $this->render('@theme/placeholder/embedTestPlaceholder.html.twig');
//      }





}
