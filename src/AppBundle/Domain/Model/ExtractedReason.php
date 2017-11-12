<?php

namespace AppBundle\Domain\Model;


class ExtractedReason
{
    public $key;
    /**
     * @var array
     */
    public $values;
    /**
     * @var array
     */
    public $translate;

    function __construct($key, $values=array(), $translate=array())
    {
        $this->key = $key;
        $this->values = $values;
        $this->translate = $translate;
    }
}