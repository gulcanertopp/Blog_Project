<?php
namespace AppBundle\Data\Repository;

use AppBundle\Data\Interfaces\ILoggerDependent;
use Doctrine\DBAL\Connection;
use Monolog\Logger;

class BaseRepository implements ILoggerDependent
{
    /**
     * Software
     *
     * @var \Monolog\Logger
     */
    protected $_logger;

    private $_connection;

    public function Initialize(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @param Logger $logger
     */
    public function SetLogger(Logger $logger)
    {
        $this->_logger = $logger;

    }
 
}