<?php

namespace AppBundle\Controller;

use AppBundle\Data\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DetailController extends Controller
{
    /**
     * @Route("/detail", name="index_app_detail")
     * @Template("AppBundle:Default:detail.html.twig")
     */
    public function indexAction(Request $request)

    {
        return array();
    }
}
