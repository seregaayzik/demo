<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i<= 20; $i++){
            $fakeUserData = FakerFactory::create("en_US");
            $testUser = new User();
            $testUser->setFirstName($fakeUserData->firstName())
                ->setLastName($fakeUserData->lastName())
                ->setEmail($fakeUserData->email())
                ->setSalary(rand(100,10000))
                ->setTimeOfUpdate(new DateTime('now'))
                ->setEmploymentDate(new DateTime('now + 1 day'));
            $manager->persist($testUser);
        }
        $manager->flush();
    }
}
