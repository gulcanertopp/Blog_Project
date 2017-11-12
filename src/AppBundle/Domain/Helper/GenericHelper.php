<?php


namespace AppBundle\Domain\Helper;


use Symfony\Component\DependencyInjection\ContainerInterface;

class GenericHelper extends \Twig_Extension
{

    private $_container;

    function __construct(ContainerInterface $container)
    {
        $this->_container = $container;

    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'generic';
    }

    public function getFunctions()
    {
        return array(
            'GetLoggedAdmin' => new \Twig_Function_Method($this , 'GetLoggedAdmin')
        );
    }

    public function GetLoggedAdmin()
    {
        return $this->_container->get('session')->get('name');
    }
}