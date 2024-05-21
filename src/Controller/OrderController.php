<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Pricing\PriceCalculatorCollector;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    private OrderRepository $orderRepository;
    private PriceCalculatorCollector $priceCalculatorCollector;

    public function __construct(OrderRepository $orderRepository, PriceCalculatorCollector $priceCalculatorCollector)
    {
        $this->orderRepository = $orderRepository;
        $this->priceCalculatorCollector = $priceCalculatorCollector;
    }

    #[Route('/', name: 'order_index')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['status' => 'Orders controller'], Response::HTTP_OK);
    }

    #[Route('/add', name: 'order_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $order = null;
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No JSON data'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['products'])) {
            return new JsonResponse(['error' => 'Missing fields'], Response::HTTP_BAD_REQUEST);
        }

        $products = new ArrayCollection($data['products']);

        try {
            $order = $this->orderRepository->create($products);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $orderData = $this->prepareOrderDataResponse($order);

        return new JsonResponse($orderData, Response::HTTP_OK);
    }

    #[Route('/get/{id}', name: 'order_read', methods: 'GET')]
    public function read($id, Request $request): JsonResponse
    {
        $order = $this->orderRepository->findOrderWithProducts($id);

        if (empty($order)) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_BAD_REQUEST);
        }

        $orderData = $this->prepareOrderDataResponse($order);

        return new JsonResponse($orderData, Response::HTTP_OK);
    }

    /**
     * @param Order $order
     * @return array
     */
    private function prepareOrderDataResponse(Order &$order): array
    {
        $prices = $this->priceCalculatorCollector->calculateTotals($order);

        $orderData = [
            'id' => $order->getId(),
            'orderDate' => $order->getOrderDate()->format('Y-m-d H:i:s'),
            'products' => [],
            'prices' => $prices,
        ];

        foreach ($order->getOrderProducts() as $orderProduct) {
            $orderData['products'][] = [
                'productId' => $orderProduct->getProduct()->getId(),
                'productName' => $orderProduct->getProduct()->getName(),
                'description' => $orderProduct->getProduct()->getDescription(),
                'quantity' => $orderProduct->getQuantity(),
                'basePrice' => $orderProduct->getProduct()->getPrice(),
            ];
        }

        return $orderData;
    }
}
