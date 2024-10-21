<?php

namespace App\Controller;

use App\Model\ProductFilter;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/product')]
final class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository
    ) {}

    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $sortField = $request->query->get('sort', 'id');
        $sortDirection = $request->query->get('direction', 'asc');

        $filter = new ProductFilter();

        $search = trim($request->query->get('search', ''));
        if ($search !== '') {
            $filter->setSearch($search);
        }

        $minPrice = $request->query->get('minPrice');
        if (!empty($minPrice) && is_numeric($minPrice)) {
            $filter->setMinPrice((float)$minPrice);
        }

        $maxPrice = $request->query->get('maxPrice');
        if (!empty($maxPrice) && is_numeric($maxPrice)) {
            $filter->setMaxPrice((float)$maxPrice);
        }

        $minStock = $request->query->get('minStock');
        if (!empty($minStock) && is_numeric($minStock)) {
            $filter->setMinStock((int)$minStock);
        }

        $maxStock = $request->query->get('maxStock');
        if (!empty($maxStock) && is_numeric($maxStock)) {
            $filter->setMaxStock((int)$maxStock);
        }

        $dateFrom = trim($request->query->get('dateFrom', ''));
        if ($dateFrom !== '') {
            $filter->setDateFrom($dateFrom);
        }

        $dateTo = trim($request->query->get('dateTo', ''));
        if ($dateTo !== '') {
            $filter->setDateTo($dateTo);
        }

        $allowedSortFields = ['id', 'name', 'description', 'price', 'stockQuantity', 'createdDatetime'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'DESC' : 'ASC';

        $queryBuilder = $this->productRepository->createQueryBuilder('p')
            ->orderBy('p.' . $sortField, $sortDirection);

        if ($filter->getSearch() !== null) {
            $searchTerm = trim($filter->getSearch());
            if ($searchTerm !== '') {
                $queryBuilder
                    ->andWhere('p.name LIKE :search OR p.description LIKE :search')
                    ->setParameter('search', '%' . $searchTerm . '%');
            }
        }

        if ($filter->getMinPrice() !== null) {
            $queryBuilder
                ->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $filter->getMinPrice());
        }

        if ($filter->getMaxPrice() !== null) {
            $queryBuilder
                ->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $filter->getMaxPrice());
        }

        if ($filter->getMinStock() !== null) {
            $queryBuilder
                ->andWhere('p.stockQuantity >= :minStock')
                ->setParameter('minStock', $filter->getMinStock());
        }

        if ($filter->getMaxStock() !== null) {
            $queryBuilder
                ->andWhere('p.stockQuantity <= :maxStock')
                ->setParameter('maxStock', $filter->getMaxStock());
        }

        if ($filter->getDateFrom() !== null) {
            try {
                $dateFrom = $filter->getDateFrom() . ' 00:00:00';
                $queryBuilder
                    ->andWhere('p.createdDatetime >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            } catch (\Exception $e) {
            }
        }

        if ($filter->getDateTo() !== null) {
            try {
                $dateTo = $filter->getDateTo() . ' 23:59:59';
                $queryBuilder
                    ->andWhere('p.createdDatetime <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            } catch (\Exception $e) {
            }
        }

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch (\Exception $e) {
            $pagerfanta->setCurrentPage(1);
        }

        return $this->render('product/index.html.twig', [
            'products' => $pagerfanta,
            'limit' => $limit,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'filter' => $filter
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $product->setCreatedDatetimeNow();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product has been created successfully.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'There were errors in the form. Please correct them and try again.');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Product has been updated successfully.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'There were errors in the form. Please correct them and try again.');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Product has been deleted successfully.');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
