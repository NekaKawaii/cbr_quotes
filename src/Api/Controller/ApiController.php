<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Response\Api\GetCurrencyPairInfoResponse;
use App\Api\Response\ResponseFactory;
use App\ReadModel\ProjectionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path = "/api")
 */
final class ApiController
{
    public function __construct(
        private ProjectionRepository $projectionRepository,
        private ResponseFactory $responseFactory
    ) {
    }

    /**
     * Get info about currency pair
     *
     * @Route(path = "/pair/{base}/{quote}", methods = {"GET"})
     */
    public function getCurrencyPairInfo(string $base, string $quote): JsonResponse
    {
        $pair = $this->projectionRepository->find($base, $quote);

        if ($pair === null) {
            return $this->responseFactory->error('Requested pair not found');
        }

        return $this->responseFactory->json(GetCurrencyPairInfoResponse::fromPair($pair));
    }
}
