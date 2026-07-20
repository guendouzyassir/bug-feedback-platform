<?php

namespace App\Form;

use App\Entity\ClientProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'attr' => ['placeholder' => 'e.g. Acme Corp'],
            ])
            ->add('phoneNumber', TelType::class, [
                'required' => false,
                'label' => 'Phone Number',
                'attr' => ['placeholder' => 'e.g. +213 555 123 456'],
            ])
            ->add('companyAddress', TextareaType::class, [
                'required' => false,
                'label' => 'Company Address',
                'attr' => ['rows' => 3, 'placeholder' => 'e.g. 123 Main St, Batna, Algeria'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientProfile::class,
        ]);
    }
}
