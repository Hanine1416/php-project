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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\Institution;

/**
 * Institution form type for creating new institution
 * Class InstitutionType
 * @package UserBundle\Form
 */
class InstitutionType extends AbstractType
{
    /**
     * Here where you should add the fields that will present in the form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $institutions = [];
        foreach ($options['institutions'] as $key => $institution)
        {
            $institutions [$institution['name']] = $key;
        }
        /** Create builder for institution type fields */
        $builder
            ->add(
                'id',
                HiddenType::class,
                ['label' => false, 'attr' => ['disabled' => 'disabled', 'class' => 'inst-identification']]
            )->add('institutionName', TextType::class, [
                'label' => 'user.register.form.fields.institution',
                'constraints' => [new NotBlank()],
                'required' => false,
                'attr' => ['class' => 'institution-name validate', 'title' => 'validation.required']])
            ->add('institutionId', ChoiceType::class, [
                'label' => 'user.register.form.fields.institution',
                'required' => false,
                'choices' => $institutions,
                'choice_attr' => function ($choice, $key, $value) use ($options) {
                    return ['data-type' => $options['institutions'][$value]['type']];
                },
                'attr' => ['class' => 'institution-id validate', 'title' => 'validation.required']])
            ->add('isPrimary', CheckboxType::class, [
                'required' => false,
                'label' => 'user.profile.institutions.isPrimary',
                'attr' => ['class' => 'isPrimary']
            ])
            ->add('departmentName', TextType::class, [
                'label' => 'user.register.form.fields.department',
                'required' => false,
                'attr' => ['class' => 'department-name validate', 'title' => 'validation.required']])
            ->add('departmentId', ChoiceType::class, [
                'label' => 'user.register.form.fields.department',
                'required' => false,
                'attr' => ['class' => 'department-id validate', 'title' => 'validation.required']])
            ->add('profession', ChoiceType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'user.register.form.fields.profession',
                'required' => false,
                'choices' => $options['professions'],
                'attr' => ['class' => 'validate profession', 'title' => 'validation.required']
            ])
            ->add('speciality', ChoiceType::class, [
                'label' => 'user.register.form.fields.speciality',
                'required' => false,
                'attr' => ['class' => 'validate speciality', 'title' => 'validation.required']]);
        $builder->get('speciality')->resetViewTransformers();
        $builder->get('institutionId')->resetViewTransformers();
        $builder->get('departmentId')->resetViewTransformers();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Institution::class, 'allow_extra_fields' => true])
            ->setRequired('professions')
            ->setRequired('institutions');
    }
}
