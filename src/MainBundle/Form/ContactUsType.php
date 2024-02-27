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

use MainBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ContactUsType
 * @package MainBundle\Form
 */
class ContactUsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subjectChoices = [
                'contact_us.form.subject_choices.registration_issue' =>
                    $options['translator']->trans('contact_us.form.subject_choices.registration_issue'),
                'contact_us.form.subject_choices.login_issue' =>
                    $options['translator']->trans('contact_us.form.subject_choices.login_issue'),
                'contact_us.form.subject_choices.heath_resource' =>
                    $options['translator']->trans('contact_us.form.subject_choices.heath_resource'),
                'contact_us.form.subject_choices.stem_resource' =>
                    $options['translator']->trans('contact_us.form.subject_choices.stem_resource')
            ];
        if ($options['translator']->getLocale()!=='anz') {
            $subjectChoices ['contact_us.form.subject_choices.where_book']= $options['translator']->trans('contact_us.form.subject_choices.where_book');
        }
        if ($options['isbn'] != null) {
            $subjectChoices['contact_us.form.subject_choices.digital_not_available'] =$options['translator']->trans('contact_us.form.subject_choices.digital_not_available');
        }
        $builder
            ->add('name', TextType::class, [
                'attr' => ['title' => 'validation.required', 'class' => 'validate', 'data-maxLength' => 255],
                'label' => 'contact_us.form.label_name',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('email', TextType::class, [
                'constraints' => [new NotBlank(), new Email(), new Length(['max' => 255])],
                'required' => false,
                'label' => 'contact_us.form.label_email',
                'attr' => ['title' => 'validation.required', 'class' => 'validate', 'data-maxLength' => 255],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'contact_us.form.label_country',
                'choices' => $options['countries'],
                'choice_label' => 'Text',
                'choice_value' => 'Text',
                'constraints' => [new NotBlank()],
                'attr' => [
                    'title' => 'validation.required',
                    'class' => 'validate',
                    'placeholder' => 'contact_us.form.please_select'
                ]
            ])
            ->add('institution', TextType::class, [
                'label' => 'contact_us.form.label_institution',
                'attr' => ['class' => 'hidden'],
            ])
            ->add('phone', TextType::class, [
                'label' => 'contact_us.form.label_phone',
                'attr' => ['title' => 'contact_us.form.error.wrong_phone_format']])
            ->add(
                'subject',
                ChoiceType::class,
                ['label' => 'contact_us.form.label_subject',
                    'choices' => $subjectChoices,
                    'translation_domain' => 'messages',
                    'constraints' => [new NotBlank()],
                    'attr' => [
                        'title' => 'validation.required',
                        'class' => 'validate',
                        'placeholder' => 'contact_us.form.please_select'
                    ]
                ]
            )
            ->add('description', TextareaType::class, [
                'label' => 'contact_us.form.label_description',
                'constraints' => [new NotBlank(), new Length(['max' => 4000])],
                'attr' => ['title' => 'validation.required', 'class' => 'validate', 'data-maxLength' => 4000],
            ]);
        if ($options['isbn'] != null){
            $builder->add(
                'subject',
                ChoiceType::class,
                ['choices' => $subjectChoices,
                    'data' => $options['translator']->trans('contact_us.form.subject_choices.digital_not_available'),
                ]
            )
             ->add('description', TextareaType::class, [
                'data'=> $options['translator']->trans('contact_us.digital_not_descr').$options['isbn'].'.' ,
            ]);
        }
        $builder->get('country')->addModelTransformer(new CallbackTransformer(function ($data) {
            return $data;
        }, function ($data) {
            return $data->Text?? '';
        }));
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        /** Add country short code to option attribute to be able to use phone number validator */
        foreach ($view->children['country']->vars['choices'] as $choice) {
            /** @var ChoiceView $choice */
            $choice->attr['data-st']=$choice->data->Shorttext;
        }
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Contact::class]);
        $resolver->setRequired('countries');
        $resolver->setRequired('translator');
        $resolver->setRequired('isbn');
    }
}
