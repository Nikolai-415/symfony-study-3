<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Resume;
use App\Entity\Vacancy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class EditDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sendingDatetimeYears = array();
        for($year = 2000; $year <= 2021; $year++) $sendingDatetimeYears[] = $year;
        
        $builder
            ->add('fullName', TextType::class)
            ->add('about'           , TextareaType::class, [
                'required' => false,
            ])
            ->add('workExperience', IntegerType::class, [
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Опыт работы не может быть меньше нуля!',
                    ])
                ],
            ])
            ->add('desiredSalary', NumberType::class, [
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Желаемая заработная плата не может быть меньше нуля!',
                    ]),
                ],
            ])
            ->add('birthDate', BirthdayType::class)
            ->add('sendingDatetime', DateTimeType::class, [
                'with_seconds' => true,
                'years' => $sendingDatetimeYears,
            ])
            ->add('deleteAvatar', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('avatar', FileType::class, [
                'required' => false,
                'mapped' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new ConstraintsFile([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Разрешённые форматы: .bmp, .gif, .jpeg, .jpg, .png.',
                    ])
                ],
            ])
            ->add('deleteFile', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('cityToWorkIn', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
            ])
            ->add('desiredVacancy', EntityType::class, [
                'class' => Vacancy::class,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getNameWithTabs();
                },
                'choice_value' => 'id',
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
        ]);
    }
}
