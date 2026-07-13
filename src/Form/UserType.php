<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('fullName')
            ->add('role', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Developer' => 'ROLE_DEVELOPER',
                    'Client/Tester' => 'ROLE_CLIENT',
                ],
                'data' => $options['current_role'],
            ])
            ->add('isActive')
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => $options['is_create'],
                'label' => $options['is_create'] ? 'Password' : 'New password',
                'help' => $options['is_create'] ? 'Minimum 8 characters.' : 'Leave empty to keep the current password.',
                'constraints' => $options['is_create'] ? [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 8),
                ] : [
                    new Assert\Length(min: 8),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_create' => false,
            'current_role' => 'ROLE_CLIENT',
        ]);

        $resolver->setAllowedTypes('is_create', 'bool');
        $resolver->setAllowedValues('current_role', ['ROLE_ADMIN', 'ROLE_DEVELOPER', 'ROLE_CLIENT']);
    }
}
