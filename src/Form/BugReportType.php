<?php

namespace App\Form;

use App\Entity\BugReport;
use App\Entity\Project;
use App\Enum\BugPriority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BugReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
            ])
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('stepsToReproduce', TextareaType::class, [
                'required' => false,
            ])
            ->add('expectedResult', TextareaType::class, [
                'required' => false,
            ])
            ->add('actualResult', TextareaType::class, [
                'required' => false,
            ])
            ->add('priority', EnumType::class, [
                'class' => BugPriority::class,
                'choice_label' => fn (BugPriority $priority): string => $priority->label(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BugReport::class,
        ]);
    }
}
