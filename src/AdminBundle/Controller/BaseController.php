<?php


namespace AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class BaseController extends Controller implements ContainerAwareInterface
{
    /**
     * @var Session
     */
    private $_session;

    public function Initialize(Session $session)
    {
        $this->_session = $session;
    }

    public function GetSession()
    {
        if(!$this->_session->isStarted())
        {
            $this->_session->start();
        }
        return $this->_session;
    }


  

}