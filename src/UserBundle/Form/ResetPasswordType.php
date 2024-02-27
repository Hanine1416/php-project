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

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\User;
use UserBundle\Services\UserService;

/**
 * Class ResetPasswordType
 * @package UserBundle\Form
 */
class ResetPasswordType extends ResettingFormType
{
    /** @var UserService $userService */
    private $userService;

    /** @var  User $user */
    private $user;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->userService = $options['userService'];
        $this->user = $options['user'];
        $builder->add('old', PasswordType::class, [
            'attr' => [
                'class' => 'validate required'
            ]
        ]);

        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            /** check if the user password isValid */
            $login = $this->userService->userLogin($this->user->getEmail(), $data['old']);
            if (!$login['success'])
            {
                $event->getForm()->get('old')->addError(new FormError('user.reset_password.old_password_invalid'));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('userService')
            ->setAllowedTypes('userService', ['object']);
        $resolver->setRequired('user')
            ->setAllowedTypes('user', ['object']);
    }
}
