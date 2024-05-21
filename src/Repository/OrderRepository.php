<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Order::class);
        $this->em = $em;
    }

    /**
     * @param ArrayCollection $products
     * @return int
     * @throws \Exception
     */
    public function create(ArrayCollection $products): Order
    {
        $order = new Order();

        if (!empty($products)) {
            foreach ($products as $item) {
                if (empty($item['quantity']) || ($item['quantity'] < 1)) {
                    throw new \Exception("Product quantity must be greater than 0");
                }

                $product = $this->em->getRepository(Product::class)->findOneBy(['id' => $item['id']]);

                if (!empty($product)) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->setProduct($product);
                    $orderProduct->setQuantity($item['quantity']);
                    $order->addOrderProduct($orderProduct);
                }
            }
        }

        if ($order->getOrderProducts()->count() === 0) {
            throw new \Exception("No products ordered");
        }

        $order->setOrderDate(new \DateTime());

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    /**
     * * !! INFO !!
     * Dodatkowa metoda do pobierania produktów oraz ilości dla zamówienia
     * Powiązany komentarz znajduje się w encji Order przy polu orderProducts
     *
     * @param int $orderId
     * @return Order|null
     */
    public function findOrderWithProducts(int $orderId): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderProducts', 'op')
            ->addSelect('op')
            ->leftJoin('op.product', 'p')
            ->addSelect('p')
            ->where('o.id = :id')
            ->setParameter('id', $orderId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
