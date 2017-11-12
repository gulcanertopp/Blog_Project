<?php

namespace AppBundle\Controller;

use AppBundle\Data\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="app.post_controller")
 */
class PostController extends BaseController
{

    private $_postRepository;

    function __construct(PostRepository $postRepository)
    {
        $this->_postRepository = $postRepository;
    }


    /**
     * @Route("/post/list/",name="index_app_list_post")
     */
    public function ListPostAction(Request $request)
    {
        $limit = intval($request->get('limit',3));
        $page = intval($request->get('page',0));

        $serachKey = $request->get('serachKey',null); 
        return new JsonResponse(array(
            'post' =>$this->_postRepository->GetPost($page*$limit,$limit,$serachKey)
        ));
    }
    /**
     * @Route("/post/category/list/",name="index_app_category_list_post")
     */
    public function ListCategoryPostAction(Request $request)
    {
        $categories = $request->get('categories'); 
        return new JsonResponse(array(
            'posts' =>$this->_postRepository->GetPostByDate($categories)
        ));
    }

    /**
     * @Route("/post/get/{title}",name="index_app_get_post")
     */
    public function GetPostAction($title)
    {
        return new JsonResponse(array(
           'post' =>$this->_postRepository->GetPostByTitle($title)
        ));
    }


    /**
     * @Route("/post/list/{category}",name="index_app_list_post_by_category")
     */
    public function ListPostActionByCategory(Request $request,$category)
    {
        $limit = intval($request->get('limit',3));
        $page = intval($request->get('page',0));

        $serachKey = $request->get('serachKey',null);
        
        

        return new JsonResponse(array(
            'post' =>$this->_postRepository->GetPostByCategory($category,$page*$limit,$limit,$serachKey)
        ));
    }

}