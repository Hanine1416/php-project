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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\User;

/**
 * User details form type
 * Class DetailsType
 * @package UserBundle\Form
 */
class DetailsType extends AbstractType
{
    /**
     * Here where you should add the fields that will present in the form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries=[];
        foreach ($options['countries'] as $country)
        {
            $countries[$country->Text]=$country->Text;
        }

        $currentLanguage = $options['language'];

        /** Create builder for details type fields */
        $builder
            ->add('email', TextType::class, [
                'constraints' => [new NotBlank(), new Email()],
                'disabled' => true,
                'required' => false,
                'label' => 'user.register.form.fields.email',
                'attr' => ['title' => 'validation.required']
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'user.register.form.fields.country',
                'choices' => $countries,
                'constraints' => [new NotBlank()],
                'required' => false,
                'attr' => [ 'title' => 'validation.required']
            ])
            ->add('title', TextType::class, [
                'constraints' => [],
                'required' => false,
                'label' => $currentLanguage === "de"?'user.register.form.fields.title_no_optional':'user.register.form.fields.title',
                'attr' => [ 'title' => 'validation.required']
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [new NotBlank()],
                'required' => false,
                'label' => 'user.register.form.fields.firstname',
                'attr' => ['title' => 'validation.required']
            ])->add('lastname', TextType::class, [
                'constraints' => [new NotBlank()],
                'required' => true,
                'label' => 'user.register.form.fields.lastname',
                'attr' => ['title' => 'validation.required']
            ])->add('url', UrlType::class, [
                'constraints' => $currentLanguage === "br" ? [new NotBlank()] : [],
                'required' => false,
                'label' => 'user.register.form.fields.url',
                'attr' => ['title' => 'validation.required']
            ])->add('mobile', TextType::class, [
                'required' => false,
                'label' => 'user.register.form.fields.phone',
                'attr' => [ 'title' => 'validation.required']
            ])
            ->add('password', PasswordType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'user.register.form.fields.password',
                'attr' => [ 'title' => 'validation.required']
            ]);
        /** If the language is india then add middlename input to register form */
        if ($currentLanguage === "in")
        {
            $builder->add('middlename', TextType::class, [
                'required' => true,
                'label' => 'user.register.form.fields.middlename',
            ]);
        }
        /** If the language is india then add cpf input to register form */
        if ($currentLanguage === "br")
        {
            $builder->add('cpf', TextType::class, [
                'constraints' => [new NotBlank()],
                'required' => false,
                'label' => 'user.register.form.fields.cpf',
                'attr' => ['title' => 'validation.required']
            ]);
        }
    }

    /**
     * Define configuration and dependencies for this form builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class])
            ->setRequired('language')
            ->setRequired('countries');
    }
}
