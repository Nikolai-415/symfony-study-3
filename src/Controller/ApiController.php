<?php

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ResumeRepository;
use App\Repository\VacancyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/cities_list', name: 'api_cities_list')]
    public function cities_list(CityRepository $cityRepository): JsonResponse
    {
        $cities = $cityRepository->findAll();

        $data = array();
        foreach ($cities as $city)
        {
            $data[] = [
                'id' => $city->getId(),
                'name' => $city->getName()
            ];
        }

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/vacancies_list', name: 'api_vacancies_list')]
    public function vacancies_list(VacancyRepository $vacancyRepository): JsonResponse
    {
        $vacancies = $vacancyRepository->findAll();

        $data = array();
        foreach ($vacancies as $vacancy)
        {
            $parent = $vacancy->getParent();
            $data[] = [
                'id' => $vacancy->getId(),
                'name' => $vacancy->getName(),
                'parent_id' => $parent === null ? null : $parent->getId()
            ];
        }

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/resumes_list', name: 'api_resumes_list')]
    public function resumes_list(ResumeRepository $resumeRepository): JsonResponse
    {
        $resumes = $resumeRepository->findAll();

        $data = array();
        foreach ($resumes as $resume)
        {
            $data[] = [
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
                'file' => $resume->getFile(),
            ];
        }

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }
}
