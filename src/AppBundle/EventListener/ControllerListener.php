<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\FileCacheReader;
use AppBundle\Data\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ControllerListener
{
    private $_userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->_userRepository   = $userRepository;
    }
    public function onHandleController(FilterControllerEvent $event)
    {
        $controllerBag = $event->getController();

        list($controller, $method) = $controllerBag;
        $className = ClassUtils::getClass($controller);


        list($bundle,$dummy,$controllerName) = explode('\\',$className);

        $restrictedBundles = array(
            'AdminBundle'
        );

        $allowedMethods = array(
            'LoginAction',
            'AjaxLoginAction',
            'LogoutAction',
        );

        // Controlling restricted bundles
        if(in_array($bundle,$restrictedBundles)){

            // Let it go if allowed method
            if(in_array($method,$allowedMethods))
                return;
            if($bundle == 'AdminBundle' && $controller->GetSession()->get('name') === null)
                throw new UnauthorizedHttpException('You must be logged in!');

        }


    }

}