<?php

namespace App\DataFixtures;

use App\Entity\Color;
use App\Entity\FormSubmission;
use App\Entity\Shape;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Создаем цвета
        $colorsData = [
            ['name' => 'Красный'],
            ['name' => 'Синий'],
            ['name' => 'Зеленый'],
            ['name' => 'Фиолетовый']
        ];
        
        $colors = [];
        foreach ($colorsData as $colorData) {
            $color = new Color();
            $color->setName($colorData['name']);
            $manager->persist($color);
            $colors[] = $color;
        }

        // Создаем фигуры
        $shapesData = [
            ['name' => 'Треугольник'],
            ['name' => 'Круг'],
            ['name' => 'Квадрат'],
            ['name' => 'Ромб']
        ];
        
        $shapes = [];
        foreach ($shapesData as $shapeData) {
            $shape = new Shape();
            $shape->setName($shapeData['name']);
            $manager->persist($shape);
            $shapes[] = $shape;
        }

        // Создаем администратора
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setName('Администратор');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Создаем обычного пользователя
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setName('Пользователь');
        $user->setRoles(['ROLE_USER']);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'user123'
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Создаем тестовые заявки
        for ($i = 1; $i <= 25; $i++) {
            $submission = new FormSubmission();
            $submission->setText("Тестовое сообщение №{$i}");
            $submission->setEmail("test{$i}@example.com");
            $submission->setColor($colors[array_rand($colors)]);
            $submission->setShape($shapes[array_rand($shapes)]);
            $submission->setImages(["test_image_{$i}.jpg"]);
            
            $date = new \DateTime();
            $date->modify("-{$i} hours");
            $submission->setCreatedAt($date);
            
            $manager->persist($submission);
        }

        $manager->flush();
    }
}
