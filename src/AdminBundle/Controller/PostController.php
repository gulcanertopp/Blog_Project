<?php


namespace AdminBundle\Controller;
use AppBundle\Data\Model\Post;
use AppBundle\Data\Repository\CategoryRepository;
use AppBundle\Data\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route(service="admin.post_controller")
 */
class PostController extends BaseController
{

    private $_postRepository;

    function __construct(PostRepository $postRepository)
    {
        $this->_postRepository = $postRepository;

    }

    /**
     * @Route("/post/save/",name="index_admin_add_post")
     * @Template("AdminBundle:Post:add.html.twig")
     */
    public function AddPostAction(Request $request)
    {
        $id = $request->get("ID");

        if ($id == null)
            return array(
                'posts'=> null
            );

        return  array(
            'posts' => $this->_postRepository->GetPostById($id)
        );
    }

    /**
     * @Route("/post/list/",name="index_admin_list_post")
     * @Template("AdminBundle:Post:list.html.twig")
     */
    public function ListPostAction(Request $request)
    {
        $limit = intval($request->get('limit',3));
        $page = intval($request->get('page',0));

        $serachKey = $request->get('serachKey',null);
        return  array(
            'posts' =>$this->_postRepository->GetPost($page*$limit,$limit,$serachKey)
        );
    }

    /**
     * @Route("/post/get/{title}",name="index_admin_get_post")
     * @Template("AdminBundle:Post:get.html.twig")
     */
    public function GetPostAction($title)
    {
        return  array(
            'post' =>$this->_postRepository->GetPostByTitle($title)
        );
    }


    /**
     * @Route("/ajax/blog/save/" , name="ajax_blog_save")
     */
    public function AjaxSaveBlogAction(Request $request)
    {
        $data = $request->request->all();
       
        $post = (new  Post())->MapFrom($data);
        $savePost =  $this->_postRepository->SavePost($post);
        if ($savePost == false)
        {
        return new JsonResponse(array(
           'success'=>false
        ));
        }
        
        return new JsonResponse(array(
            'success'=>true
        ));

    }


}