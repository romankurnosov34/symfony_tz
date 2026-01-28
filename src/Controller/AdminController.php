<?php

namespace App\Controller;

use App\Entity\FormSubmission;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $totalSubmissions = $em->getRepository(FormSubmission::class)->count([]);
        $totalUsers = $em->getRepository(User::class)->count([]);
        
        return $this->render('admin/dashboard.html.twig', [
            'total_submissions' => $totalSubmissions,
            'total_users' => $totalUsers,
        ]);
    }

    #[Route('/admin/submissions', name: 'app_admin_submissions')]
    #[IsGranted('ROLE_ADMIN')]
    public function submissions(EntityManagerInterface $em): Response
    {
        $submissions = $em->getRepository(FormSubmission::class)->findBy([], ['created_at' => 'DESC']);
        
        return $this->render('admin/submissions/index.html.twig', [
            'pagination' => $submissions,
        ]);
    }

    #[Route('/admin/submission/{id}', name: 'app_admin_submission_view')]
    #[IsGranted('ROLE_ADMIN')]
    public function viewSubmission(int $id, EntityManagerInterface $em): Response
    {
        $submission = $em->getRepository(FormSubmission::class)->find($id);
        
        if (!$submission) {
            throw $this->createNotFoundException('Заявка не найдена');
        }
        
        return $this->render('admin/submissions/view.html.twig', [
            'submission' => $submission,
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'pagination' => $users,
        ]);
    }

    #[Route('/admin/users/new', name: 'app_admin_user_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', 'Пользователь успешно создан!');
            return $this->redirectToRoute('app_admin_users');
        }
        
        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
