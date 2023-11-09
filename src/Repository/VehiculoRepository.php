<?php

namespace App\Repository;

use App\Entity\Vehiculo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehiculo>
 *
 * @method Vehiculo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehiculo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehiculo[]    findAll()
 * @method Vehiculo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiculoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehiculo::class);
    }

//    /**
//     * @return Vehiculo[] Returns an array of Vehiculo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vehiculo
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
		/**
		 * @param string|null $q
		 * @return array <Vehiculo>
		 */
		public function findQuery(string|null $q) : array
		{
				return $this->createQueryBuilder('v')
					->where('v.marca LIKE :q')
					->orWhere('v.modelo LIKE :q')
					->orWhere('v.color LIKE :q')
					->orWhere('v.matricula LIKE :q')
					->setParameter('q', '%' . $q . '%')
					->andWhere('v.venta IS NULL')
					->getQuery()
					->getResult();
		}
		
		/**
		 * @return array <Vehiculo>
		 */
		public function findDisponibles () :  array
		{
		 return $this->createQueryBuilder('v')
			 ->where('v.venta IS NULL')
			 ->getQuery()
			 ->execute();
		}
}
