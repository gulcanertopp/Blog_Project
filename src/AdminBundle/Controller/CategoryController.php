<?php


namespace AdminBundle\Controller;
use AppBundle\Data\Model\Category;
use AppBundle\Data\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route(service="admin.category_controller")
 */
class CategoryController extends BaseController
{ 
    private $_categoryRepository;

    function __construct(CategoryRepository $categoryRepository)
    {
        $this->_categoryRepository = $categoryRepository;

    }

    /**
     * @Route("/category/save",name="index_admin_save_category")
     * @Template("AdminBundle:Category:add.html.twig")
     */
    public function CategoryAddAction(Request $request)
    {
        $id = $request->get('ID');
        if($id == null)
            return  array(
                'category'=>false
            );

        return array(
            'category' => $this->_categoryRepository->GetCategoryById($id)
        );
    }

    /**
     * @Route("/category/list/",name="index_admin_list_category")
     * @Template("AdminBundle:Category:list.html.twig")
     * @param Request $request
     * @return array
     */
    public function CategoryListAction(Request $request)
    {
        $limit = intval($request->get('limit',5));
        $page  = intval($request->get('page',0));
        $serachKey = $request->get('serachKey',null);
        return  array(
            'categories' => $this->_categoryRepository->GetCategory($page*$limit,$limit,$serachKey)
        );
    }

    /**
     * @Route("/category/get/",name="index_admin_get_category")
     * @Template("AdminBundle:Category:get.html.twig")
     * @param Request $request
     * @return array
     */
    public function CategoryGetAction(Request $request)
    {
        $categoryId = intval($request->get('categoryId',0));
            var_dump($this->_categoryRepository->GetCategoryById($categoryId));exit();
        return  array(
            'category' => $this->_categoryRepository->GetCategoryById($categoryId)
        );
    }
    /**
     * @Route("/category/delete/",name="index_admin_delete_category")
     * @param Request $request
     * @return array
     */
    public function CategoryDeleteAction(Request $request)
    {
        $categoryId = $request->get('categoryId'); 

        $deleteCat = $this->_categoryRepository->DeleteCategory($categoryId);
        var_dump($deleteCat);exit();
        if($deleteCat == false)
            return new JsonResponse(array(
                'success' => false
            ));

        return new JsonResponse(array(
            'success' => true
        ));
    }
    /**
     * @Route("/ajax/category/save",name="ajax_cat_save")
     */
    public function AjaxSaveCategoryAction(Request $request)
    {

        $data = $request->request->all();

        $category = (new Category())->MapFrom($data);

       
        $saveCat = $this->_categoryRepository->SaveCategory($category);

        if($saveCat == false)
        {
            return new JsonResponse(array(
                'success' => true
            ));
        }

        return new JsonResponse(array(
            'success' => true
        ));
    }
    /**
     * @Route("/ajax/category/search",name="ajax_select2_search")
     */
    public function AjaxSearchCategoryAction(Request $request)
    {
        $key = $request->get('q');
        $results = $this->_categoryRepository->GetKeyCategory($key);
        $serach = array();
        foreach ($results as $result)
        {
            $serach[] = array('id'=>$result->CategoryId,'text'=>$result->CategoryName);
        }

        return new JsonResponse(array(
            'results' => $serach
        ));
    }


}