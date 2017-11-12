<?php 
namespace AppBundle\Data\Repository;

 
use AppBundle\Data\Model\User;

class UserRepository extends BaseRepository
{
    public function GetUser()
    {
        try{
        $query='Select * 
                    From users';

        $result = $this->getConnection()->prepare($query);
        $result->execute();

        $results = $result->fetchAll();

        if($results == false)
            return false;

        $users = array();

        foreach ($results as &$result)
        {
            $users[] = (new User())->MapFrom($result);
        }
        return $users;
        }catch (Exception $e)
        {
            $this->_logger->addError("UserRepository->GetUser failed: " . $e->getMessage());
            return false;
        }
    }

    public function LoginUser($name,$password)
    {
        try{
        $query='SELECT * 
                FROM users WHERE user_name = :user_name  and password = :password';

        $result = $this->getConnection()->prepare($query);
        $result->execute(array(
            ':user_name' => $name,
            ':password'  => $password
        ));

        $result = $result->fetch();

        if($result == false)
            return false;

        return (new User())->MapFrom($result);
        }catch (Exception $e)
        {
            $this->_logger->addError("UserRepository->LoginUser failed: " . $e->getMessage());
            return false;
        }
    }

   

    

}