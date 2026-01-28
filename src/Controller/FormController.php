<?php

namespace App\Controller;

use App\Entity\FormSubmission;
use App\Form\FormSubmissionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{
    #[Route('/', name: 'app_form')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $formSubmission = new FormSubmission();
        $form = $this->createForm(FormSubmissionType::class, $formSubmission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обработка изображений
            $images = $form->get('images')->getData();
            $imageNames = [];
            
            if ($images) {
                $uploadDir = $this->getParameter('images_directory');
                
                foreach ($images as $image) {
                    if ($image) {
                        // Генерируем уникальное имя файла
                        $originalName = $image->getClientOriginalName();
                        
                        // Получаем расширение
                        $extension = $image->guessExtension();
                        if (!$extension) {
                            $extension = $image->getClientOriginalExtension();
                        }
                        if (!$extension) {
                            $extension = 'jpg';
                        }
                        
                        // Проверяем допустимые расширения
                        $allowedExtensions = ['jpg', 'jpeg', 'png'];
                        $extension = strtolower($extension);
                        if (!in_array($extension, $allowedExtensions)) {
                            $extension = 'jpg';
                        }
                        
                        // Создаем безопасное имя файла
                        $safeName = pathinfo($originalName, PATHINFO_FILENAME);
                        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $safeName);
                        $fileName = $safeName . '_' . uniqid() . '.' . $extension;
                        
                        // Сохраняем файл
                        try {
                            $image->move($uploadDir, $fileName);
                            $imageNames[] = $fileName;
                        } catch (\Exception $e) {
                            // Если ошибка при сохранении файла
                            continue;
                        }
                    }
                }
                
                if (!empty($imageNames)) {
                    $formSubmission->setImages($imageNames);
                }
            }

            // Сохраняем
            $em->persist($formSubmission);
            $em->flush();

            return $this->redirectToRoute('app_form_success');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/success', name: 'app_form_success')]
    public function success(): Response
    {
        return $this->render('form/success.html.twig');
    }
}
