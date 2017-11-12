<?php

namespace AppBundle\Controller;

use AppBundle\Data\Repository\CategoryRepository;
use AppBundle\Data\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="app.default_controller")
 */
class DefaultController extends BaseController
{

    private $_postRepository;

    private $_categoryRepository;

    function __construct(PostRepository $postRepository,CategoryRepository $categoryRepository)
    {
        $this->_postRepository = $postRepository;
        $this->_categoryRepository = $categoryRepository;
    }

    /**
      * @Route("/", name="homepage")
      * @Template("AppBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $posts = $this->_postRepository->GetPostByDate();
         
        $categories= $this->_categoryRepository->GetAllCategory();

       return array(
           'posts'=>$posts,
           'categories'=>$categories
       );
    }
}
