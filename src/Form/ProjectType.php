<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('platform', ChoiceType::class, [
                'choices' => [
                    'Web' => 'Web',
                    'Mobile' => 'Mobile',
                    'API' => 'API',
                    'Desktop' => 'Desktop',
                ],
                'placeholder' => 'Select a platform',
            ])
            ->add('isActive')
            ->add('assignedDevelopers', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->findDevelopers(),
                'choice_label' => 'fullName',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'required' => false,
                'label' => 'Assigned developers',
                'help' => 'Only these developers can view, report, comment on, and update bugs in this project.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
