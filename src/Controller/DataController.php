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

/**
 * Контроллер для страниц по работе с данными
 */
class DataController extends AbstractController
{
    // ==============================================================================================
    // Вспомогательные методы
    // ==============================================================================================
    /**
     * Адрес сервера API
     */
    private $serverContainerName = "nginx";

    /**
     * Получает JSON-данные от обращения к API (методом POST)
     * @param string $urn URN API
     * @param string[] &$errors_texts Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
     * @param stdClass $post_fields Ассоциативный массив POST-параметров
     * @return stdClass|null Объект JSON или null в случае возникновения ошибки
     */
    private function getJsonFromApi($urn, &$errors_texts = array(), $post_fields = null)
    {
        // Создание объекта CurlHandle
        $uri = $this->serverContainerName."/".$urn;
        $curlHandle = curl_init($uri);

        // Если объект создан успешно
        if($curlHandle != false)
        {
            // Получение информации о текущем пользователе
            // Если он авторизован - добавляет его токен (равен "$username:$password") в POST-параметры
            /**
             * @var \App\Entity\User $user
             */
            $user = $this->getUser();
            if($user != null)
            {
                $username = $user->getUserIdentifier();
                $password = $user->getPassword();
                curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array("X-AUTH-TOKEN: $username:$password"));
            }

            // Если передаются какие-либо POST-параметры - добавляет их в запрос
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

            // Выполнение запроса и получение данных
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($curlHandle);
            curl_close($curlHandle);

            // Получение кода ответа
            $response = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);

