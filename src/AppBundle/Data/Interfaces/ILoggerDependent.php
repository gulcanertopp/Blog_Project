<?php 

namespace AppBundle\Data\Interfaces;


use Monolog\Logger;

interface ILoggerDependent
{
    public function SetLogger(Logger $logger);

}