<?php

namespace App\Tests;

use App\Entity\User;
use App\Response\ApiResponse;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Faker\Factory as FakerFactory;
class UserControllerTest extends WebTestCase
{
    private KernelBrowser $_client;
    private EntityManagerInterface $_entityManager;
    private Generator $_fakeUserGenerator;
    public function setUp(): void
    {
        parent::setUp();
        $this->_client = static::createClient();
        $this->_entityManager = $this->_client->getContainer()->get('doctrine')->getManager();
        $this->_fakeUserGenerator = FakerFactory::create("en_US");
    }

    public function testValidationForCreateCase(): void
    {
        $attributes = [
            'firstName' => 'Na',
            'lastName' => 'La',
            'email' => 'mail',
            'employmentDate' => '2000-10-10',
            'salary' => rand(-100,99)

        ];
        $this->_client->jsonRequest('POST', '/api/user',$attributes);
        $this->assertJson($this->_client->getResponse()->getContent());
        $this->assertSame(400,$this->_client->getResponse()->getStatusCode());
        $validationResult = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertArrayHasKey('status',$validationResult);
        $this->assertEquals(ApiResponse::ERROR_STATUS, $validationResult['status']);
        $this->assertArrayHasKey('errors',$validationResult);
        $this->assertArrayHasKey('employmentDate',$validationResult['errors']);
        $this->assertArrayHasKey('salary',$validationResult['errors']);
        $this->assertArrayHasKey('email',$validationResult['errors']);
        $this->assertCount(2, $validationResult['errors']['email']);
        $this->assertArrayHasKey('lastName',$validationResult['errors']);
        $this->assertArrayHasKey('firstName',$validationResult['errors']);
    }
    public function testCreateCase():void{
        $attributes = [
            'firstName' => $this->_fakeUserGenerator->firstName(),
            'lastName' => $this->_fakeUserGenerator->lastName(),
            'email' => $this->_fakeUserGenerator->email(),
            'employmentDate' => date('Y-m-d', strtotime("+1 day")),
            'salary' => rand(100,10000)

        ];
        $this->_client->jsonRequest('POST', '/api/user',$attributes);
        $this->assertJson($this->_client->getResponse()->getContent());
        $this->assertSame(200,$this->_client->getResponse()->getStatusCode());
        $result = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertArrayHasKey('status',$result);
        $this->assertEquals(ApiResponse::SUCCESS_STATUS, $result['status']);
        $this->assertArrayHasKey('data',$result);
        $this->assertArrayHasKey('id',$result['data']);
        $user = $this->_entityManager->getRepository(User::class)->find($result['data']['id']);
        $this->assertInstanceOf(User::class,$user);
    }
    public function testReadCase(): void
    {
        $newEntity = $this->addFakeUserDirectly();
        $this->_client->request('GET', '/api/user?offset=0&limit=100');
        $this->assertJson($this->_client->getResponse()->getContent());
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertArrayHasKey('status',$data);
        $this->assertArrayHasKey('data',$data);
        $this->assertNotEmpty($data['data']);
        $this->assertEquals(ApiResponse::SUCCESS_STATUS, $data['status']);
    }
    public function testUpdateCase():void{

        $newAttributes = [
            'firstName' => $this->_fakeUserGenerator->firstName(),
            'lastName' => $this->_fakeUserGenerator->lastName(),
            'email' => $this->_fakeUserGenerator->email(),
            'employmentDate' => date('Y-m-d', strtotime("+1 day")),
            'salary' => rand(100,10000)
        ];
        $originalUserDada = $this->addFakeUserDirectly();
        $this->_client->jsonRequest('PATCH', '/api/user/'.$originalUserDada->getId(),$newAttributes);
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->_client->getResponse()->getContent());
        $data = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertArrayHasKey('status',$data);
        $this->assertArrayHasKey('data',$data);
        $this->assertNotEmpty($data['data']);
        $this->assertEquals(ApiResponse::SUCCESS_STATUS, $data['status']);
        $this->assertNotEquals($data['data']['firstName'],$originalUserDada->getFirstName());
        $this->assertNotEquals($data['data']['lastName'],$originalUserDada->getLastName());
        $this->assertNotEquals($data['data']['email'],$originalUserDada->getEmail());
        $this->assertNotEquals($data['data']['employmentDate'],$originalUserDada->getEmploymentDate());
        $this->assertNotEquals($data['data']['salary'],$originalUserDada->getSalary());

    }
    public function testRemoveCase():void{
        $newEntity = $this->addFakeUserDirectly();
        $originalUserDada = clone $newEntity;
        $this->_client->jsonRequest('DELETE', '/api/user/'.$originalUserDada->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->_client->getResponse()->getContent());
        $data = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertEquals(ApiResponse::SUCCESS_STATUS, $data['status']);
        $user = $this->_entityManager->getRepository(User::class)->find($originalUserDada->getId());
        $this->assertNull($user,'The user has been not deleted');
    }

    public function testUniqueEmailCase():void{
        $user1 = $this->addFakeUserDirectly();
        $user2 = $this->addFakeUserDirectly();
        $this->_client->jsonRequest('PATCH', '/api/user/'.$user1->getId(),[
            'email' => $user2->getEmail()
        ]);
        $this->assertJson($this->_client->getResponse()->getContent());
        $validationResult = json_decode($this->_client->getResponse()->getContent(),true);
        $this->assertArrayHasKey('errors',$validationResult);
        $this->assertArrayHasKey('email',$validationResult['errors']);
    }

    private function addFakeUserDirectly():User{
        $testUser = new User();
        $testUser->setFirstName($this->_fakeUserGenerator->firstName())
            ->setLastName($this->_fakeUserGenerator->lastName())
            ->setEmail($this->_fakeUserGenerator->email())
            ->setSalary(rand(100,10000))
            ->setTimeOfUpdate(new DateTime('now'))
            ->setEmploymentDate(new DateTime('now + 1 day'));
        $this->_entityManager->persist($testUser);
        $this->_entityManager->flush();
        return clone $testUser; //Because we want to see the original user's data .
    }

}
