<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Resume;
use App\Entity\Vacancy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName')
            ->add('about')
            ->add('workExperience')
            ->add('desiredSalary')
            ->add('birthDate')
            ->add('sendingDatetime')
            ->add('avatar')
            ->add('file')
            ->add('cityToWorkIn', EntityType::class, ['class' => City::class, 'choice_label' => 'name', 'choice_value' => 'id'])
            ->add('desiredVacancy', EntityType::class, ['class' => Vacancy::class, 'choice_label' => 'name', 'choice_value' => 'id'])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
        ]);
    }
}