            // Если всё в порядке
            if ($response == 200) 
            {
                return json_decode($data);
            }
            // В случае ошибки ответа от сервера
            else if ($response != false) 
            {
                $errors_texts[] = "Ошибка $response при обращении к API по адресу $uri";
                return null;
            }
            // В случае ошибки подключения к серверу
            else
            {
                $errors_texts[] = "Ошибка \"".curl_error($curlHandle)."\" при обращении к API по адресу $uri";
                return null;
            }
        }
        // В случае ошибки при создании объекта CurlHandle
        else
        {
            $errors_texts[] = "Ошибка создания объекта CurlHandle при обращении к API по адресу $uri";
            return null;
        }
    }

    /**
     * Получает массив городов от обращения к API
     * @param string[] &$errors_texts Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
     * @return City[] Массив городов. В случае возникновения ошибок вернёт пустой массив
     */
    private function getCitiesFromApi(&$errors_texts = array())
    {
        //  Получение информации о городах в виде JSON
        /** @var stdClass JSON.
         * Состоит из:
         * - data:
         * -- cities        - массив записей
         * - errors         - массив строк с текстами ошибок
        */
        $json_data = $this->getJsonFromApi("api/cities_list", $errors_texts);

        // ----------------------------------------------------------------------------------------------
        // Преобразование JSON в массив городов
        // ----------------------------------------------------------------------------------------------
        /**
         * @var City[]
         */
        $cities = array();
        // Если JSON получен успешно
        if($json_data != null && $json_data->errors == null)
        {
            foreach($json_data->data->cities as $city_data)
            {
                $city = new City(
                    $city_data->id,
                    $city_data->name
                );
                // ID объекта в массиве равен ID города в БД
                $cities[$city_data->id] = $city;
            }
        }
        // Если возникли ошибки при формировании JSON (при получении ошибки заносятся в методе getJsonFromApi(...))
        else
        {
            // Добавление текстов ошибок формирования JSON в случае возникновения таковых
            foreach($json_data->errors as $error)
            {
                $errors_texts[] = $error;
            }
        }
        // ----------------------------------------------------------------------------------------------

        return $cities;
    }

    /**
     * Получает массив вакансий от обращения к API
     * @param string[] &$errors_texts Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
     * @return Vacancy[] Массив вакансий. В случае возникновения ошибок вернёт пустой массив
     */
    private function getVacanciesFromApi(&$errors_texts = array())
    {
        //  Получение информации о вакансиях в виде JSON
        /** @var stdClass JSON.
         * Состоит из:
         * - data:
         * -- vacancies     - массив записей
         * - errors         - массив строк с текстами ошибок
        */
        $json_data = $this->getJsonFromApi("api/vacancies_list", $errors_texts);

        // ----------------------------------------------------------------------------------------------
        // Преобразование JSON в массив вакансий
        // ----------------------------------------------------------------------------------------------
        /**
         * @var Vacancy[]
         */
        $vacancies = array();
        // Если JSON получен успешно
        if($json_data != null && $json_data->errors == null)
        {
            foreach($json_data->data->vacancies as $vacancy_data)
            {
                $vacancy = new Vacancy(
                    $vacancy_data->id,
                    $vacancy_data->name,
                    $vacancy_data->parent_id == null ? null : $vacancies[$vacancy_data->parent_id]
                );
                // ID объекта в массиве равен ID вакансии в БД
                $vacancies[$vacancy_data->id] = $vacancy;
            }
        }
        // Если возникли ошибки при формировании JSON (при получении ошибки заносятся в методе getJsonFromApi(...))
        else
        {
            // Добавление текстов ошибок формирования JSON в случае возникновения таковых
            foreach($json_data->errors as $error)
            {
                $errors_texts[] = $error;
            }
        }
        // ----------------------------------------------------------------------------------------------

        return $vacancies;
    }

    /**
     * Получает объект резюме из JSON-данных
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param stdClass $json_data_resume JSON с данными резюме
     * @param City[] &$cities Массив городов
     * @param Vacancy[] &$cities Массив вакансий
     * @return Resume Резюме
     */
    private function getResumeFromJsonData($cityRepository, $vacancyRepository, $json_data_resume, &$cities, &$vacancies)
    {
        $resume_city = $cities[$json_data_resume->city_to_work_in_id];
        $resume_vacancy = $vacancies[$json_data_resume->desired_vacancy_id];
        $resume = new Resume (
            $cityRepository,
            $vacancyRepository,
            $json_data_resume->id,
            $json_data_resume->full_name,
            $json_data_resume->about,
            $json_data_resume->work_experience,
            $json_data_resume->desired_salary,
            new DateTime($json_data_resume->birth_date), 
            new DateTime($json_data_resume->sending_datetime), 
            $resume_city,
            $resume_vacancy,
            $json_data_resume->avatar,
            $json_data_resume->file,
            $json_data_resume->file_name
        );
        return $resume;
    }

    /**
     * Получает ассоциативный массив от обращения к API, содержащий массив резюме и количество страниц
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param int|null $page ID страницы
     * @param City[] &$cities Массив городов
     * @param Vacancy[] &$cities Массив вакансий
     * @param string[] &$errors_texts Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
     * @param stdClass $post_fields Ассоциативный массив POST-параметров
     * @return stdClass Ассоциативный массив, содержащий массив резюме и количество страниц
     * Состоит из:
     * - resumes        - массив записей на выбранной странице
     * - pages_number   - количество страниц
     */
    private function getResumesAndPagesNumberFromApi($cityRepository, $vacancyRepository, $page, &$cities, &$vacancies, &$errors_texts = array(), $post_fields = null)
    {
        //  Получение информации о вакансиях и количеству страниц в виде JSON
        /** @var stdClass JSON.
         * Состоит из:
         * - data:
         * -- resumes       - массив записей на выбранной странице
         * -- pages_number  - количество страниц
         * - errors         - массив строк с текстами ошибок
        */
        $json_data = $this->getJsonFromApi("api/data_list/$page", $errors_texts, $post_fields);
        
        // ----------------------------------------------------------------------------------------------
        // Преобразование JSON
        // ----------------------------------------------------------------------------------------------
        $resumesAndPagesNumber = new stdClass();
        $resumesAndPagesNumber->resumes = array();
        $resumesAndPagesNumber->pages_number = 0;
        // Если JSON получен успешно
        if($json_data != null && $json_data->errors == null)
        {
            /**
             * @var Resume[]
             */
            $resumes = array();
            if($json_data->data->resumes != null)
            {
                foreach($json_data->data->resumes as $json_data_resume)
                {
                    $resume = $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $json_data_resume, $cities, $vacancies);
                    // ID объекта в массиве равен ID резюме в БД
                    $resumes[$json_data_resume->id] = $resume;
                }
            }
            $resumesAndPagesNumber->resumes = $resumes;
            $resumesAndPagesNumber->pages_number = $json_data->data->pages_number;
        }
        // Если возникли ошибки при формировании JSON (при получении ошибки заносятся в методе getJsonFromApi(...))
        else
        {
            // Добавление текстов ошибок формирования JSON в случае возникновения таковых
            foreach($json_data->errors as $error)
            {
                $errors_texts[] = $error;
            }
        }
        // ----------------------------------------------------------------------------------------------

        return $resumesAndPagesNumber;
    }

    /**
     * Получает резюме от обращения к API
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param int|null $id ID резюме
     * @param City[] &$cities Массив городов
     * @param Vacancy[] &$cities Массив вакансий
     * @param string[] &$errors_texts Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
     * @return Resume Резюме
     */
    private function getResumeFromApi($cityRepository, $vacancyRepository, $id, &$cities, &$vacancies, &$errors_texts = array())
    {
        //  Получение информации о резюме в виде JSON
        /** @var stdClass JSON.
         * Состоит из:
         * - data:
         * -- resume        - резюме
         * - errors         - массив строк с текстами ошибок
        */
        $json_data = $this->getJsonFromApi("api/get_data/$id", $errors_texts);
        
        // ----------------------------------------------------------------------------------------------
        // Преобразование JSON
        // ----------------------------------------------------------------------------------------------
        $resumesAndPagesNumber = new stdClass();
        $resumesAndPagesNumber->resumes = array();
        $resumesAndPagesNumber->pages_number = 0;
        // Если JSON получен успешно
        if($json_data != null && $json_data->errors == null)
        {
            $cities = $this->getCitiesFromApi($errors_texts);
            $vacancies = $this->getVacanciesFromApi($errors_texts);
            return $this->getResumeFromJsonData($cityRepository, $vacancyRepository, $json_data->data->resume, $cities, $vacancies);
        }
        // Если возникли ошибки при формировании JSON (при получении ошибки заносятся в методе getJsonFromApi(...))
        else
        {
            // Добавление текстов ошибок формирования JSON в случае возникновения таковых
            foreach($json_data->errors as $error)
            {
                $errors_texts[] = $error;
            }
            return null;
        }
        // ----------------------------------------------------------------------------------------------
    }
    // ==============================================================================================

    // ==============================================================================================
    // Страницы
    // ==============================================================================================
    /**
     * Страница просмотра списка резюме
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param Request $request Запрос, содержащий GET-параметры с настройками фильтрации, сортировки и паджинации
     * @param int|null $page ID страницы. Если равен null, будет перенаправление на первую страницу
     * @return Response
     */
    public function data_list(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, int $page = null)
    {
        /**
         * @var string[] Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
         */
        $errors_texts = array();

        /**
         * @var bool Была ли использована форма фильтрации и сортировки
         */
        $is_form_used = false;

        // Страница должна быть указана и должна быть больше 0 - иначе идёт перенаправление на первую страницу
        if($page == null || $page < 1)
        {
            return $this->redirectToRoute('data_list', array('page' => 1));
        }

        $cities = $this->getCitiesFromApi($errors_texts);
        $vacancies = $this->getVacanciesFromApi($errors_texts);

        // Создание и обработка формы фильтрации и сортировки
        $form = $this->createForm(DataListFormType::class, null, array('method' => 'GET', 'action' => $this->generateUrl('data_list', array('page' => 1))));
        $form->handleRequest($request);

        // Поля для передачи в API
        $post_fields = new stdClass();

        // Если форма отправлена
        if ($form->isSubmitted() && $form->isValid())
        {
            // ----------------------------------------------------------------------------------------------
            // Получение данных формы и присвоение значений по умолчанию в случае, если данные не введены
            // ----------------------------------------------------------------------------------------------
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
                
                // Добавление в массив несуществующего ID для того, чтобы функция http_build_query(...) не проигнорировала пустой массив и передала-таки его в API
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
                
                // Добавление в массив несуществующего ID для того, чтобы функция http_build_query(...) не проигнорировала пустой массив и передала-таки его в API
                if(count($filter_desiredVacanciesIds) == 0)
                {
                    $filter_desiredVacanciesIds[] = -1;
                }

                $post_fields->filter_desiredVacanciesIds    = $filter_desiredVacanciesIds;
            }

            $post_fields->sort_field                        = $form['sort_field']->getData();
            $post_fields->sort_ascOrDesc                    = $form['sort_ascOrDesc']->getData();

            $post_fields->records_on_page                   = $form['records_on_page']->getData()               ?? 20;
            // ----------------------------------------------------------------------------------------------

            $is_form_used = true;
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $is_form_invalid = true;
        }

        // Сохранение настроек фильтрации, сортировки и паджинации в сессию для последующей загрузки после добавления/изменения записи
        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params') + $request->query->all();
        $data_list_page_path = $this->generateUrl($routeName, $routeParameters);
        $session = $request->getSession();
        $session->set('data_list_page_path', $data_list_page_path);

        // Обращение к API
        $resumes_and_pages_number = $this->getResumesAndPagesNumberFromApi($cityRepository, $vacancyRepository, $page, $cities, $vacancies, $errors_texts, $post_fields);
        $resumes = $resumes_and_pages_number->resumes;
        $pages_number = $resumes_and_pages_number->pages_number;

        // Если указанный номер страницы превышает общее количество страниц - перенаправляем на последнюю страницу + 1
        // Это специальная страница, отображающая паджинацию как выход за предел диапазона
        if(($is_form_used == true) && ($page > $pages_number) && ($pages_number != 0))
        {
            return $this->redirectToRoute('data_list', array('page' => $pages_number));
        }

        return $this->render('data/data_list.html.twig', [
            'errors_texts' => $errors_texts,
            'cities' => $cities,
            'vacancies' => $vacancies,
            'resumes' => $resumes,
            'form' => $form->createView(),
            'is_form_used' => $is_form_used,
            'is_form_invalid' => $is_form_invalid ?? false,
            'current_page' => $page,
            'pages_number' => $pages_number,
            'pages_at_side' => 3, // Количество страниц, отображающихся в начале и в конце списка паджинации, а также слева и справа от текущей страницы
        ]);
    }

    /**
     * Страница добавления нового или изменения существующего резюме
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param Request $request Запрос, содержащий POST-параметры с информацией о резюме
     * @param int|null $id ID добавляемого резюме. Равен null в случае добавления нового резюме
     * @return Response
     */
    public function edit_data(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, int $id = null)
    {
        /**
         * @var string[] Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
         */
        $errors_texts = array();

        /**
         * @var string[] Массив с текстами ошибок отправки формы
         */
        $form_errors_texts = array();

        /**
         * @var bool Идёт ли изменение существующей записи
         */
        $is_edit = false;

        // ----------------------------------------------------------------------------------------------
        // Создание и обработка формы добавления нового или изменения существующего резюме
        // ----------------------------------------------------------------------------------------------
        // Добавление новой записи
        if($id === null)
        {
            $resume = new Resume($cityRepository, $vacancyRepository);
            $form = $this->createForm(EditDataFormType::class, $resume);
        }
        // Изменение существующей записи
        else
        {
            $is_edit = true;
            
            // Получение резюме по его ID через API
            $resume = $this->getResumeFromApi($cityRepository, $vacancyRepository, $id, $cities, $vacancies, $errors_texts);

            $form = $this->createForm(EditDataFormType::class, $resume);
        }
        $form->handleRequest($request);
        // ----------------------------------------------------------------------------------------------

        // Если форма отправлена
        if ($form->isSubmitted() && $form->isValid())
        {
            $urn = "api/edit_data";
            if($id !== null)
            {
                $urn .= "/$id";
            }

            $deleteAvatar = $form['deleteAvatar']->getData();
            // Если выбрана опция удаления аватара
            if($deleteAvatar && $resume->getAvatar())
            {
                $resume->setAvatar(null);
            }
            // Если не выбрана опция удаления аватара
            else
            {
                $avatarInput = $form['avatar']->getData();
                // Если прикреплён аватар
                if($avatarInput != null)
                {
                    $resume->setAvatar(base64_encode(file_get_contents($avatarInput)));
                }
            }

            $deleteFile = $form['deleteFile']->getData();
            // Если выбрана опция удаления файла резюме
            if($deleteFile && $resume->getFile())
            {
                $resume->setFile(null);
                $resume->setFileName(null);
            }
            // Если не выбрана опция удаления файла резюме
            else
            {
                /**
                 * @var UploadedFile
                 */
                $fileInput = $form['file']->getData();
                // Если прикреплён файл резюме
                if($fileInput != null)
                {
                    $resume->setFile(base64_encode(file_get_contents($fileInput)));
                    $resume->setFileName($fileInput->getClientOriginalName());
                }
            }

            // Отправка запроса к API
            $json_data = $this->getJsonFromApi($urn, $errors_texts, array(
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
            
            if($json_data->errors != null)
            {
                $form_errors_texts += $json_data->errors;
            }

            // Если запрос к API был успешным
            if(($errors_texts == null) && ($form_errors_texts == null) && ($json_data->data->result == 'success'))
            {
                // Загрузка настроек фильтрации, сортировки и паджинации из сессии и перенаправление
                $session = $request->getSession();
                if($data_list_page_path = $session->get('data_list_page_path'))
                {
                    return $this->redirect($data_list_page_path);
                }
                else
                {
                    return $this->redirectToRoute('data_list');
                }
            }
        }

        return $this->render('data/edit_data.html.twig', [
            'errors_texts' => $errors_texts,
            'form_errors_texts' => $form_errors_texts,
            'is_edit' => $is_edit,
            'resume' => $resume,
            'form' => $form->createView(),
            'data_list_page_path' => $request->getSession()->get('data_list_page_path') ?? $this->generateUrl('data_list', array('page' => 1)),
        ]);
    }

    /**
     * Страница удаления резюме
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param Request $request Запрос, содержащий POST-параметры с информацией о подтверждении удаления
     * @param int $id ID удаляемого резюме
     * @return Response
     */
    public function delete_data(CityRepository $cityRepository, VacancyRepository $vacancyRepository, Request $request, int $id) : Response
    {
        /**
         * @var string[] Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
         */
        $errors_texts = array();

        // Получение резюме по его ID через API
        $resume = $this->getResumeFromApi($cityRepository, $vacancyRepository, $id, $cities, $vacancies, $errors_texts);

        // Создание и обработка формы удаления резюме
        $form = $this->createForm(DeleteDataFormType::class, $resume);
        $form->handleRequest($request);

        // Если форма отправлена
        if ($form->isSubmitted() && $form->isValid()) {
            $urn = "api/delete_data/$id";
            $json_data = $this->getJsonFromApi($urn, $errors_texts);
            
            // Если резюме было успешно удалено
            if(($errors_texts == null) && ($json_data->data->result == 'success'))
            {
                return $this->redirectToRoute('data_list');
            }
        }

        return $this->render('data/delete_data.html.twig', [
            'errors_texts' => $errors_texts,
            'form' => $form->createView(),
            'data_list_page_path' => $request->getSession()->get('data_list_page_path') ?? $this->generateUrl('data_list', array('page' => 1)),
        ]);
    }

    /**
     * Страница скачивания файла резюме
     * @param CityRepository $cityRepository Вспомогательный класс для работы с БД через Doctrine
     * @param VacancyRepository $vacancyRepository Вспомогательный класс для работы с БД через Doctrine
     * @param int $id ID резюме
     * @return Response Ответ отображает сообщение об ошибке или закрывается в случае успешного скачивания или его отмены
     */
    public function download_file(CityRepository $cityRepository, VacancyRepository $vacancyRepository, int $id)
    {
        /**
         * @var string[] Массив с текстами ошибок - в него заносится ошибки в случае возникновения таковых
         */
        $errors_texts = array();

        // Получение резюме по его ID через API
        $resume = $this->getResumeFromApi($cityRepository, $vacancyRepository, $id, $cities, $vacancies, $errors_texts);

        if($resume != null)
        {
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
            else
            {
                $errors_texts[] = 'У данного резюме нет файла для скачивания!';
            }
        }

        // Если дошло до сюда, значит, возникли ошибки
        $errors_as_text = "";
        foreach($errors_texts as $error)
        {
            $errors_as_text .= $error." ";
        }
        return new Response("При скачивании файла возникли ошибки: $errors_as_text");
    }
    // ==============================================================================================
}
