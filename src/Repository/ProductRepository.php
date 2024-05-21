<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $manager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        parent::__construct($registry, Product::class);
        $this->manager = $manager;
    }

    public function create($name, $description, $price)
    {
        $product = new Product();

        $product->setName($name);
        $product->setDescription($description);
        $product->setPrice($price);

        $this->manager->persist($product);
        $this->manager->flush();
    }
}
