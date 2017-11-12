<?php
namespace AppBundle\Data\Model;

class User
{

    public $UserId;
    public $UserName;
    public $Name;
    public $SurName;
    public $Password;

    public function MapFrom(array $data)
    {
        $this->UserId   = $data['ID'];
        $this->UserName = $data['user_name'];
        $this->Name     = $data['name'];
        $this->SurName  = $data['surname'];
        $this->Password = $data['password'];
        return $this;
    }
}