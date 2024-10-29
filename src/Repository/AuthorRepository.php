<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    // Méthode pour récupérer tous les auteurs
    public function findAllAuthors(): array
    {
        return $this->findAll();
    }

    // Méthode pour récupérer un auteur à partir de son id

   public function findAuthorById(int $id): ?Author
{
   return $this->find($id); // Utilise la méthode find de Doctrine
}

  //4) DQL
  public function deleteAuthorsWithNoBooks(): int
  {
      // Create a QueryBuilder to delete authors with no books
      $qb = $this->createQueryBuilder('a')
          ->delete()
          ->where('a.nb_books = 0');
  
      return $qb->getQuery()->execute(); // Execute the delete query
  }

  // // 1) Atelier QueryBuilder : Method to list authors ordered by email
  public function listAuthorByEmail(): array
  {
      return $this->createQueryBuilder('a')
          ->orderBy('a.email', 'ASC') //Asc=>ascending
          ->getQuery()
          ->getResult();
  }

//    /**
//     * @return Author[] Returns an array of Author objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Author
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
