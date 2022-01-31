<?php

declare(strict_types=1);

namespace App\Api\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class ResponseFactory
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * JSON API error
     */
    public function error(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], 400);
    }

    /**
     * JSON API response object as JSON
     */
    public function json(Response $response): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($response, 'json'), 200, [], true);
    }
}
