<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function searchEngine(string $query){
        // créé un objet de requête qui permet de construire la requête de recherche
        return $this->createQueryBuilder('p')
            // recherche les éléments dont le nom contient la requête de recherche
            ->where('p.name LIKE :query')
            // OU recherche les éléments dont la description contient la requête de recherche
            ->orWhere('p.description LIKE :query')
            // définit la valeur de la variable "query" pour la requête
            ->setParameter('query', '%' .$query .'%')
            // execute la requête et récupère les résultats
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByIdUp($value): array
    //    {
    //        return $this->createQueryBuilder('p')  // retourner la requête
    //            ->andWhere('p.id > :val')  // on ajoute des critères val = $value
    //            ->setParameter('val', $value)  //on set les paramètres
    //            ->orderBy('p.id', 'ASC')   // on déf les critères
    //            ->setMaxResults(10)  
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
