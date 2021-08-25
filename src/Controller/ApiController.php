<?php

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ResumeRepository;
use App\Repository\VacancyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/data_list', name: 'api_data_list')]
    public function resumes_list(CityRepository $cityRepository, VacancyRepository $vacancyRepository, ResumeRepository $resumeRepository): JsonResponse
    {
        $data = array();

        $data['cities'] = array();
        $cities = $cityRepository->findAll();
        foreach ($cities as $city)
        {
            $data['cities'][] = [
                'id' => $city->getId(),
                'name' => $city->getName()
            ];
        }

        $data['vacancies'] = array();
        $vacancies = $vacancyRepository->findAll();
        foreach ($vacancies as $vacancy)
        {
            $parent = $vacancy->getParent();
            $data['vacancies'][] = [
                'id' => $vacancy->getId(),
                'name' => $vacancy->getName(),
                'parent_id' => $parent === null ? null : $parent->getId()
            ];
        }

        $data['resumes'] = array();
        $resumes = $resumeRepository->findAll();
        foreach ($resumes as $resume)
        {
            $data['resumes'][] = [
                'id' => $resume->getId(),
                'full_name' => $resume->getFullName(),
                'about' => $resume->getAbout(),
                'work_experience' => $resume->getWorkExperience(),
                'desired_salary' => $resume->getDesiredSalary(),
                'birth_date' => $resume->getBirthDate(),
                'sending_datetime' => $resume->getSendingDatetime(),
                'city_to_work_in_id' => $resume->getCityToWorkIn()->getId(),
                'desired_vacancy_id' => $resume->getDesiredVacancy()->getId(),
                'avatar' => $resume->getAvatar(),
                'file' => $resume->getFile()
            ];
        }

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }
}
