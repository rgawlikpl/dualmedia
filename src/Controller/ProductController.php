<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ProductRepository;

#[Route('/product')]
class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/', name: 'product_index')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['status' => 'Products controller'], Response::HTTP_OK);
    }

    #[Route('/add', name: 'product_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            throw new NotFoundHttpException('No JSON data');
        }

        if (empty($data['name']) || empty($data['price'])) {
            throw new NotFoundHttpException('Missing fields');
        }

        $name = $data['name'];
        $price = $data['price'];
        $description = $data['description'] ?? '';

        $this->productRepository->create($name, $description, $price);

        return new JsonResponse(['status' => 'Product Created'], Response::HTTP_OK);
    }

    #[Route('/get/{id}', name: 'product_read', methods: 'GET')]
    public function read($id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if (empty($product)) {
            throw new NotFoundHttpException('No product found');
        }

        $data = [
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

}
