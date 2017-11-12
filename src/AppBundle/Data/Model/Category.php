<?php
namespace AppBundle\Data\Model;


class Category
{

    public $CategoryId;
    public $CategoryName;


    public function MapFrom(array $data)
    { 
        $this->CategoryId = $data['ID'];
        $this->CategoryName =   $data['category_name'];
        return $this;

    }

}