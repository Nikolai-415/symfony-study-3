<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Repository\CityRepository;
use App\Repository\ResumeRepository;
use App\Repository\VacancyRepository;
use DateTime;
use DateTimeZone;
use PDO;
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
            'birth_date' => $resume->getBirthDate()->format('Y-m-d'),
            'sending_datetime' => $resume->getSendingDatetime()->format('Y-m-d H:i:s.u'),
            'city_to_work_in_id' => $resume->getCityToWorkIn()->getId(),
            'desired_vacancy_id' => $resume->getDesiredVacancy()->getId(),
            'avatar' => $resume->getAvatar(),
            'file' => $resume->getFile(),
            'file_name' => $resume->getFileName()
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

    public function edit_data(Request $request, $id = null): JsonResponse
    {
        $is_full_name = $request->request->has('full_name');
        $new_full_name = $is_full_name ? $request->get('full_name') : null;
        
        $is_about = $request->request->has('about');
        $new_about = $is_about ? $request->get('about') : null;
        
        $is_work_experience = $request->request->has('work_experience');
        $new_work_experience = $is_work_experience ? $request->get('work_experience') : null;
        
        $is_desired_salary = $request->request->has('desired_salary');
        $new_desired_salary = $is_desired_salary ? $request->get('desired_salary') : null;
        
        $is_birth_date = $request->request->has('birth_date');
        $new_birth_date = $is_birth_date ? $request->get('birth_date') : null;
        
        $is_sending_datetime = $request->request->has('sending_datetime');
        $new_sending_datetime = $is_sending_datetime ? $request->get('sending_datetime') : null;
        
        $is_city_to_work_in_id = $request->request->has('city_to_work_in_id');
        $new_city_to_work_in_id = $is_city_to_work_in_id ? $request->get('city_to_work_in_id') : null;
        
        $is_desired_vacancy_id = $request->request->has('desired_vacancy_id');
        $new_desired_vacancy_id = $is_desired_vacancy_id ? $request->get('desired_vacancy_id') : null;
        
        $is_avatar = $request->request->has('avatar');
        $new_avatar = $is_avatar ? $request->get('avatar') : null;
        
        $is_file = $request->request->has('file') && $request->request->has('file_name');
        $new_file = $is_file ? $request->get('file') : null;
        $new_file_name = $is_file ? $request->get('file_name') : null;
    
        if($id == null)
        {
            $conn = $this->getDoctrine()->getConnection();
            $stmt = $conn->prepare('SELECT add_record(
                :full_name,
                :about,
                :work_experience,
                :desired_salary,
                :birth_date,
                :sending_datetime,
                :city_to_work_in_id,
                :desired_vacancy_id,
                :avatar,
                :file,
                :file_name
            );');
            $stmt->bindParam(':full_name', $new_full_name);
            $stmt->bindParam(':about', $new_about);
            $stmt->bindParam(':work_experience', $new_work_experience);
            $stmt->bindParam(':desired_salary', $new_desired_salary);
            $stmt->bindParam(':birth_date', $new_birth_date);
            $stmt->bindParam(':sending_datetime', $new_sending_datetime);
            $stmt->bindParam(':city_to_work_in_id', $new_city_to_work_in_id);
            $stmt->bindParam(':desired_vacancy_id', $new_desired_vacancy_id);
            $stmt->bindParam(':avatar', $new_avatar);
            $stmt->bindParam(':file', $new_file);
            $stmt->bindParam(':file_name', $new_file_name);
            $result = $stmt->executeQuery()->fetchAssociative()['add_record'];
            if($result == 'success')
            {
                $errors = null;
                $data = 'success';
            }
            else
            {
                $errors[] = $result;
                $data = null;
            }
        }
        else
        {
            $conn = $this->getDoctrine()->getConnection();
            $stmt = $conn->prepare('SELECT edit_record(
                :id,

                :is_full_name,
                :full_name,

                :is_about,
                :about,

                :is_work_experience,
                :work_experience,

                :is_desired_salary,
                :desired_salary,

                :is_birth_date,
                :birth_date,

                :is_sending_datetime,
                :sending_datetime,

                :is_city_to_work_in_id,
                :city_to_work_in_id,

                :is_desired_vacancy_id,
                :desired_vacancy_id,

                :is_avatar,
                :avatar,

                :is_file,
                :file,
                :file_name
            );');
            $stmt->bindParam(':id', $id);

            $stmt->bindParam(':is_full_name', $is_full_name, PDO::PARAM_BOOL);
            $stmt->bindParam(':full_name', $new_full_name);

            $stmt->bindParam(':is_about', $is_about, PDO::PARAM_BOOL);
            $stmt->bindParam(':about', $new_about);

            $stmt->bindParam(':is_work_experience', $is_work_experience, PDO::PARAM_BOOL);
            $stmt->bindParam(':work_experience', $new_work_experience);

            $stmt->bindParam(':is_desired_salary', $is_desired_salary, PDO::PARAM_BOOL);
            $stmt->bindParam(':desired_salary', $new_desired_salary);

            $stmt->bindParam(':is_birth_date', $is_birth_date, PDO::PARAM_BOOL);
            $stmt->bindParam(':birth_date', $new_birth_date);

            $stmt->bindParam(':is_sending_datetime', $is_sending_datetime, PDO::PARAM_BOOL);
            $stmt->bindParam(':sending_datetime', $new_sending_datetime);

            $stmt->bindParam(':is_city_to_work_in_id', $is_city_to_work_in_id, PDO::PARAM_BOOL);
            $stmt->bindParam(':city_to_work_in_id', $new_city_to_work_in_id);

            $stmt->bindParam(':is_desired_vacancy_id', $is_desired_vacancy_id, PDO::PARAM_BOOL);
            $stmt->bindParam(':desired_vacancy_id', $new_desired_vacancy_id);

            $stmt->bindParam(':is_avatar', $is_avatar, PDO::PARAM_BOOL);
            $stmt->bindParam(':avatar', $new_avatar);

            $stmt->bindParam(':is_file', $is_file, PDO::PARAM_BOOL);
            $stmt->bindParam(':file', $new_file);
            $stmt->bindParam(':file_name', $new_file_name);

            $result = $stmt->executeQuery()->fetchAssociative()['edit_record'];
            if($result == 'success')
            {
                $errors = null;
                $data = 'success';
            }
            else
            {
                $errors[] = $result;
                $data = null;
            }
        }
        
        $json = json_encode(array("errors" => $errors, "data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        return new JsonResponse($json, 200, [], true);
    }

    

    public function delete_data($id): JsonResponse
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT delete_record(:id);');
        $stmt->bindParam(':id', $id);
        $result = $stmt->executeQuery()->fetchAssociative()['delete_record'];
        if($result == 'success')
        {
            $errors = null;
            $data = 'success';
        }
        else
        {
            $errors[] = $result;
            $data = null;
        }

        $json = json_encode(array("errors" => $errors, "data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        return new JsonResponse($json, 200, [], true);
    }
}