<?php

namespace App\Entity;

use App\Repository\CityRepository;
use App\Repository\ResumeRepository;
use App\Repository\VacancyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Резюме
 * @ORM\Entity(repositoryClass=ResumeRepository::class)
 * @ORM\Table(name="resumes")
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
    private $sendingDatetime;

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
     * @ORM\Column(type="text", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    public function __construct(
        CityRepository $cityRepository,
        VacancyRepository $vacancyRepository,
        int $id = null,
        string $fullName = null,
        ?string $about = null,
        int $workExperience = null,
        float $desiredSalary = null,
        \DateTimeInterface $birthDate = null,
        \DateTimeInterface $sendingDatetime = null,
        City $cityToWorkIn = null,
        Vacancy $desiredVacancy = null,
        string $avatar = null,
        string $file = null,
        string $fileName = null
    ) {
        $this->id = $id;
        $this   ->setFullName($fullName ?? '')
                ->setAbout($about)
                ->setWorkExperience($workExperience ?? 0)
                ->setDesiredSalary($desiredSalary ?? 0)
                ->setBirthDate($birthDate ?? new \DateTime())
                ->setSendingDatetime($sendingDatetime ?? new \DateTime())
                ->setCityToWorkIn($cityToWorkIn ?? $cityRepository->findOneBy(array('id' => 0)))
                ->setDesiredVacancy($desiredVacancy ?? $vacancyRepository->findOneBy(array('id' => 0)))
                ->setAvatar($avatar)
                ->setFile($file)
                ->setFileName($fileName);
    }

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

    public function getSendingDatetime(): ?\DateTimeInterface
    {
        return $this->sendingDatetime;
    }

    public function setSendingDatetime(\DateTimeInterface $sendingDatetime): self
    {
        $this->sendingDatetime = $sendingDatetime;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
