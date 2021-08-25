<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Vacancy;
use App\Entity\Resume;
use CurlHandle;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;

class DataController extends AbstractController
{
    private $serverContainerName = "nginx";

    private function getJsonFromApi($urn, &$errors_texts = array()) : mixed
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

    #[Route('/', name: 'index')]
    public function index(): Response
    {        
        // Работаю не напрямую с БД, а с созданным API просто для того, чтобы показать, как, к примеру, я бы обрабатывал API извне (классы моделей тоже бы использовал)

        $data_list = $this->getJsonFromApi("api/data_list", $errors_texts);

        /**
         * @var City[]
         */
        $cities = array();

        /**
         * @var Vacancy[]
         */
        $vacancies = array();

        /**
         * @var Resume[]
         */
        $resumes = array();

        if($data_list != null)
        {
            //if($data_list != null)
            {
                foreach($data_list->data->cities as $city_data)
                {
                    $city = new City(
                        $city_data->id,
                        $city_data->name
                    );
                    $cities[$city_data->id] = $city;
                }
            }
    
            //if($vacancies_data != null)
            {
                foreach($data_list->data->vacancies as $vacancy_data)
                {
                    $vacancy = new Vacancy(
                        $vacancy_data->id,
                        $vacancy_data->name,
                        $vacancy_data->parent_id == null ? null : $vacancies[$vacancy_data->parent_id]
                    );
                    $vacancies[$vacancy_data->id] = $vacancy;
                }
            }
            
            //if($resumes_data != null)
            {
                foreach($data_list->data->resumes as $resume_data)
                {
                    $resume_city = $cities[$resume_data->city_to_work_in_id];
                    $resume_vacancy = $vacancies[$resume_data->desired_vacancy_id];
                    $resume = new Resume (
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
                        $resume_data->file
                    );
                    $resume_city->addResume($resume);
                    $resume_vacancy->addResume($resume);
                    $resumes[$resume_data->id] = $resume;
                }
            }
        }

        return $this->render('data/index.html.twig', [
            'errors_texts' => $errors_texts,
            'cities' => $cities,
            'vacancies' => $vacancies,
            'resumes' => $resumes,
            'controller_name' => 'DataController',
        ]);
    }
}
