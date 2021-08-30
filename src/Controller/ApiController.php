<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Repository\CityRepository;
use App\Repository\VacancyRepository;
use PDO;
use stdClass;
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

    public function data_list(Request $request, $page = 1): JsonResponse
    {
        $is_filter_id_from = $request->request->has('filter_id_from');
        $filter_id_from = $is_filter_id_from ? $request->get('filter_id_from') : null;
        
        $is_filter_id_to = $request->request->has('filter_id_to');
        $filter_id_to = $is_filter_id_to ? $request->get('filter_id_to') : null;
        
        $is_filter_fullName = $request->request->has('filter_fullName');
        $filter_fullName = $is_filter_fullName ? $request->get('filter_fullName') : null;
        
        $is_filter_about = $request->request->has('filter_about');
        $filter_about = $is_filter_about ? $request->get('filter_about') : null;
        
        $is_filter_workExperience_from = $request->request->has('filter_workExperience_from');
        $filter_workExperience_from = $is_filter_workExperience_from ? $request->get('filter_workExperience_from') : null;
        
        $is_filter_workExperience_to = $request->request->has('filter_workExperience_to');
        $filter_workExperience_to = $is_filter_workExperience_to ? $request->get('filter_workExperience_to') : null;
        
        $is_filter_desiredSalary_from = $request->request->has('filter_desiredSalary_from');
        $filter_desiredSalary_from = $is_filter_desiredSalary_from ? $request->get('filter_desiredSalary_from') : null;
        
        $is_filter_desiredSalary_to = $request->request->has('filter_desiredSalary_to');
        $filter_desiredSalary_to = $is_filter_desiredSalary_to ? $request->get('filter_desiredSalary_to') : null;
        
        $is_filter_birthDate_from = $request->request->has('filter_birthDate_from');
        $filter_birthDate_from = $is_filter_birthDate_from ? $request->get('filter_birthDate_from') : null;
        
        $is_filter_birthDate_to = $request->request->has('filter_birthDate_to');
        $filter_birthDate_to = $is_filter_birthDate_to ? $request->get('filter_birthDate_to') : null;
        
        $is_filter_sendingDatetime_from = $request->request->has('filter_sendingDatetime_from');
        $filter_sendingDatetime_from = $is_filter_sendingDatetime_from ? $request->get('filter_sendingDatetime_from') : null;
        
        $is_filter_sendingDatetime_to = $request->request->has('filter_sendingDatetime_to');
        $filter_sendingDatetime_to = $is_filter_sendingDatetime_to ? $request->get('filter_sendingDatetime_to') : null;
        
        $is_filter_citiesToWorkInIds = $request->request->has('filter_citiesToWorkInIds');
        $filter_citiesToWorkInIds = null;
        if($is_filter_citiesToWorkInIds)
        {
            $filter_citiesToWorkInIds_array = $request->get('filter_citiesToWorkInIds');
            $filter_citiesToWorkInIds = '{';
            $is_first = true;
            foreach($filter_citiesToWorkInIds_array as $element)
            {
                if($is_first == false)
                {
                    $filter_citiesToWorkInIds .= ',';
                }
                $filter_citiesToWorkInIds .= $element;
                $is_first = false;
            }
            $filter_citiesToWorkInIds .= '}';
        }
        
        $is_filter_desiredVacanciesIds = $request->request->has('filter_desiredVacanciesIds');
        $filter_desiredVacanciesIds = null;
        if($is_filter_desiredVacanciesIds)
        {
            $filter_desiredVacanciesIds_array = $request->get('filter_desiredVacanciesIds');
            $filter_desiredVacanciesIds = '{';
            $is_first = true;
            foreach($filter_desiredVacanciesIds_array as $element)
            {
                if($is_first == false)
                {
                    $filter_desiredVacanciesIds .= ',';
                }
                $filter_desiredVacanciesIds .= $element;
                $is_first = false;
            }
            $filter_desiredVacanciesIds .= '}';
        }
        
        $is_sort_field = $request->request->has('sort_field');
        $sort_field = $is_sort_field ? $request->get('sort_field') : 'id';
        
        $is_sort_ascOrDesc = $request->request->has('sort_ascOrDesc');
        $sort_ascOrDesc = $is_sort_ascOrDesc ? $request->get('sort_ascOrDesc') : 'asc';
        
        $is_records_on_page = $request->request->has('records_on_page');
        $records_on_page = $is_records_on_page ? $request->get('records_on_page') : 20;
        
        $conn = $this->getDoctrine()->getConnection();
        $sql_params = '(
            :filter_id_from,
            :filter_id_to,
            :filter_fullName,
            :filter_about,
            :filter_workExperience_from,
            :filter_workExperience_to,
            :filter_desiredSalary_from,
            :filter_desiredSalary_to,
            :filter_birthDate_from,
            :filter_birthDate_to,
            :filter_sendingDatetime_from,
            :filter_sendingDatetime_to,
            :filter_citiesToWorkInIds,
            :filter_desiredVacanciesIds,
            :sort_field,
            :sort_ascOrDesc,
            :records_on_page,
            :page
        )';
        $statements = array(
            'resumes' => $stmt = $conn->prepare("SELECT * FROM get_records $sql_params;"),
            'pages_number' => $stmt = $conn->prepare("SELECT * FROM get_records_pages_number $sql_params;")
        );
        foreach($statements as $stmt)
        {
            $stmt->bindParam(':filter_id_from'              , $filter_id_from);
            $stmt->bindParam(':filter_id_to'                , $filter_id_to);
            $stmt->bindParam(':filter_fullName'             , $filter_fullName);
            $stmt->bindParam(':filter_about'                , $filter_about);
            $stmt->bindParam(':filter_workExperience_from'  , $filter_workExperience_from);
            $stmt->bindParam(':filter_workExperience_to'    , $filter_workExperience_to);
            $stmt->bindParam(':filter_desiredSalary_from'   , $filter_desiredSalary_from);
            $stmt->bindParam(':filter_desiredSalary_to'     , $filter_desiredSalary_to);
            $stmt->bindParam(':filter_birthDate_from'       , $filter_birthDate_from);
            $stmt->bindParam(':filter_birthDate_to'         , $filter_birthDate_to);
            $stmt->bindParam(':filter_sendingDatetime_from' , $filter_sendingDatetime_from);
            $stmt->bindParam(':filter_sendingDatetime_to'   , $filter_sendingDatetime_to);
            $stmt->bindParam(':filter_citiesToWorkInIds'    , $filter_citiesToWorkInIds);
            $stmt->bindParam(':filter_desiredVacanciesIds'  , $filter_desiredVacanciesIds);
            $stmt->bindParam(':sort_field'                  , $sort_field);
            $stmt->bindParam(':sort_ascOrDesc'              , $sort_ascOrDesc);
            $stmt->bindParam(':records_on_page'             , $records_on_page);
            $stmt->bindParam(':page'                        , $page);
        }
        $resumes = $statements['resumes']->executeQuery()->fetchAll();
        $pages_number = $statements['pages_number']->executeQuery()->fetchAssociative()['pages_number'];

        $data = new stdClass();
        if($resumes == null)
        {
            $data->resumes = null;
        }
        else
        {
            foreach ($resumes as $result)
            {
                if ($result['avatar'] != null) $result['avatar'] = stream_get_contents($result['avatar']);
                if ($result['file'] != null) $result['file'] = stream_get_contents($result['file']);
            }
            $data->resumes = $resumes;
        }
        $data->pages_number = $pages_number;

        $json = json_encode(array("data" => $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        return new JsonResponse($json, 200, [], true);
    }

    public function get_data($id): JsonResponse
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM get_record(:id);');
        $stmt->bindParam(':id', $id);
        $result = $stmt->executeQuery()->fetchAssociative();
        if($result == false)
        {
            $errors[] = 'Запись не найдена!';
            $data = null;
        }
        else
        {
            $errors = null;
            $data = $result;
            if ($data['avatar'] != null) $data['avatar'] = stream_get_contents($data['avatar']);
            if ($data['file'] != null) $data['file'] = stream_get_contents($data['file']);
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