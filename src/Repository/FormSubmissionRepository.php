<?php

namespace App\Repository;

use App\Entity\FormSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSubmission>
 */
class FormSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSubmission::class);
    }

    // Метод для поиска по email
    public function findByEmail(string $email): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.email = :email')
            ->setParameter('email', $email)
            ->orderBy('f.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Метод для получения последних заявок
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
