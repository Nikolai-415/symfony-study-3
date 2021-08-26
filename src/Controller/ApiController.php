<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Repository\CityRepository;
use App\Repository\ResumeRepository;
use App\Repository\VacancyRepository;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{
    public function cities_list(CityRepository $cityRepository): JsonResponse
    {
        $data = array();
        $cities = $cityRepository->findAll();
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

    public function vacancies_list(VacancyRepository $vacancyRepository): JsonResponse
    {
        $data = array();
        $vacancies = $vacancyRepository->findAll();
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

    private function getResumeDataAsArray(Resume $resume)
    {
        return array(
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
        );
    }

    public function data_list(ResumeRepository $resumeRepository): JsonResponse
    {
        $data = array();
        $resumes = $resumeRepository->findAll();
        foreach ($resumes as $resume)
        {
            $data[] = $this->getResumeDataAsArray($resume);
        }

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }

    public function get_data($id, ResumeRepository $resumeRepository): JsonResponse
    {
        $errors = null;

        $resume = $resumeRepository->findOneBy(array('id' => $id));
        if($resume != null)
        {
            $data = $this->getResumeDataAsArray($resume);
        }
        else
        {
            $errors[] = 'Запись не найдена!';
            $data = null;
        }

        $json = json_encode(array("errors" => $errors, "data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

        return new JsonResponse($json, 200, [], true);
    }

    public function edit_data(
        Request $request,
        CityRepository $cityRepository,
        VacancyRepository $vacancyRepository,
        ResumeRepository $resumeRepository,
        $id = null
    ): JsonResponse
    {
        $errors = null;

        if($id == null)
        {
            $resume = new Resume($cityRepository, $vacancyRepository);
        }
        else
        {
            $resume = $resumeRepository->findOneBy(array('id' => $id));
        }

        if($resume == null)
        {
            $errors[] = 'Запись не найдена!';
            $data = null;
        }
        else
        {
            if($request->request->has('full_name'))
            {
                $full_name = $request->get('full_name');
                if($full_name != null) $resume->setFullName($full_name);
                else $errors[] = 'ФИО не может быть равно null!';
            }
            
            if($request->request->has('about'))
            {
                $about = $request->get('about');
                $resume->setAbout($about);
            }
            
            if($request->request->has('work_experience'))
            {
                $work_experience = $request->get('work_experience');
                if($work_experience != null) $resume->setWorkExperience($work_experience);
                else $errors[] = 'Опыт работы не может быть равен null!';
            }
            
            if($request->request->has('desired_salary'))
            {
                $desired_salary = $request->get('desired_salary');
                if($desired_salary != null) $resume->setDesiredSalary($desired_salary);
                else $errors[] = 'Желаемая заработная плата не может быть равна null!';
            }
            
            if($request->request->has('birth_date_date') || $request->request->has('birth_date_timezone'))
            {
                if(($request->get('birth_date_date') != null) && ($request->get('birth_date_timezone') != null))
                {
                    $birth_date = new DateTime($request->get('birth_date_date'), new DateTimeZone($request->get('birth_date_timezone')));
                    $resume->setBirthDate($birth_date);
                }
                else $errors[] = 'Должны быть указаны birth_date_date и birth_date_timezone!';
            }
            
            if($request->request->has('sending_datetime_date') || $request->request->has('sending_datetime_timezone'))
            {
                if(($request->get('sending_datetime_date') != null) && ($request->get('sending_datetime_timezone') != null))
                {
                    $sending_datetime = new DateTime($request->get('sending_datetime_date'), new DateTimeZone($request->get('sending_datetime_timezone')));
                    $resume->setSendingDatetime($sending_datetime);
                }
                else $errors[] = 'Должны быть указаны sending_datetime_date и sending_datetime_timezone!';
            }
            
            if($request->request->has('city_to_work_in_id'))
            {
                $cityToWorkIn = $cityRepository->findOneBy(array('id' => $request->get('city_to_work_in_id')));
                if($cityToWorkIn != null) $resume->setCityToWorkIn($cityToWorkIn);
                else $errors[] = 'Город не найден!';
            }
            
            if($request->request->has('desired_vacancy_id'))
            {
                $desiredVacancy = $vacancyRepository->findOneBy(array('id' => $request->get('desired_vacancy_id')));
                if($desiredVacancy != null) $resume->setDesiredVacancy($desiredVacancy);
                else $errors[] = 'Вакансия не найдена!';
            }
            
            if($request->request->has('avatar'))
            {
                $avatar = $request->get('avatar');
                $resume->setAvatar($avatar);
            }
            
            if($request->request->has('file'))
            {
                $file = $request->get('file');
                $resume->setFile($file);
            }

            // Обновление записи в БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($resume);
            $entityManager->flush();
    
            $data = 'success';
        }
        $json = json_encode(array("errors" => $errors, "data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        return new JsonResponse($json, 200, [], true);
    }

    

    public function delete_data(
        ResumeRepository $resumeRepository,
        $id
    ): JsonResponse
    {
        $errors = null;

        $resume = $resumeRepository->findOneBy(array('id' => $id));

        if($resume == null)
        {
            $errors[] = 'Запись не найдена!';
            $data = null;
        }
        else
        {
            // Удаление записи из БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($resume);
            $entityManager->flush();
    
            $data = 'success';
        }
        $json = json_encode(array("errors" => $errors, "data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        return new JsonResponse($json, 200, [], true);
    }
}