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

use MainBundle\Entity\Banner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BookRequestType
 * @package MainBundle\Form
 */
class BannerType extends AbstractType
{
    /**
     * Add field to form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imagePosition', ChoiceType::class, [
            'label' => false,
            'choices' => ['Image Left' => 'slideLeft', 'Image Right' => 'slideRight'],
            'placeholder' => 'Image Position'
        ])
            ->add('title', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Title*']])
            ->add('text', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Please add some content*', 'cols' => 30, 'rows' => 10]])
            ->add('type', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'http://example.com'], 'required' => false]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Banner::class, 'allow_extra_fields' => true]);
    }
}
