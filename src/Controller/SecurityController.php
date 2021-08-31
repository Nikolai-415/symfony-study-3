<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Контроллер для страниц авторизации, регистрации и выхода из аккаунта
 */
class SecurityController extends AbstractController
{
    /**
     * Страница авторизации
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Если пользователь уже авторизован - перекидываем его на главную страницу
        if ($this->getUser())
        {
            return $this->redirectToRoute('index');
        }

        // Создание и обработка формы авторизации
        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);
        $form->handleRequest($request);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, // Не моё
            'error' => $error, // Не моё
            'form' => $form->createView(),
        ]);
    }

    /**
     * Страница выхода из аккаунта
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Страница регистрации
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Если пользователь уже авторизован - перекидываем его на главную страницу
        if ($this->getUser())
        {
            return $this->redirectToRoute('index');
        }

        // Создание и обработка формы регистрации
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        // Если форма отправлена
        if ($form->isSubmitted() && $form->isValid())
        {
            // Хэширование пароя
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Формирование токена пользователя для API. По умолчанию равен "$username:$password"
            $user->setToken($user->getUserIdentifier().":".$user->getPassword());

            // Добавление пользователя в БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
