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
use MainBundle\Entity\BookRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class BookRequestType
 * @package MainBundle\Form
 */
class BookRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookIsbn', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('bookFormat', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('preOrder')
            ->add('addressId', TextType::class)
            ->add('institutions', CollectionType::class, [
            'entry_type' => BookInstitutionRequestType::class,
            'allow_add' => true
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BookRequest::class,
            'csrf_protection' => true]);
    }

    public function getBlockPrefix()
    {
        return 'bookRequest';
    }
}
