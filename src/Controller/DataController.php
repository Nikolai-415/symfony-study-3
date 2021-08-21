<?php

namespace App\Controller;

use CurlHandle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{
    private $serverContainerName = "nginx";

    private function getJsonFromApi($urn, &$errors_texts = array()) : mixed
    {
        $uri = $this->serverContainerName."/".$urn;
        $curlHandle = curl_init($uri);
        if($curlHandle != false)
        {
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($curlHandle);
            $response = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);

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
        $cities_data = $this->getJsonFromApi("api/cities_list", $errors_texts);
        $vacancies_data = $this->getJsonFromApi("api/vacancies_list", $errors_texts);
        $resumes_data = $this->getJsonFromApi("api/data_list", $errors_texts);

        $cities = $cities_data->data;
        $vacancies = array();
        foreach($vacancies_data->data as $vacancy_data)
        {
            $vacancies[$vacancy_data->id] = [
                'id' => $vacancy_data->id,
                'name' => $vacancy_data->name,
                'parent' => $vacancy_data->parent_id == null ? null : $vacancies[$vacancy_data->parent_id]
            ];
        }
        $resumes = $resumes_data->data;

        return $this->render('data/index.html.twig', [
            'errors_texts' => $errors_texts,
            'cities' => $cities,
            'vacancies' => $vacancies,
            'resumes' => $resumes,
            'controller_name' => 'DataController',
        ]);
    }
}
