<?php 

namespace AppBundle\Data\Repository;


use AppBundle\Data\Model\Category;
use AppBundle\Domain\Model\PagedList;

class CategoryRepository extends BaseRepository
{
    /**
     * @param $id
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function DeleteCategory($id)
    {
        try{
            $this->getConnection()->beginTransaction();
            $query = 'DELETE 
                  FROM category 
                  WHERE ID = '.$id
        $result = $this->getConnection()->prepare($query);
        $result->execute();

        if($result === false)
            return false;
            $this->getConnection()->commit();

        return true;
        }catch (\Exception $e)
        {
            $this->getConnection()->rollBack();
            $this->_logger->addError("CategoryRepository->DeleteCategory failed: " . $e->getMessage());
            return false;
        }
    }

    public function SaveCategory(Category $category)
    {
        try{

        if($category->CategoryId == 'null')
        {
            $query='INSERT INTO category 
                (category_name) VALUES (:category_name)';
        }else
        {
            $query = 'UPDATE category
                  SET category_name = :category_name
                  WHERE ID = '.$category->CategoryId;
        }

        $result = $this->getConnection()->prepare($query);

        $result->execute(array(
            ':category_name'=> $category->CategoryName
        ));

        if($result === false)
            return false;

        return true;
        }catch (Exception $e)
        {
            $this->_logger->addError("CategoryRepository->SaveCategory failed: " . $e->getMessage());
            return false;
        }
    }
 
    /**
     * @param $categoryId
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function GetCategoryById($categoryId)
    {
        try{
        $query= 'SELECT 
                      c.ID,
                      c.category_name
                FROM category c 
                WHERE c.ID = '.$categoryId;

        $result = $this->getConnection()->prepare($query);
        $result->execute();

        $result = $result->fetch();
        if ($result === false)
            return false;

        return (new Category())->MapFrom($result);
        }catch (Exception $e)
        {
            $this->_logger->addError("CategoryRepository->GetCategoryById failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param null $searchKey
     * @return PagedList
     * @throws \Doctrine\DBAL\DBALException
     */
    public function GetCategory($offset = 0, $limit=5, $searchKey = null)
    {
        try{
        $countQuery='SELECT COUNT(*) as row_count 
                FROM category';
        if($searchKey !== null)
        {
            $countQuery .=' WHERE category_name
                        LIKE "%'.$searchKey.'%"';
        }

        $countResult = $this->getConnection()->prepare($countQuery);
        $countResult->execute();
        $countResults = $countResult->fetch();

        if($countResult === null || (int)$countResults['row_count'] == 0)
            return new PagedList(null,0);

        $query= 'SELECT 
                      c.ID,
                      c.category_name
                FROM category c';
        if($searchKey !== null)
        {
            $query .=' WHERE c.category_name
                        LIKE "%'.$searchKey.'%"';
        }
        $query .=' LIMIT '.$offset.','.$limit;

        $result = $this->getConnection()->prepare($query);

        $result->execute();

        $results = $result->fetchAll();


        if($results == false)
            return new PagedList(null, 0);

        $categories = array();

        foreach ($results as $result)
        {
            $categories[] = (new Category())->MapFrom($result);
        }
        
        $list = new PagedList($categories,(int)$countResults['row_count']);

        return $list;
        }catch (Exception $e)
        {
            $this->_logger->addError("CategoryRepository->GetCategory failed: " . $e->getMessage());
            return false;
        }

    }


    public function GetAllCategory()
    {
        try{

            $query= 'SELECT 
                      c.ID,
                      c.category_name
                FROM category c';

            $result = $this->getConnection()->prepare($query);

            $result->execute();

            $results = $result->fetchAll();


            if($results == false)
                return false;

            $categories = array();

            foreach ($results as $result)
            {
                $categories[] = (new Category())->MapFrom($result);
            }
            
            return $categories;

        }catch (Exception $e)
        {
            $this->_logger->addError("CategoryRepository->GetCategory failed: " . $e->getMessage());
            return false;
        }

    }

    public function GetKeyCategory($key)
    {
        try{
        $query='SELECT
                    c.ID,
                    c.category_name
                FROM category c
                WHERE category_name
                LIKE "%'.$key.'%"';

        $result = $this->getConnection()->prepare($query);
        $result->execute();
        $results = $result->fetchAll();

        if($results === false)
            return false;
        $categories = array();

        foreach ($results as &$result)
        {
            $categories[] = (new Category())->MapFrom($result);
        }
 
        return $categories;

        }catch (Exception $e)
        {
            $this->_logger->addError("CategoryRepository->GetKeyCategory failed: " . $e->getMessage());
            return false;
        }

    }




}