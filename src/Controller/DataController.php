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
use DateTimeZone;
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
                        CURLOPT_POSTFIELDS => $post_fields
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
        // Работаю не напрямую с БД, а с созданным API просто для того, чтобы показать, как, к примеру, я бы обрабатывал API извне (классы моделей тоже бы использовал)
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
        // Работаю не напрямую с БД, а с созданным API просто для того, чтобы показать, как, к примеру, я бы обрабатывал API извне (классы моделей тоже бы использовал)
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
            new DateTime($resume_data->birth_date->date, new DateTimeZone($resume_data->birth_date->timezone)), 
            new DateTime($resume_data->sending_datetime->date, new DateTimeZone($resume_data->sending_datetime->timezone)), 
            $resume_city,
            $resume_vacancy,
            $resume_data->avatar,
            $resume_data->file,
            $resume_data->file_name
        );
        return $resume;
    }

    /**
     * @return Resume[]
     */
    private function getResumesFromApi(CityRepository $cityRepository, VacancyRepository $vacancyRepository, &$cities, &$vacancies, &$errors_texts = array())
    {
        // Работаю не напрямую с БД, а с созданным API просто для того, чтобы показать, как, к примеру, я бы обрабатывал API извне (классы моделей тоже бы использовал)
        $data_list = $this->getJsonFromApi("api/data_list", $errors_texts);
        
        /**
         * @var Resume[]
         */
        $resumes = array();
        if($data_list != null)
        {
            foreach($data_list->data as $resume_data)
            {
                $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $resume_data, $cities, $vacancies);
                $resumes[$resume_data->id] = $resume;
            }
        }

        return $resumes;
    }

    public function data_list(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request): Response
    {        
        $cities = $this->getCitiesFromApi($errors_texts);
        $vacancies = $this->getVacanciesFromApi($errors_texts);
        $resumes = $this->getResumesFromApi($cityRepository, $vacancyRepository, $cities, $vacancies, $errors_texts);

        $form = $this->createForm(DataListFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // ...
        }

        return $this->render('data/data_list.html.twig', [
            'errors_texts' => $errors_texts,
            'cities' => $cities,
            'vacancies' => $vacancies,
            'resumes' => $resumes,
            'form' => $form->createView()
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
                'birth_date_date' => $resume->getBirthDate()->format('Y-m-d H:i:s.u'),
                'birth_date_timezone' => $resume->getBirthDate()->getTimezone()->getName(),
                'sending_datetime_date' => $resume->getSendingDatetime()->format('Y-m-d H:i:s.u'),
                'sending_datetime_timezone' => $resume->getSendingDatetime()->getTimezone()->getName(),
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
            return $this->redirectToRoute('edit_data');
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
