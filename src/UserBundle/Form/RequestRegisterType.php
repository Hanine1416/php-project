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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\RequestRegister;

/**
 * Class RequestRegisterType
 * @package UserBundle\Form
 */
class RequestRegisterType extends AbstractType
{
    /**
     * Add register required fields and build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $placeholder = new \stdClass();
        $placeholder->Text = "";
        $currentLanguage = $options['language'];

        /** Add desired fields */
        $builder
            ->add('title', TextType::class, [
                'constraints' => [],
                'required' => false,
                'label' => 'user.register.form.fields.title',
                'attr' => ['title' => 'validation.required']
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [new NotBlank()],
                'error_bubbling' => true,
                'required' => false,
                'label' => 'user.register.form.fields.firstname',
                'attr' => ['title' => 'validation.firstname']
            ]);
        /** If the language is india then add middlename input to register form */
        if ($currentLanguage === "in")
        {
            $builder->add('middlename', TextType::class, [
                'required' => false,
                'label' => 'user.register.form.fields.middlename',
            ]);
        }

        $builder->add('lastname', TextType::class, [
            'constraints' => [new NotBlank()],
            'required' => false,
            'error_bubbling' => true,
            'label' => 'user.register.form.fields.lastname',
            'attr' => ['title' => 'validation.lastname']
        ])
            ->add('url', UrlType::class, [
                'constraints' => $currentLanguage === "br" ? [new NotBlank()] : [],
                'required' => false,
                'label' => 'user.register.form.fields.url',
                'attr' => ['title' => 'validation.teacher_url', 'data-max-length' => 80]
            ])
            ->add('email', TextType::class, [
                'constraints' => [new NotBlank(), new Email()],
                'required' => false,
                'label' => 'user.register.form.fields.email',
            ])
            /** Add password and confirm fields */
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'constraints' => [new NotBlank()],
                'first_options' => [
                    'label' => 'user.register.step1.password',
                    'attr' => ['class' => 'validate required password']
                ],
                'second_options' => [
                    'label' => 'user.register.step1.confirm_password',
                    'attr' => ['class' => 'validate required password_check']
                ],
            ))
            ->add('acceptMarketing',
                CheckboxType::class,
                ['label' => 'user.register.step1.notification_check_box', 'required' => false]
            );
    }

    /**
     * Return bind form to class
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => RequestRegister::class])
            ->setRequired('language');
    }
}
