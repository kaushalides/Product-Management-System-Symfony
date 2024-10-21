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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;

#[Route('/product')]
final class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private readonly ValidatorInterface $validator
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
    #[Route('/export', name: 'app_product_export', methods: ['GET'])]
    public function export(): Response
    {
        $products = $this->productRepository->findAll();

        $csvData = [
            ['Name', 'Description', 'Price', 'Stock Quantity', 'Created Datetime']
        ];

        foreach ($products as $product) {
            $csvData[] = [
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
                $product->getStockQuantity(),
                $product->getCreatedDatetime()->format('Y-m-d H:i:s')
            ];
        }

        $csv = implode("\n", array_map(function ($row) {
            return implode(',', $row);
        }, $csvData));

        $response = new Response($csv);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'products.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }
    #[Route('/import', name: 'app_product_import', methods: ['POST'])]
    public function import(Request $request): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('csv_file');

        if (!$file) {
            $this->addFlash('error', 'No file uploaded.');
            return $this->redirectToRoute('app_product_index');
        }

        if ($file->getClientMimeType() !== 'text/csv' && $file->getClientOriginalExtension() !== 'csv') {
            $this->addFlash('error', 'Please upload a valid CSV file.');
            return $this->redirectToRoute('app_product_index');
        }

        try {
            $handle = fopen($file->getPathname(), 'r');
            if ($handle === false) {
                throw new \RuntimeException('Could not open file');
            }

            fgetcsv($handle);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    if (count($row) < 4) {
                        $errors[] = 'Invalid row format';
                        $errorCount++;
                        continue;
                    }

                    $name = trim($row[0]);
                    $description = trim($row[1]);
                    $price = filter_var($row[2], FILTER_VALIDATE_FLOAT);
                    $stockQuantity = filter_var($row[3], FILTER_VALIDATE_INT);

                    if (empty($name) || $price === false || $stockQuantity === false) {
                        $errors[] = "Invalid data in row: " . implode(', ', $row);
                        $errorCount++;
                        continue;
                    }

                    $product = new Product();
                    $product->setName($name);
                    $product->setDescription($description);
                    $product->setPrice($price);
                    $product->setStockQuantity($stockQuantity);
                    $product->setCreatedDatetimeNow();

                    $validationErrors = $this->validator->validate($product);
                    if (count($validationErrors) > 0) {
                        $errors[] = "Validation failed for row: " . implode(', ', $row);
                        $errorCount++;
                        continue;
                    }

                    $this->entityManager->persist($product);
                    $successCount++;

                    if ($successCount % 100 === 0) {
                        $this->entityManager->flush();
                        $this->entityManager->clear();
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing row: " . $e->getMessage();
                    $errorCount++;
                }
            }

            fclose($handle);

            $this->entityManager->flush();

            if ($successCount > 0) {
                $this->addFlash('success', sprintf('%d products imported successfully.', $successCount));
            }
            if ($errorCount > 0) {
                $this->addFlash('error', sprintf('%d rows failed to import.', $errorCount));
                foreach (array_slice($errors, 0, 5) as $error) {
                    $this->addFlash('error', $error);
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred during import: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_product_index');
    }
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

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
    public function delete(Request $request, string $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Product has been deleted successfully.');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
