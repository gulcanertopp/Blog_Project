<?php

namespace AppBundle\Domain\Model;


class PagedList
{
    public $Content;
    public $ListSize;

    public function __construct($content = null, $listSize = null)
    {
        $this->Content = $content;
        $this->ListSize = $listSize;
    }
}