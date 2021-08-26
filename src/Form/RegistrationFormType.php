<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [ 
                'label' => 'Логин',
                'constraints' => [
                    new NotBlank([
                        'message' => '{{ label }} должен быть введён!',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => '{{ label }} не может быть меньше {{ limit }} символов!',
                        'max' => 64,
                        'maxMessage' => '{{ label }} не может быть больше {{ limit }} символов!',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Пароль',
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => '{{ label }} должен быть введён!',
                    ]),
                    new Length([
                        'min' => 4,
                        'minMessage' => '{{ label }} не может быть меньше {{ limit }} символов!',
                        'max' => 32,
                        'maxMessage' => '{{ label }} не может быть больше {{ limit }} символов!',
                    ]),
                ],
            ])
            ->add('register', SubmitType::class, ['label' => 'Зарегистрироваться'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
