<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Vacancy;
use App\Entity\Resume;
use App\Form\DataListFormType;
use App\Form\DeleteDataFormType;
use App\Form\EditDataFormType;
use App\Repository\CityRepository;
use App\Repository\VacancyRepository;
use DateTime;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class DataController extends AbstractController
{
    private $serverContainerName = "nginx";

    private function getJsonFromApi($urn, &$errors_texts = array(), $post_fields = null) : mixed
    {
        $uri = $this->serverContainerName."/".$urn;
        $curlHandle = curl_init($uri);
        if($curlHandle != false)
        {
            /**
             * @var \App\Entity\User
             */
            $user = $this->getUser();
            if($user != null)
            {
                $username = $user->getUserIdentifier();
                $password = $user->getPassword();
                curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array("X-AUTH-TOKEN: $username:$password"));
            }

            if($post_fields != null)
            {
                curl_setopt_array (
                    $curlHandle,
                    array (
                        CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => http_build_query($post_fields)
                    )
                );
            }

            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($curlHandle);
            $response = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);
            curl_close($curlHandle);

            if ($response == 200) // Если всё в порядке
            {
                return json_decode($data);
            }
            else if ($response != false) // В случае ошибки ответа от сервера
            {
                $errors_texts[] = "Ошибка $response при обращении к API по адресу $uri";
                return null;
            }
            else // В случае ошибки подключения к серверу
            {
                $errors_texts[] = "Ошибка \"".curl_error($curlHandle)."\" при обращении к API по адресу $uri";
                return null;
            }
        }
        else
        {
            $errors_texts[] = "Ошибка создания объекта CurlHandle при обращении к API по адресу $uri";
            return null;
        }
    }

    /**
     * @return City[]
     */
    private function getCitiesFromApi(&$errors_texts = array())
    {
        $cities_list = $this->getJsonFromApi("api/cities_list", $errors_texts);

        /**
         * @var City[]
         */
        $cities = array();
        if($cities_list != null)
        {
            foreach($cities_list->data as $city_data)
            {
                $city = new City(
                    $city_data->id,
                    $city_data->name
                );
                $cities[$city_data->id] = $city;
            }
        }

        return $cities;
    }

    /**
     * @return Vacancy[]
     */
    private function getVacanciesFromApi(&$errors_texts = array())
    {
        $vacancies_list = $this->getJsonFromApi("api/vacancies_list", $errors_texts);

        /**
         * @var Vacancy[]
         */
        $vacancies = array();
        if($vacancies_list != null)
        {
            foreach($vacancies_list->data as $vacancy_data)
            {
                $vacancy = new Vacancy(
                    $vacancy_data->id,
                    $vacancy_data->name,
                    $vacancy_data->parent_id == null ? null : $vacancies[$vacancy_data->parent_id]
                );
                $vacancies[$vacancy_data->id] = $vacancy;
            }
        }

        return $vacancies;
    }

    /**
     * @return Resume
     */
    private function getResumeFromJsonData(CityRepository $cityRepository, VacancyRepository $vacancyRepository, $resume_data, &$cities, &$vacancies)
    {
        $resume_city = $cities[$resume_data->city_to_work_in_id];
        $resume_vacancy = $vacancies[$resume_data->desired_vacancy_id];
        $resume = new Resume (
            $cityRepository,
            $vacancyRepository,
            $resume_data->id,
            $resume_data->full_name,
            $resume_data->about,
            $resume_data->work_experience,
            $resume_data->desired_salary,
            new DateTime($resume_data->birth_date), 
            new DateTime($resume_data->sending_datetime), 
            $resume_city,
            $resume_vacancy,
            $resume_data->avatar,
            $resume_data->file,
            $resume_data->file_name
        );
        return $resume;
    }

    private function getResumesAndPagesNumberFromApi($page, CityRepository $cityRepository, VacancyRepository $vacancyRepository, &$cities, &$vacancies, &$errors_texts = array(), $post_fields = null)
    {
        $data_list = $this->getJsonFromApi("api/data_list/$page", $errors_texts, $post_fields);
        
        $resumes_and_pages_number = new stdClass();
        $resumes_and_pages_number->resumes = array();
        $resumes_and_pages_number->pages_number = 0;
        if($data_list != null)
        {
            /**
             * @var Resume[]
             */
            $resumes = array();
            if($data_list->data->resumes != null)
            {
                foreach($data_list->data->resumes as $resume_data)
                {
                    $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $resume_data, $cities, $vacancies);
                    $resumes[$resume_data->id] = $resume;
                }
            }
            $resumes_and_pages_number->resumes = $resumes;
            $resumes_and_pages_number->pages_number = $data_list->data->pages_number;
        }

        return $resumes_and_pages_number;
    }

    public function data_list(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, $page = null): Response
    {
        if($page == null || $page < 1)
        {
            return $this->redirectToRoute('data_list', array('page' => 1));
        }

        $cities = $this->getCitiesFromApi($errors_texts);
        $vacancies = $this->getVacanciesFromApi($errors_texts);

        $form = $this->createForm(DataListFormType::class, null, array('method' => 'GET', 'action' => $this->generateUrl('data_list', array('page' => 1))));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // Поля для передачи в API
            $post_fields = new stdClass();

            // Получение данных формы и присвоение значений по умолчанию в случае, если данные не введены
            $isFilter_id = $form['isFilter_id']->getData();
            if($isFilter_id)
            {
                $post_fields->filter_id_from                = $form['filter_id_from']->getData()                ?? 0;
                $post_fields->filter_id_to                  = $form['filter_id_to']->getData()                  ?? 100;
            }
            
            $isFilter_fullName = $form['isFilter_fullName']->getData();
            if($isFilter_fullName)
            {
                $post_fields->filter_fullName               = $form['filter_fullName']->getData();
            }

            $isFilter_about = $form['isFilter_about']->getData();
            if($isFilter_about)
            {
                $post_fields->filter_about                  = $form['filter_about']->getData();
            }

            $isFilter_workExperience = $form['isFilter_workExperience']->getData();
            if($isFilter_workExperience)
            {
                $post_fields->filter_workExperience_from    = $form['filter_workExperience_from']->getData()    ?? 5;
                $post_fields->filter_workExperience_to      = $form['filter_workExperience_to']->getData()      ?? 10;
            }

            $isFilter_desiredSalary = $form['isFilter_desiredSalary']->getData();
            if($isFilter_desiredSalary)
            {
                $post_fields->filter_desiredSalary_from     = $form['filter_desiredSalary_from']->getData()     ?? 30000;
                $post_fields->filter_desiredSalary_to       = $form['filter_desiredSalary_to']->getData()       ?? 50000;
            }

            $isFilter_birthDate = $form['isFilter_birthDate']->getData();
            if($isFilter_birthDate)
            {
                $post_fields->filter_birthDate_from         = $form['filter_birthDate_from']->getData()->format('Y-m-d');
                $post_fields->filter_birthDate_to           = $form['filter_birthDate_to']->getData()->format('Y-m-d');
            }

            $isFilter_sendingDatetime = $form['isFilter_sendingDatetime']->getData();
            if($isFilter_sendingDatetime)
            {
                $post_fields->filter_sendingDatetime_from   = $form['filter_sendingDatetime_from']->getData()->format('Y-m-d H:i:s');
                $post_fields->filter_sendingDatetime_to     = $form['filter_sendingDatetime_to']->getData()->format('Y-m-d H:i:s');
            }

            $isFilter_cityToWorkIn = $form['isFilter_cityToWorkIn']->getData();
            if($isFilter_cityToWorkIn)
            {
                $filter_citiesToWorkInIds = array();
                foreach($form['filter_cityToWorkIn']->getData() as $element)
                {
                    $filter_citiesToWorkInIds[] = $element->getId();
                }
                
                // Добавление в массив несуществующего ID для того, чтобы
                // функция http_build_query(...) не проигнорировала пустой
                // массив и передала-таки его в API
                if(count($filter_citiesToWorkInIds) == 0)
                {
                    $filter_citiesToWorkInIds[] = -1;
                }

                $post_fields->filter_citiesToWorkInIds      = $filter_citiesToWorkInIds;
            }

            $isFilter_desiredVacancy = $form['isFilter_desiredVacancy']->getData();
            if($isFilter_desiredVacancy)
            {
                $filter_desiredVacanciesIds = array();
                foreach($form['filter_desiredVacancy']->getData() as $element)
                {
                    $filter_desiredVacanciesIds[] = $element->getId();
                }
                
                // Добавление в массив несуществующего ID для того, чтобы
                // функция http_build_query(...) не проигнорировала пустой
                // массив и передала-таки его в API
                if(count($filter_desiredVacanciesIds) == 0)
                {
                    $filter_desiredVacanciesIds[] = -1;
                }

                $post_fields->filter_desiredVacanciesIds    = $filter_desiredVacanciesIds;
            }

            $post_fields->sort_field                        = $form['sort_field']->getData();
            $post_fields->sort_ascOrDesc                    = $form['sort_ascOrDesc']->getData();

            $post_fields->records_on_page                   = $form['records_on_page']->getData()               ?? 20;

            $resumes_and_pages_number = $this->getResumesAndPagesNumberFromApi($page, $cityRepository, $vacancyRepository, $cities, $vacancies, $errors_texts, $post_fields);
            $resumes = $resumes_and_pages_number->resumes;
            $pages_number = $resumes_and_pages_number->pages_number;

            $is_form_used = true;
        }
        else
        {
            $resumes_and_pages_number = $this->getResumesAndPagesNumberFromApi($page, $cityRepository, $vacancyRepository, $cities, $vacancies, $errors_texts);
            $resumes = $resumes_and_pages_number->resumes;
            $pages_number = $resumes_and_pages_number->pages_number;

            $is_form_used = false;

            if($page > $pages_number && $pages_number != 0)
            {
                return $this->redirectToRoute('data_list', array('page' => $pages_number));
            }
        }

        return $this->render('data/data_list.html.twig', [
            'errors_texts' => $errors_texts,
            'cities' => $cities,
            'vacancies' => $vacancies,
            'resumes' => $resumes,
            'form' => $form->createView(),
            'is_form_used' => $is_form_used,
            'current_page' => $page,
            'pages_number' => $pages_number,
            'pages_at_side' => 3,
        ]);
    }

    public function edit_data(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, $id = null) : Response
    {
        $errors_texts = null;
        $is_edit = false;
        $resume = new Resume($cityRepository, $vacancyRepository);
        $success_message = null;

        if($id == null) // если ID не указан - идёт добавление новой записи
        {
            $this->denyAccessUnlessGranted('ROLE_ADD');
            $form = $this->createForm(EditDataFormType::class, $resume);
        }
        else
        {
            $is_edit = true;

            $resume_data = $this->getJsonFromApi("api/get_data/$id", $errors_texts);
            if($resume_data->errors == null)
            {
                $cities = $this->getCitiesFromApi($errors_texts);
                $vacancies = $this->getVacanciesFromApi($errors_texts);
                $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $resume_data->data, $cities, $vacancies);
            }
            else
            {
                return $this->redirectToRoute('edit_data');
            }
            $form = $this->createForm(EditDataFormType::class, $resume);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urn = "api/edit_data";
            if($id != null)
            {
                $urn .= "/$id";
            }

            $deleteAvatar = $form['deleteAvatar']->getData();
            if($deleteAvatar && $resume->getAvatar())
            {
                $resume->setAvatar(null);
            }
            else
            {
                $avatarInput = $form['avatar']->getData();
                if($avatarInput != null)
                {
                    $resume->setAvatar(base64_encode(file_get_contents($avatarInput)));
                }
            }

            $deleteFile = $form['deleteFile']->getData();
            if($deleteFile && $resume->getFile())
            {
                $resume->setFile(null);
                $resume->setFileName(null);
            }
            else
            {
                /**
                 * @var UploadedFile
                 */
                $fileInput = $form['file']->getData();
                if($fileInput != null)
                {
                    $resume->setFile(base64_encode(file_get_contents($fileInput)));
                    $resume->setFileName($fileInput->getClientOriginalName());
                }
            }

            $edit_result = $this->getJsonFromApi($urn, $errors_texts, array(
                'full_name' => $resume->getFullName(),
                'about' => $resume->getAbout(),
                'work_experience' => $resume->getWorkExperience(),
                'desired_salary' => $resume->getDesiredSalary(),
                'birth_date' => $resume->getBirthDate()->format('Y-m-d'),
                'sending_datetime' => $resume->getSendingDatetime()->format('Y-m-d H:i:s'),
                'city_to_work_in_id' => $resume->getCityToWorkIn()->getId(),
                'desired_vacancy_id' => $resume->getDesiredVacancy()->getId(),
                'avatar' => $resume->getAvatar(),
                'file' => $resume->getFile(),
                'file_name' => $resume->getFileName()
            ));

            if(($errors_texts == null) && ($edit_result->data == 'success'))
            {
                if($id != null) // Если идёт изменение записи
                {
                    $success_message = 'Запись успешно изменена!';
                }
                else // Если идёт добавление записи
                {
                    return $this->redirectToRoute('data_list');
                }
            }
        }

        return $this->render('data/edit_data.html.twig', [
            'errors_texts' => $errors_texts,
            'is_edit' => $is_edit,
            'resume' => $resume,
            'form' => $form->createView(),
            'success_message' => $success_message,
        ]);
    }

    public function delete_data(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, $id) : Response
    {
        $errors_texts = null;
        $is_edit = false;
        $resume = new Resume($cityRepository, $vacancyRepository);

        $resume_data = $this->getJsonFromApi("api/get_data/$id", $errors_texts);
        if($resume_data->errors == null)
        {
            $cities = $this->getCitiesFromApi($errors_texts);
            $vacancies = $this->getVacanciesFromApi($errors_texts);
            $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $resume_data->data, $cities, $vacancies);
        }
        else
        {
            return $this->redirectToRoute('data_list');
        }
        $form = $this->createForm(DeleteDataFormType::class, $resume);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urn = "api/delete_data/$id";
            $edit_result = $this->getJsonFromApi($urn, $errors_texts);

            if(($errors_texts == null) && ($edit_result->data == 'success'))
            {
                return $this->redirectToRoute('data_list');
            }
        }

        return $this->render('data/delete_data.html.twig', [
            'errors_texts' => $errors_texts,
            'is_edit' => $is_edit,
            'form' => $form->createView(),
        ]);
    }

    public function download_file(CityRepository $cityRepository, VacancyRepository $vacancyRepository, $id)
    {
        $resume_data = $this->getJsonFromApi("api/get_data/$id", $errors_texts);
        if($resume_data != null && $resume_data->errors == null)
        {
            $cities = $this->getCitiesFromApi($errors_texts);
            $vacancies = $this->getVacanciesFromApi($errors_texts);
            $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $resume_data->data, $cities, $vacancies);
            $file = $resume->getFile();
            if($file != null)
            {
                $filename = $resume->getFileName();
                
                // Generate response
                $response = new Response();

                // Set headers
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'";');

                // Send headers before outputting anything
                $response->sendHeaders();

                $response->setContent(base64_decode($file));

                return $response;
            }
        }
        $errors_as_text = "";
        foreach($resume_data->errors as $error)
        {
            $errors_as_text .= $error." ";
        }
        return new Response("При скачивании файла возникли ошибки: $errors_as_text");
    } 
}
