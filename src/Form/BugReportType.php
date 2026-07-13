<?php

namespace App\Form;

use App\Entity\BugReport;
use App\Entity\Project;
use App\Enum\BugPriority;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BugReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'query_builder' => fn (ProjectRepository $repo) => $repo->createQueryBuilder('p')
                    ->where('p.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('p.name', 'ASC'),
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
            ->add('screenshot', FileType::class, [
                'label' => 'Screenshot',
                'mapped' => false,
                'required' => false,
                'help' => 'Accepted formats: JPG, PNG, WEBP. Maximum size: 2 MB.',
                'attr' => [
                    'accept' => '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp',
                ],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Please upload a JPG, PNG, or WEBP screenshot.',
                    ),
                ],
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
