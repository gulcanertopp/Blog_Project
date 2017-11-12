<?php

namespace AdminBundle\Controller;
 
use AppBundle\Data\Repository\UserRepository;
use AppBundle\Domain\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAware;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route(service="admin.default_controller")
 */
class DefaultController extends BaseController
{
    private $_userRepository;

    function __construct(UserRepository $userRepository)
    {
        $this->_userRepository = $userRepository;

    }

    /**
     * @Route("/",name="admin")
     * @Template("AdminBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        return  array(
            "users" => $this->_userRepository->GetUser()
            
        );
    }
    /**
     *@Route("/login",name="login")
     *@Template("AdminBundle:Default:login.html.twig")
     */
    public function LoginAction()
    {

        return array();
    }

    /**
     *@Route("/ajax/login",name="ajax_login")
     */
    public function AjaxLoginAction(Request $request)
    {
        $name = $request->request->get('username');
        $password = $request->request->get('password');
        $users = $this->_userRepository->LoginUser($name,$password);

        if($users == false)
            return new JsonResponse (array(
                'success' => false
            ));


        $this->GetSession()->set('id',$users->UserId);
        $this->GetSession()->set('name',$users->UserName);

        return new JsonResponse (array(
            'success' => true
        ));

    }


    /**
     * @Route("/logout",name="logout")
     */
    public function LogoutAction()
    {
        $this->GetSession()->clear();
        return $this->redirect('/admin');

    }



    /**
     * Media Easy Save Action
     * @Route("/media/save/easy/", name="admin_media_save_easy")
     */
    public function MediaEasySaveAction(Request $request)
    {
        $files = $request->files->get('file');
        $size  = floatval($request->get('s',-1));
        $urls = array();
        /** @var UploadedFile $file */
        foreach($files as &$file){
            $mime = explode("/",$file->getMimeType())[0];

            if($mime != "image"){
                return new JsonResponse(
                    array(
                        'success' => false
                    ));
            }

            if($size != -1 && ($file->getClientSize() / 1048576) > $size){
                return new JsonResponse(
                    array(
                        'success' => false,
                        'message' => str_replace('{size}',$size ,$this->TranslateItem('MAX_{size}_MB_ALLOWED'))
                    ));
            }

            $fileUploadResult = Utils::Upload($file);
            if ($fileUploadResult['code'] !== 200) {
                return new JsonResponse(
                    array(
                        'success' => false

                    ));
            }

            $urls[] = $fileUploadResult['path'];
        }
        return new JsonResponse(
            array(
                'success' => count($urls) > 0,
                'urls'    => $urls
            ));
    }
    /**
     * @Route("/crawl/{os}/{id}",name="home")
     */
    public function crawleAction($os,$id)
    {
        return array( 'data'=> $this->_userRepository->GetUser());
        try{
            $c = curl_init('http://appsrabbit.com/'.$os.'/app/'.$id);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            $html = curl_exec($c);
            if(curl_errno($c))
                return new JsonResponse(array(
                    'success'=>false
                ));
            $crawler = new Crawler($html);
            $data = array(
                'logo'=>$crawler->filter('.app-details div.colimg img')->attr('src'),
                'slides'=>$crawler->filter('.screenshots div a.slider-image')->each(function (Crawler $node) {
                    return $node->filter('img')->attr('data-img-large');
                })
            );
            return  new JsonResponse(array(
                'success'=>true,
                'data'=>$data
            ));
        }catch (\Exception $e){
            return new JsonResponse(array(
                'success'=>false
            ));
        }
    }


}
