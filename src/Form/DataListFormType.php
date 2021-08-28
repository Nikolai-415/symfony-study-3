<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Vacancy;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataListFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sendingDatetimeYears = array();
        for($year = 2000; $year <= 2021; $year++) $sendingDatetimeYears[] = $year;

        $fields = array(
            'id' => 'ID',
            'full_name' => 'ФИО',
            'work_experience' => 'Опыт работы',
            'desired_salary' => 'Желаемая заработная плата',
            'birth_date' => 'Дата рождения',
            'sending_datetime' => 'Дата и время отправки резюме',
            'city_to_work_in' => 'Выбранный город трудоустройства',
            'desired_vacancy' => 'Желаемая вакансия',
        );

        $builder
            ->add('isFilter_id', CheckboxType::class)
            ->add('filter_id_from', IntegerType::class)
            ->add('filter_id_to', IntegerType::class)

            ->add('isFilter_fullName', CheckboxType::class)
            ->add('filter_fullName', TextType::class)

            ->add('isFilter_about', CheckboxType::class)
            ->add('filter_about', TextType::class)

            ->add('isFilter_workExperience', CheckboxType::class)
            ->add('filter_workExperience_from', IntegerType::class)
            ->add('filter_workExperience_to', IntegerType::class)

            ->add('isFilter_desiredSalary', CheckboxType::class)
            ->add('filter_desiredSalary_from', NumberType::class)
            ->add('filter_desiredSalary_to', NumberType::class)

            ->add('isFilter_birthDate', CheckboxType::class)
            ->add('filter_birthDate_from', BirthdayType::class, array(
                'data' => new DateTime('1980-01-01')
            ))
            ->add('filter_birthDate_to', BirthdayType::class, array(
                'data' => new DateTime('2000-01-01')
            ))

            ->add('isFilter_sendingDatetime', CheckboxType::class)
            ->add('filter_sendingDatetime_from', DateTimeType::class, [
                'with_seconds' => true,
                'years' => $sendingDatetimeYears,
                'data' => new DateTime('2010-01-01 00:00:00'),
            ])
            ->add('filter_sendingDatetime_to', DateTimeType::class, [
                'with_seconds' => true,
                'years' => $sendingDatetimeYears,
                'data' => new DateTime(),
            ])

            ->add('isFilter_cityToWorkIn', CheckboxType::class)
            ->add('filter_cityToWorkIn', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
            ])
            ->add('isFilter_desiredVacancy', CheckboxType::class)
            ->add('filter_desiredVacancy', EntityType::class, [
                'class' => Vacancy::class,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getNameWithTabs();
                },
                'choice_value' => 'id',
                'multiple' => true,
            ])
            
            ->add('sort_field', ChoiceType::class, array(
                'choices' => $fields,
                'choice_label' => function ($choice, $key, $value) { return $value; },
            ))
            ->add('sort_ascOrDesc', ChoiceType::class, array(
                'choices' => array('asc' => 'возрастания', 'desc' => 'убывания'),
                'choice_label' => function ($choice, $key, $value) { return $value; },
            ))

            ->add('records_on_page', IntegerType::class)
            ->add('page', IntegerType::class)

            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'required' => false
        ]);
    }
}
