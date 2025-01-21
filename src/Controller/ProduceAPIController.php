<?php

namespace App\Controller;

use App\DTO\ProduceDTO;
use App\Entity\Produce;
use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use App\Service\ProduceStorageService\Enum\UnitEnum;
use App\Service\ProduceStorageService\ProduceStorageService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class ProduceAPIController extends AbstractController
{

    #[Route('/api/produce/all', name: 'produce_index', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{1,225}$/'])] ?string $name = null,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{3,225}$/'])] ?string $type = null,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{1,2}$/'])] ?string $unit = null,
//      The following feature doesn't work, unfortunately, and both $type and $unit are ALWAYS null,
//      Added asserts manually
//        #[MapQueryParameter(resolver: BackedEnumValueResolver::class)] $type = null,
//        #[MapQueryParameter(resolver: BackedEnumValueResolver::class)] UnitEnum $unit = null,
    ): JsonResponse
    {
        // Manually added constraints start
        if (null !== $type && null === ProduceTypeEnum::tryFrom($type)) {
            return $this->generateEnumErrorJsonResponse('type', ProduceTypeEnum::class);
        }

        if (null !== $unit && null === UnitEnum::tryFrom($unit)) {
            return $this->generateEnumErrorJsonResponse('unit', UnitEnum::class);
        }
        // Manually added constraints end

        // query start
        $query = $entityManager
            ->getRepository(Produce::class)
            ->createQueryBuilder('p');

        if (null !== $name) {
            $query
                ->andWhere('LOWER(p.name) LIKE LOWER(:name)')
                ->setParameter('name', '%' . $name . '%')
            ;
        }

        if (null !== $type) {
            $query
                ->andWhere('p.type = :type')
                ->setParameter('type', $type);
        }

        $produce = $query
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
        // query end

        $data = [];

        $unitEnum = null !== $unit ? UnitEnum::tryFrom($unit) : UnitEnum::GRAM;

        foreach ($produce as $item) {

            $data[] = (new ProduceDTO())
                ->fillFromEntity($item)
                ->setUnit($unitEnum) // convenient conversion to other units, only kg for now
                ->serialize();

        }

        $success = true;

        return new JsonResponse(compact('data', 'success'));
    }

    # insecure, for testing purposes only
    # ideally POST for dynamic data, but for now GET to testing purposes
    #[Route('/api/produce/load', name: 'produce_load', methods: ['GET'])]
    public function load(EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $request = file_get_contents($path = $this->getParameter('kernel.project_dir') . '/request.json');

        $storageService = new ProduceStorageService($request);

        // validation start
        try {
            $collection = $storageService->process($validator, ProduceDTO::class);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        // validation end

        foreach ($collection->list() as $produceDTO) {
            $produce = Produce::createFromDTO($produceDTO);
            $entityManager->persist($produce);
        }

        $entityManager->flush();

        // imitating response for a successful POST request
        return new JsonResponse([
            'success' => true,
        ], Response::HTTP_CREATED);
    }

    # ideally, should be POST, but GET for testing purposes
    #[Route('/api/produce/add', name: 'produce_add', methods: ['GET'])]
    public function add(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_INT, options: ['min_range' => 1])] int $id,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{3,225}$/'])] string $name,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_INT, options: ['min_range' => 1])] int $quantity,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{3,225}$/'])] string $type,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w{1,2}$/'])] string $unit,
    ): JsonResponse
    {
        // Manually added constraints start
        if (null === ProduceTypeEnum::tryFrom($type)) {
            return $this->generateEnumErrorJsonResponse('type', ProduceTypeEnum::class);
        }

        if (null === UnitEnum::tryFrom($unit)) {
            return $this->generateEnumErrorJsonResponse('unit', UnitEnum::class);
        }
        // Manually added constraints end

        // checking we can add: start
        $produce = $entityManager
            ->getRepository(Produce::class)
            ->findByExternalId($id);

        if (null !== $produce) {
            return new JsonResponse([
                'status' => false,
                'error' => 'Produce already exists.'
            ], Response::HTTP_BAD_REQUEST);
        }
        // checking we can add: end

        // validation DTO: start
        $produceDTO = (new ProduceDTO())
            ->setId($id)
            ->setName($name)
            ->setQuantity($quantity * UnitEnum::tryFrom($unit)->getCoefficient())
            ->setType(ProduceTypeEnum::tryFrom($type))
            ->setUnit(UnitEnum::GRAM)
        ;

        $validator->validate($produceDTO);

        // could be also done with a form
        $errors = $validator->validate($produceDTO);

        if (count($errors) > 0) {

            // so far, the first error only should be enough
            $errorString = $errors->get(0)->getMessage();

            return new JsonResponse([
                'success' => false,
                'error' => $errorString
            ], Response::HTTP_BAD_REQUEST);
        }
        // validation DTO: end

        // DTO is valid, time to store the object
        $produce = (new Produce())->createFromDTO($produceDTO);

        $entityManager->persist($produce);

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
        ], Response::HTTP_CREATED);
    }

    # For testing purposes only,
    # Totally insecure, fixtures must be used instead
    #[Route('/api/produce/reset', name: 'produce_reset', methods: ['GET'])]
    public function reset(EntityManagerInterface $entityManager): JsonResponse
    {
        $produce = $entityManager
            ->getRepository(Produce::class)
            ->findAll();

        foreach ($produce as $entity) {
            $entityManager->remove($entity);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]); // 200 since we send a non-empty response, otherwise 204
    }

    public function generateEnumErrorJsonResponse(string $key, string $enumString): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => "'$key' is not in: " . implode(', ', $enumString::values()),
        ], Response::HTTP_BAD_REQUEST);
    }
}