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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Reset password form
 * Class ResettingFormType
 * @package UserBundle\Form
 */
class ResettingFormType extends AbstractType
{
    /**
     * Add register required fields and build form
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** Add password and confirm fields */
        $builder->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'constraints' => [new NotBlank()],
                'first_options' => [
                    'label'=>'user.register.step1.password',
                    'attr' => ['class' => 'validate required password']
                ],
                'second_options' => [
                    'label'=>'user.register.step1.confirm_password',
                    'attr' => ['class' => 'validate required password_check']
                ],
            ));
    }

    /**
     * Return bind form to class
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'resetting'
            ]
        );
    }
}