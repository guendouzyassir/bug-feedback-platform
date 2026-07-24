<?php

namespace App\Form;

use App\Entity\ClientProfile;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('fullName')
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
            ->add('clientProfile', ClientProfileType::class, [
                'label' => 'Client Profile',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var User|null $user */
            $user = $event->getData();
            $form = $event->getForm();

            $primaryRole = 'ROLE_CLIENT';
            if ($user !== null) {
                foreach (['ROLE_ADMIN', 'ROLE_DEVELOPER', 'ROLE_CLIENT'] as $role) {
                    if (in_array($role, $user->getRoles(), true)) {
                        $primaryRole = $role;
                        break;
                    }
                }
            }

            $form->add('role', ChoiceType::class, [
                'mapped' => false,
                'data' => $primaryRole,
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Developer' => 'ROLE_DEVELOPER',
                    'Client/Tester' => 'ROLE_CLIENT',
                ],
            ]);

            if ($user !== null) {
                if ($user->getClientProfile() === null && $primaryRole === 'ROLE_CLIENT') {
                    $user->setClientProfile(new ClientProfile());
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            $role = $data['role'] ?? null;

            if ($role === 'ROLE_CLIENT') {
                /** @var User|null $user */
                $user = $form->getData();

                if ($user !== null && $user->getClientProfile() === null) {
                    $user->setClientProfile(new ClientProfile());
                }
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var User|null $user */
            $user = $event->getData();
            $form = $event->getForm();

            if ($user === null) {
                return;
            }

            $role = $form->get('role')->getData();

            if ($role === 'ROLE_CLIENT') {
                $clientProfile = $user->getClientProfile();
                if ($clientProfile === null || $clientProfile->getCompanyName() === null || $clientProfile->getCompanyName() === '') {
                    $form->get('clientProfile')->get('companyName')->addError(
                        new FormError('Company name is required when assigning the Client role.')
                    );
                }
            } elseif ($user->getClientProfile() !== null) {
                $user->setClientProfile(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_create' => false,
        ]);

        $resolver->setAllowedTypes('is_create', 'bool');
    }
}
