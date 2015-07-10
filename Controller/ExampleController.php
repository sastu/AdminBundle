<?php

namespace Optisoop\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class ExampleController extends Controller
{
    
    /**
     * @Route("/dashboard2")
     * @Template("AdminBundle:Example/dashboard:dashboard2.html.twig")
     */
    public function dashboard2Action()
    {
         return array();
    }
    
    /**
     * @Route("/layout")
     */
    public function layoutAction(Request $request)
    {
              
        if($request->get('top-nav')){
            return $this->render("AdminBundle:Example/layout:top-nav.html.twig",array());
        } elseif($request->get('boxed')) {
            return $this->render("AdminBundle:Example/layout:boxed.html.twig",array());
        }elseif($request->get('fixed')) {
            return $this->render("AdminBundle:Example/layout:fixed.html.twig",array());
        }elseif($request->get('collapsed')) {
            return $this->render("AdminBundle:Example/layout:collapsed.html.twig",array());
        }
    }
    
    /**
     * @Route("/widget")
     * @Template()
     */
    public function widgetAction()
    {
         return array();
    }
    
    /**
     * @Route("/chart")
     * @Template()
     */
    public function chartAction(Request $request)
    {
         if($request->get('chartjs')){
            return $this->render("AdminBundle:Example/chart:chartjs.html.twig",array());
        } elseif($request->get('morris')) {
            return $this->render("AdminBundle:Example/chart:morris.html.twig",array());
        }elseif($request->get('flot')) {
            return $this->render("AdminBundle:Example/chart:flot.html.twig",array());
        }elseif($request->get('inline')) {
            return $this->render("AdminBundle:Example/chart:inline.html.twig",array());
        }
    }
    
    /**
     * @Route("/ui")
     * @Template()
     */
    public function uiAction(Request $request)
    {
         if($request->get('general')){
            return $this->render("AdminBundle:Example/ui:general.html.twig",array());
        } elseif($request->get('icons')) {
            return $this->render("AdminBundle:Example/ui:icons.html.twig",array());
        }elseif($request->get('buttons')) {
            return $this->render("AdminBundle:Example/ui:buttons.html.twig",array());
        }elseif($request->get('sliders')) {
            return $this->render("AdminBundle:Example/ui:sliders.html.twig",array());
        }elseif($request->get('timeline')) {
            return $this->render("AdminBundle:Example/ui:timeline.html.twig",array());
        }elseif($request->get('modals')) {
            return $this->render("AdminBundle:Example/ui:modals.html.twig",array());
        }
    }
    
    /**
     * @Route("/form")
     * @Template()
     */
    public function formAction(Request $request)
    {
         if($request->get('general')){
            return $this->render("AdminBundle:Example/form:general.html.twig",array());
        } elseif($request->get('advance')) {
            return $this->render("AdminBundle:Example/form:advance.html.twig",array());
        }elseif($request->get('editors')) {
            return $this->render("AdminBundle:Example/form:editors.html.twig",array());
        }
    }
    
    /**
     * @Route("/table")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        if($request->get('simple')){
            return $this->render("AdminBundle:Example/table:simple.html.twig",array());
        } elseif($request->get('datatable')) {
            return $this->render("AdminBundle:Example/table:datatable.html.twig",array());
        }
    }
    
    /**
     * @Route("/calendar")
     * @Template()
     */
    public function calendarAction()
    {
         return array();
    }
    
    /**
     * @Route("/mailbox")
     * @Template()
     */
    public function mailboxAction(Request $request)
    {
         if($request->get('inbox')){
            return $this->render("AdminBundle:Example/mailbox:inbox.html.twig",array());
        } elseif($request->get('compose')) {
            return $this->render("AdminBundle:Example/mailbox:compose.html.twig",array());
        } elseif($request->get('read')) {
            return $this->render("AdminBundle:Example/mailbox:read.html.twig",array());
        }
    }
    
    /**
     * @Route("/example")
     * @Template()
     */
    public function exampleAction(Request $request)
    {
        if($request->get('invoice')){
            return $this->render("AdminBundle:Example/example:invoice.html.twig",array());
        } elseif($request->get('invoice-print')){
            return $this->render("AdminBundle:Example/example:invoice.print.html.twig",array());
        } elseif($request->get('login')) {
            return $this->render("AdminBundle:Example/example:login.html.twig",array());
        } elseif($request->get('register')) {
            return $this->render("AdminBundle:Example/example:register.html.twig",array());
        } elseif($request->get('lockscreen')) {
            return $this->render("AdminBundle:Example/example:lockscreen.html.twig",array());
        } elseif($request->get('404')) {
            return $this->render("AdminBundle:Example/example:404.html.twig",array());
        } elseif($request->get('500')) {
            return $this->render("AdminBundle:Example/example:500.html.twig",array());
        }  elseif($request->get('blank')) {
            return $this->render("AdminBundle:Example/example:blank.html.twig",array());
        }
    }
    
    /**
     * @Route("/documentation")
     * @Template()
     */
    public function documentationAction()
    {
         return array();
    }
}
