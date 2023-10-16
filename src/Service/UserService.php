<?php

namespace App\Service;

use App\Entity\User;
use App\Model\DbCriteria;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private EntityManagerInterface $_entityManager;
    public function __construct(
        EntityManagerInterface $entityManager
    ){
        $this->_entityManager = $entityManager;
    }

    public function saveUser(User $user):User{
        $user->setTimeOfUpdate(new DateTime("now"));
        $this->_entityManager->persist($user);
        $this->_entityManager->flush();
        return $user;
    }

    public function getUsers(DbCriteria $criteria):array{
        return $this->_entityManager->getRepository(User::class)->getUsers($criteria);
    }

    public function removeUser(User $user): void
    {
        $this->_entityManager->remove($user);
        $this->_entityManager->flush();
    }
    public function findById(int $id):?User{
        return $this->_entityManager->getRepository(User::class)->find($id);
    }
}