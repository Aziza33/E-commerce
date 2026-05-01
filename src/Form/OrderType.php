<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{

    private ?User $user = null;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'data' => $this->user->getFirstName(),
                'required' => true,
                'attr' => [
                    'class' => 'form form-control',
                ]
            ])
            ->add('lastName', TextType::class, [
                'data' => $this->user->getLastName(),
                'attr' => [
                    'class' => 'form form-control',
                ]
            ])
            ->add('email', TextType::class, [
                'data' => $this->user->getEmail(),
                'attr' => [
                    'class' => 'form form-control',
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'data' => $this->user->getPhoneNumber(),
                'attr' => [
                    'class' => 'form form-control',
                    '00'
                ]
            ])
            ->add('address', TextType::class, [
                'data' => $this->user->getAddress(),
                'attr' => [
                    'class' => 'form form-control',
                    'v'
                ]
            ])
            // ->add('createdAt', null, [
            //     'attr' => [
            //         'class'=>'form form-control',
            //         'widget' => 'single_text',
            //     ]
            // ])

            ->add('city', EntityType::class, [
                'data' => $this->user->getCity(),
                'class' => City::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form form-control'
                ]
            ])
             ->add('zipCode', TextType::class, [
                'data' => $this->user->getZipCode(),
                'attr' => [
                    'class' => 'form form-control',
                ]
            ])
            ->add('payOnDelivery', CheckboxType::class, [
                'label' => 'Payez à la livraison',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-outline-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
