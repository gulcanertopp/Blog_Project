<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class ExceptionListener
{
    private $_twigEnvironment;
    private $_logger;

    public function __construct(\Twig_Environment $twigEnvironment, Logger $logger)
    {
        $this->_twigEnvironment = $twigEnvironment;
        $this->_logger = $logger;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        $isAdminBundle          = strpos($event->getRequest()->getPathInfo(),'/admin/') !== false;


        $exception = $event->getException();
        $response = new Response();
        $logId = microtime(true);
        $templateName = null;
        $statusCode = null;
        $message = $exception->getMessage();


        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = $exception->getCode() !== 0 ? $exception->getCode() : 500;
        }



        if($statusCode == 401){
            if($isAdminBundle){
                $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/login'));
                return;
            }
        }
        return;
 
    }


}