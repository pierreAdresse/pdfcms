<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/", name="topicalities")
     */
    public function topicalitiesAction(Request $request)
    {
        return $this->render('main/topicalities.html.twig');
    }

    /**
     * @Route("/informations", name="informations")
     */
    public function informationsAction(Request $request)
    {
        return $this->render('main/informations.html.twig');
    }

    /**
     * @Route("/demande-de-visite", name="requestForVisit")
     */
    public function requestForVisitAction(Request $request)
    {
        return $this->render('main/requestForVisit.html.twig');
    }

    /**
     * @Route("/mon-compte", name="myAccount")
     */
    public function accountAction(Request $request)
    {
        return $this->render('main/account.html.twig');
    }

    /**
     * @Route("/admin/parametrage", name="setting")
     */
    public function settingAction(Request $request)
    {
        return $this->render('admin/setting.html.twig');
    }
}
