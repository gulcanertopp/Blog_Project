<?php
namespace AppBundle\Data\Model;


class Post
{
    public $ID;
    public $Title;
    public $Description;
    public $ImageUrl;
    public $UserId;
    public $BeginDate;
    public $EndDate;
    public $Slug;

    /**
     * @var int[]
     */
    public $Categories;

    public function MapFrom(array $data)
    {
        $this->ID = $data["ID"];
        $this->Title = $data["title"];
        $this->Description = $data["description"];
        $this->ImageUrl = $data["image_url"];
        $this->UserId = $data["user_id"];
        $this->BeginDate = $data["begin_date"];
        $this->EndDate = $data["end_date"];
        $this->Slug = $data['slug'];

        $this->Categories = array();
        if(array_key_exists('categories',$data))
        {

            foreach ($data['categories'] as $row)
            {
                $this->Categories[] = (new Category())->MapFrom($row);
            }
        }

        return $this;
    }


}