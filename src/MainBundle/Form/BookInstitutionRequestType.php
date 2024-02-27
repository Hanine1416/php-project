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

namespace MainBundle\Form;

use MainBundle\Entity\BookInstitutionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookInstitutionRequestType extends AbstractType
{
    /**
     * Build book institution request form with provided fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** Adding needed fields to the form */
        $builder
            ->add('institutionId', TextType::class, ['constraints'=> [new NotBlank()]])
            ->add('course', TextType::class, ['label' => false,'constraints'=> [new NotBlank()]])
            ->add('courseName', TextType::class, ['label' => false])
            ->add('courseCode', TextType::class, ['label' => false])
            ->add('studentsNumber', TextType::class, ['label' => false,'constraints'=> [new NotBlank()]])
            /** Add start and end date fields */
            ->add('startDate', TextType::class, ['label' => false,'constraints'=> [new NotBlank()]])
            ->add('endDate', TextType::class, ['label' => false])
            ->add('courseLevel', TextType::class, ['label' => false, 'constraints'=> [new NotBlank()]])
            /** Add book reason fields with predefined reasons */
            ->add('bookUsedReason', ChoiceType::class, ['label' => false, 'constraints'=> [new NotBlank()],
                'choices' => [
                    'No currently used a similar title' => 'No currently used a similar title',
                    'New course' => 'New course',
                    'Considering alternative' => 'Considering alternative'
                ]])
            /** Add current used book fields */
            ->add('currentUsedBook', TextType::class, ['label' => false])
            /** Add book rec level fields with predefined levels */
            ->add('recLevel', ChoiceType::class, [
                'choices' => [
                    'Core' => 'Core',
                    'Recommended' => 'Recommended',
                    'Supplementary' => 'Supplementary',
                    'Undecided' => 'Undecided',
                ]]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** Set default binding class  */
        $resolver->setDefaults(['data_class' => BookInstitutionRequest::class,
            'allow_extra_fields'=>true]);
    }
}
