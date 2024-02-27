<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

/**
 * User address form type
 * Class AddressType
 * @package UserBundle\Form
 */
class AddressType extends AbstractType
{
    /**
     * Here where you should add the fields that will present in the form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** Create builder for address type fields */
        $builder
            ->add('id', HiddenType::class)
            ->add('state', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.state',
                'attr' => [
                    'title' => 'validation.required',
                    'data-max-length' => in_array($options['language'], ['es', 'en']) ? 30 : 80
                ]
            ])->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.postalCode',
                'attr' => [
                    'title' => 'validation.required',
                    'data-max-length' => in_array($options['language'], ['es', 'en']) ? 30 : 80
                ]
            ])->add('city', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.city',
                'attr' => ['title' => 'validation.required'
                ]
            ])->add('country', ChoiceType::class, [
                'label' => 'user.register.form.fields.country',
                'choices' => $options['countries'],
                'choice_label' => 'Text',
                'choice_value' => 'Text',
                'constraints' => [new NotBlank()],
                'required' => false,
                'choices_as_values' => true,
                'data_class' => null,
                'attr' => ['title' => 'validation.required']
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'user.register.form.fields.phone',
                'attr' => [ 'title' => 'validation.required', 'data-max-length' => 20]
            ])
            ->add('address1', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.address1',
                'attr' => [
                    'title' => 'validation.required',
                    'data-max-length' => in_array($options['language'], ['es', 'en']) ? 30 : 80
                ]
            ])->add('address2', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.address2',
                'attr' => [
                    'title' => 'validation.required',
                    'data-max-length' => in_array($options['language'], ['es', 'en']) ? 30 : 80
                ]
            ])
            ->add('address3', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.address3',
                'attr' => [
                    'title' => 'validation.required',
                    'data-max-length' => $options['language'] == 'en' ? 30 : 80
                ]
            ]);
        /** Add address 4 field only for es and br site language */
        if ($options['language'] === 'es')
        {
            $builder->add('address4', TextType::class, [
                'required' => false,
                'label' => 'user.profile.address.address4',
                'attr' => [
                    'title' => 'validation.required',
                ]
            ]);
        }
    }

    /**
     * Define configuration and dependencies for this form builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Address::class,'csrf_protection'=>true])
            ->setRequired('language')
            ->setRequired('countries');
    }
}
