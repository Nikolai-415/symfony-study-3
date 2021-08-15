<?php

namespace App\Entity;

use App\Repository\ResumeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResumeRepository::class)
 */
class Resume
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="integer")
     */
    private $workExperience;

    /**
     * @ORM\Column(type="float")
     */
    private $desiredSalary;

    /**
     * @ORM\Column(type="date")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendingDateTime;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="resumes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cityToWorkIn;

    /**
     * @ORM\ManyToOne(targetEntity=Vacancy::class, inversedBy="resumes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $desiredVacancy;

    /**
     * @ORM\Column(type="binary", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getWorkExperience(): ?int
    {
        return $this->workExperience;
    }

    public function setWorkExperience(int $workExperience): self
    {
        $this->workExperience = $workExperience;

        return $this;
    }

    public function getDesiredSalary(): ?float
    {
        return $this->desiredSalary;
    }

    public function setDesiredSalary(float $desiredSalary): self
    {
        $this->desiredSalary = $desiredSalary;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getSendingDateTime(): ?\DateTimeInterface
    {
        return $this->sendingDateTime;
    }

    public function setSendingDateTime(\DateTimeInterface $sendingDateTime): self
    {
        $this->sendingDateTime = $sendingDateTime;

        return $this;
    }

    public function getCityToWorkIn(): ?City
    {
        return $this->cityToWorkIn;
    }

    public function setCityToWorkIn(?City $cityToWorkIn): self
    {
        $this->cityToWorkIn = $cityToWorkIn;

        return $this;
    }

    public function getDesiredVacancy(): ?Vacancy
    {
        return $this->desiredVacancy;
    }

    public function setDesiredVacancy(?Vacancy $desiredVacancy): self
    {
        $this->desiredVacancy = $desiredVacancy;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }
}
