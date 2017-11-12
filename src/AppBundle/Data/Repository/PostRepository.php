<?php

namespace AppBundle\Data\Repository;



use AppBundle\Data\Model\Post;
use AppBundle\Domain\Model\PagedList;
use Symfony\Component\Config\Definition\Exception\Exception;

class PostRepository extends BaseRepository
{

    /**
     * @param int $offset
     * @param int $limit
     * @param null $searchKey
     * @return PagedList|bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function GetPost($offset = 0, $limit=5, $searchKey = null)
    {
        try{
        $countQuery='SELECT COUNT(*) as row_count 
                FROM post p';
        if($searchKey !== null)
        {
            $countQuery .=' WHERE p.title
                        LIKE "%'.$searchKey.'%"';
        }
        $countResult = $this->getConnection()->prepare($countQuery);
        $countResult->execute();
        $countResult = $countResult->fetch();

        if($countResult === null || (int)$countResult['row_count'] == 0)
            return new PagedList(null,0);

        $query= 'SELECT
                    p.ID,
                    p.title,
                    p.description,
                    p.image_url,
                    p.user_id,
                    p.begin_date,
                    p.end_date,
                    p.slug
                 FROM post p';

        if($searchKey !== null)
        {
            $query .=' WHERE p.title
                        LIKE "%'.$searchKey.'%"';
        }
        $query .=' LIMIT '.$offset.','.$limit;

        $result = $this->getConnection()->prepare($query);
        $result->execute();
        $results = $result->fetchAll();

        if($results == false)
            return new PagedList(null, 0);

        $catQuery='SELECT  
                      c.ID,
                      c.category_name 
                   FROM post p 
                   INNER JOIN post_category pg on pg.postID = p.ID
                   INNER JOIN category c on pg.categoryID = c.ID 
                   WHERE p.ID =:postId';


        $resultCat = $this->getConnection()->prepare($catQuery);

        $posts= array();

        foreach ($results as $result)
        {
            $resultCat->execute(array(
                'postId'=>$result["ID"]
            ));

            $result['categories']=$resultCat->fetchAll();

            $posts[] = (new Post())->MapFrom($result);
        }

        $list = new PagedList($posts,(int)$countResult['row_count']);
 
        return $list;
        }catch (Exception $e)
        {
            $this->_logger->addError("PostRepository->GetPost failed: " . $e->getMessage());
            return false;
        }
    }

    public function GetPostByDate($categories = null)
    {
        try{
            $nowDate = date("Y-m-d H:i:s");

            $query= 'SELECT
                    p.ID,
                    p.title,
                    p.description,
                    p.image_url,
                    p.user_id,
                    p.begin_date,
                    p.end_date,
                    p.slug 
                    FROM post p 
                    INNER JOIN post_category pc on p.ID = pc.postID 
                    WHERE p.begin_date <= "'.$nowDate.'" AND p.end_date >= "'.$nowDate.'"';

            if($categories != null)
            {
                $query .= 'AND';
                $i=0;
                $len = count($categories);
                foreach ($categories as $category) {
                    if ($i == $len - 1)
                    {
                        $query .= ' pc.categoryID =' . $category["ID"];

                    } else
                    {
                        $query .=' pc.categoryID ='.$category["ID"].' or ';
                    }
                    $i++;
                }
            }
            $query .= " GROUP BY(p.ID)";
            $result = $this->getConnection()->prepare($query);
            $result->execute();
            $results = $result->fetchAll();
           ;
            if($results == false)
                return false;

            $catQuery='SELECT  
                      c.ID,
                      c.category_name 
                   FROM post p 
                   INNER JOIN post_category pg on pg.postID = p.ID
                   INNER JOIN category c on pg.categoryID = c.ID 
                   WHERE p.ID =:postId';


            $resultCat = $this->getConnection()->prepare($catQuery);

            $posts= array();

            foreach ($results as $result)
            {
                $resultCat->execute(array(
                    'postId'=>$result["ID"]
                ));

                $result['categories']=$resultCat->fetchAll();

                $posts[] = (new Post())->MapFrom($result);
            }



            return $posts;
        }catch (Exception $e)
        {
            $this->_logger->addError("PostRepository->GetPostByDAte failed: " . $e->getMessage());
            return false;
        }
    }
    public function GetPostByCategory($category,$offset = 0, $limit=5)
    {
        try{
            $countQuery='SELECT COUNT(*) as row_count 
                FROM post p 
                INNER JOIN post_category pc on p.ID = pc.postID
                WHERE pc.categoryID = '.$category;

            $countResult = $this->getConnection()->prepare($countQuery);
            $countResult->execute();
            $countResult = $countResult->fetch();

            if($countResult === null || (int)$countResult['row_count'] == 0)
                return new PagedList(null,0);

            $query= 'SELECT
                    p.ID,
                    p.title,
                    p.description,
                    p.image_url,
                    p.user_id,
                    p.begin_date,
                    p.end_date,
                    p.slug
                 FROM post p
                 INNER JOIN post_category pc on p.ID = pc.postID
                 WHERE pc.categoryID = '.$category;


            $query .=' LIMIT '.$offset.','.$limit;

            $result = $this->getConnection()->prepare($query);
            $result->execute();
            $results = $result->fetchAll();

            if($results == false)
                return new PagedList(null, 0);

            $catQuery='SELECT  
                      c.ID,
                      c.category_name 
                   FROM post p 
                   INNER JOIN post_category pg on pg.postID = p.ID
                   INNER JOIN category c on pg.categoryID = c.ID 
                   WHERE p.ID =:postId';


            if ($results === false)
                return false;
            $resultCat = $this->getConnection()->prepare($catQuery);

            $posts= array();

            foreach ($results as $result)
            {
                $resultCat->execute(array(
                    'postId'=>$result["ID"]
                ));

                $result['categories']=$resultCat->fetchAll();

                $posts[] = (new Post())->MapFrom($result);
            }

            $list = new PagedList($posts,(int)$countResult['row_count']);

            return $list;
        }catch (Exception $e)
        {
            $this->_logger->addError("PostRepository->GetPostByCategory failed: " . $e->getMessage());
            return false;
        }
    }


    public function GetPostByTitle($title)
    {
        try{

        $query= 'SELECT
                    p.ID,
                    p.title,
                    p.description,
                    p.image_url,
                    p.user_id,
                    p.begin_date,
                    p.end_date,
                    p.slug
                 FROM post p
                 WHERE p.slug LIKE "%'.$title.'%"';


        $result = $this->getConnection()->prepare($query);
        $result->execute();
        $result = $result->fetch();

        if($result== false)
            return false;

        $catQuery='SELECT 
                      c.ID,
                      c.category_name 
                   FROM post p 
                   INNER JOIN post_category pg on pg.postID = p.ID
                   INNER JOIN category c on pg.categoryID = c.ID 
                   WHERE p.ID =:postId';


        if ($result === false)
            return false;

        $resultCat = $this->getConnection()->prepare($catQuery);

             $resultCat->execute(array(
                'postId'=>$result["ID"]
            ));
            $result['categories']=$resultCat->fetchAll();

            $posts = (new Post())->MapFrom($result);

        return $posts;

        }catch (Exception $e)
        {
            $this->_logger->addError("PostRepository->GetPostByTitle failed: " . $e->getMessage());
            return null;
        }
    }
    public function GetPostById($id)
    {

        try
        {
        $query= 'SELECT
                    p.ID,
                    p.title,
                    p.description,
                    p.image_url,
                    p.user_id,
                    p.begin_date,
                    p.end_date,
                    p.slug
                 FROM post p
                 WHERE p.ID  = '.$id;


        $result = $this->getConnection()->prepare($query);
        $result->execute();
        $result = $result->fetch();


        if($result === false)
            return false;

        $catQuery='SELECT 
                      c.ID,
                      c.category_name 
                   FROM post p 
                   INNER JOIN post_category pg on pg.postID = p.ID
                   INNER JOIN category c on pg.categoryID = c.ID 
                   WHERE p.ID =:postId';



        $resultCat = $this->getConnection()->prepare($catQuery);

        $resultCat->execute(array(
            'postId'=>$result["ID"]
        ));
        $result['categories']=$resultCat->fetchAll();

        $posts = (new Post())->MapFrom($result);

        if($resultCat === false)
            return false;

        return $posts;
        }catch (Exception $e)
        {
            $this->_logger->addError("PostRepository->GetPostById failed: " . $e->getMessage());
            return false;
        }
    }

    public function SavePost(Post $post)
    {
        try
        {
            $this->getConnection()->beginTransaction();
            $query = "";
            if($post->ID == null)
            {
                $query = "INSERT INTO `post`
                         (`title`, `description`, `image_url`, `user_id`, `begin_date`, `end_date`, `slug`) VALUES
                         (:title, :description, :image_url, :user_id, :begin_date, :end_date, :slug)";

            }else
            {
                $query = "UPDATE `post` SET 
                         `title`=:title,`description`=:description,`image_url`=:image_url,`user_id`=:user_id,`begin_date`=:begin_date,`end_date`=:end_date,`slug`=:slug
                         WHERE ID=".$this->getConnection()->quote($post->ID);

                $deleteQuery = "DELETE FROM post_category
                            WHERE postID=".$this->getConnection()->quote($post->ID);

                $deleteResult =  $this->getConnection()->prepare($deleteQuery);
                $deleteResult->execute();
                if($deleteResult === false)
                    return false;
            }

            $result = $this->getConnection()->prepare($query);
            $result->execute(array(
               'title'          => $post->Title,
               'description'    => $post->Description,
               'image_url'      => $post->ImageUrl,
               'user_id'        => $post->UserId,
               'begin_date'     => $post->BeginDate,
               'end_date'       => $post->EndDate,
               'slug'           => $post->Slug,
            ));



            if ($post->ID == null)
                $post->ID = intval($this->getConnection()->lastInsertId());

            if($result === false)
                return false;

            $catQuery="INSERT INTO `post_category`
                          (`categoryID`, `postID`) VALUES ";

            $numItems = count($post->Categories);
            $i = 0;


            foreach ($post->Categories as $category)
            {
                if (++$i === $numItems)
                {
                    $catQuery.="(".$category->CategoryId.",".$post->ID.")";
                }else{
                    $catQuery.="(".$category->CategoryId.",".$post->ID."),";
                }
            }

            $catResult = $this->getConnection()->prepare($catQuery);
            $catResult->execute();
            if($catResult === false)
                return false;

            $this->getConnection()->commit();
            return true;
        }catch (\Exception $e)
        {
            $this->getConnection()->rollBack();
            $this->_logger->addError("PostRepository->SavePost failed: " . $e->getMessage());
            return false;
        }


    }

}