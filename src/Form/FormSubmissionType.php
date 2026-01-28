<?php

namespace App\Form;

use App\Entity\FormSubmission;
use App\Repository\ColorRepository;
use App\Repository\ShapeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class FormSubmissionType extends AbstractType
{
    private $colorRepository;
    private $shapeRepository;

    public function __construct(ColorRepository $colorRepository, ShapeRepository $shapeRepository)
    {
        $this->colorRepository = $colorRepository;
        $this->shapeRepository = $shapeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Получаем цвета и фигуры для select'ов
        $colors = $this->colorRepository->findAll();
        $colorChoices = [];
        foreach ($colors as $color) {
            $colorChoices[$color->getName()] = $color->getId();
        }

        $shapes = $this->shapeRepository->findAll();
        $shapeChoices = [];
        foreach ($shapes as $shape) {
            $shapeChoices[$shape->getName()] = $shape->getId();
        }

        $builder
            ->add('text', TextareaType::class, [
                'label' => 'Текст сообщения',
                'constraints' => [
                    new NotBlank(['message' => 'Поле не должно быть пустым']),
                ],
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email адрес',
                'constraints' => [
                    new NotBlank(['message' => 'Поле не должно быть пустым']),
                    new Email(['message' => 'Введите корректный email адрес']),
                    new Length(['max' => 255, 'maxMessage' => 'Email не должен превышать 255 символов'])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('color', ChoiceType::class, [
                'label' => 'Цвет',
                'choices' => $colorChoices,
                'constraints' => [
                    new NotBlank(['message' => 'Выберите цвет']),
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('shape', ChoiceType::class, [
                'label' => 'Фигура',
                'choices' => $shapeChoices,
                'constraints' => [
                    new NotBlank(['message' => 'Выберите фигуру']),
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('images', FileType::class, [
                'label' => 'Изображения (до 4 файлов)',
                'multiple' => true,
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Загрузите хотя бы одно изображение']),
                    new Count([
                        'max' => 4,
                        'maxMessage' => 'Можно загрузить не более {{ limit }} изображений'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);

        // Обработчик для установки цветов и фигур
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $formSubmission = $event->getData();
            
            if ($form->isValid()) {
                $colorId = $form->get('color')->getData();
                $shapeId = $form->get('shape')->getData();
                
                $color = $this->colorRepository->find($colorId);
                $shape = $this->shapeRepository->find($shapeId);
                
                if ($color && $shape) {
                    $formSubmission->setColor($color);
                    $formSubmission->setShape($shape);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormSubmission::class,
        ]);
    }
}
