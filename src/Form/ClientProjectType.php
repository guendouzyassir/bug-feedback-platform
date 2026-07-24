<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignedClients', EntityType::class, [
                'class' => User::class,
                'choices' => $options['clients'],
                'choice_label' => 'fullName',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Assign Clients to this Project',
                'help' => 'Select the clients who should have access to this project.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'clients' => [],
        ]);

        $resolver->setAllowedTypes('clients', 'array');
    }
}