<?php

namespace App\Form;

use App\Form\Model\AlertList as AlertListFormModel;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\File;

final class AlertListForm
{
    public const MAX_ITEMS = 20;

    private FormFactory $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function generate(AlertListFormModel $alert, string $formAction): FormInterface
    {
        $formBuilder = $this->formFactory->createBuilder(FormType::class, $alert)
            ->setAction($formAction)
            ->setMethod('POST');

        for($i=0; $i<self::MAX_ITEMS; $i++) {
            $formBuilder = $this->addFormItemPerIndex($formBuilder, $i);
        }

        $formBuilder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'save',
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
                    'label' => 'active',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ]
            )
            ->add(
                'name_' . $index,
                TextType::class,
                [
                    'label' => 'reward',
                    'required' => false,
                    'help' => 'reward_hint',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            )
            ->add(
                'sound_' . $index,
                FileType::class,
                [
                    'label' => 'soundfile',
                    'mapped' => false,
                    'required' => false,
                    'help' => 'soundfile_hint',
                    'attr' => [
                        'class' => 'form-control-file',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'audio/mpeg',
                            ],
                            'mimeTypesMessage' => 'invalid_file',
                        ]),
                    ],
                ]
            );
    }
}