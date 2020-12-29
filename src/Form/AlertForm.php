<?php

namespace App\Form;

use App\Form\Model\Alert as AlertFormModel;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\File;

final class AlertForm
{
    public const MAX_ITEMS = 9;

    private FormFactory $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function generate(AlertFormModel $alert): FormInterface
    {
        $formBuilder = $this->formFactory->createBuilder(FormType::class, $alert);

        for($i=0; $i<self::MAX_ITEMS; $i++) {
            $formBuilder = $this->addFormItemPerIndex($formBuilder, $i);
        }

        $formBuilder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-block'
                ],
            ]
        );

        return $formBuilder->getForm();
    }

    private function addFormItemPerIndex(FormBuilderInterface $formBuilder, int $index): FormBuilderInterface
    {
        return $formBuilder
            ->add(
                'active_' . $index,
                CheckboxType::class,
                [
                    'label' => 'Active',
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ]
            )
            ->add(
                'name_' . $index,
                TextType::class,
                [
                    'label' => 'Reward',
                    'help' => 'Name of the twitch reward',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            )
            ->add(
                'sound_' . $index,
                FileType::class,
                [
                    'label' => 'Soundfile',
                    'mapped' => false,
                    'required' => false,
                    'help' => 'Only mp3 files allowed.',
                    'attr' => [
                        'class' => 'form-control-file',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'audio/mpeg',
                            ],
                            'mimeTypesMessage' => 'Invalid file.',
                        ]),
                    ],
                ]
            );
    }
}