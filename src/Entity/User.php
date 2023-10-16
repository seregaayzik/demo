<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(min:3,max:32)]
    #[Groups(['api'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(min:3,max:32)]
    #[Groups(['api'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(min:6,max:64)]
    #[Groups(['api'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\Positive]
    #[Assert\Range(min: 100,max: 100000)]
    #[Assert\NotBlank]
    #[Groups(['api'])]
    #[OA\Property(type: 'number')]
    private ?float $salary = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 'now',
        max: '+10 years',
    )]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['api'])]
    private ?\DateTimeInterface $employmentDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    private ?\DateTimeInterface $timeOfCreate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    private ?\DateTimeInterface $timeOfUpdate = null;

    public function __construct(){
        $this->timeOfCreate = new DateTime("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmploymentDate(): ?\DateTimeInterface
    {
        return $this->employmentDate;
    }

    public function setEmploymentDate(?\DateTimeInterface $employmentDate): static
    {
        $this->employmentDate = $employmentDate;

        return $this;
    }

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function setSalary(?float $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function getTimeOfCreate(): ?\DateTimeInterface
    {
        return $this->timeOfCreate;
    }

    public function getTimeOfUpdate(): ?\DateTimeInterface
    {
        return $this->timeOfUpdate;
    }

    public function setTimeOfUpdate(\DateTimeInterface $timeOfUpdate): static
    {
        $this->timeOfUpdate = $timeOfUpdate;

        return $this;
    }
}
