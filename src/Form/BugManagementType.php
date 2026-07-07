<?php

namespace App\Form;

use App\Entity\BugReport;
use App\Entity\User;
use App\Enum\BugPriority;
use App\Enum\BugStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BugManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignedDeveloper', EntityType::class, [
                'class' => User::class,
                'choices' => $options['developers'],
                'choice_label' => 'fullName',
                'placeholder' => 'Unassigned',
                'required' => false,
            ])
            ->add('priority', EnumType::class, [
                'class' => BugPriority::class,
                'choice_label' => fn (BugPriority $priority): string => $priority->label(),
            ])
            ->add('status', EnumType::class, [
                'class' => BugStatus::class,
                'choice_label' => fn (BugStatus $status): string => $status->label(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BugReport::class,
            'developers' => [],
        ]);

        $resolver->setAllowedTypes('developers', 'array');
    }
}
