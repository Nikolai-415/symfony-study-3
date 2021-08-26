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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class EditDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sendingDatetimeYears = array();
        for($year = 2000; $year <= 2021; $year++) $sendingDatetimeYears[] = $year;
        
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'ФИО'
            ])
            ->add('about'           , TextareaType::class, [
                'label' => 'Обо мне',
                'required' => false
            ])
            ->add('workExperience', IntegerType::class, [
                'label' => 'Опыт работы',
                'constraints' => [
                    new PositiveOrZero([
                        'message' => '{{ label }} не может быть меньше нуля!',
                    ])
                ],
            ])
            ->add('desiredSalary', NumberType::class, [
                'label' => 'Желаемая заработная плата',
                'constraints' => [
                    new PositiveOrZero([
                        'message' => '{{ label }} не может быть меньше нуля!',
                    ])
                ],
            ])
            ->add('birthDate', BirthdayType::class, [
                'label' => 'Дата рождения'
            ])
            ->add('sendingDatetime', DateTimeType::class, [
                 'label' => 'Дата отправки',
                'with_seconds' => true,
                'years' => $sendingDatetimeYears
            ])
            ->add('avatar', TextType::class, [ // Потом использовать FileType::class
                'label' => 'Аватар',
                'required' => false
            ])
            ->add('file', TextType::class, [ // Потом использовать FileType::class
                'label' => 'Файл резюме',
                'required' => false
            ])
            ->add('cityToWorkIn', EntityType::class, [
                'label' => 'Город трудоустройства',
                'class' => City::class,
                'choice_label' => 'name',
                'choice_value' => 'id'
            ])
            ->add('desiredVacancy', EntityType::class, [
                'label' => 'Желаемая вакансия',
                'class' => Vacancy::class,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getNameWithTabs();
                },
                'choice_value' => 'id'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
        ]);
    }
}
