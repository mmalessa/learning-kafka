<?php

declare(strict_types=1);

namespace App\SchemaRegistry;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final class SchemaRegistrySchemaStorage
{
    public const COMPATIBILITY_BACKWARD = 'BACKWARD';
    public const COMPATIBILITY_BACKWARD_TRANSITIVE = 'BACKWARD_TRANSITIVE';
    public const COMPATIBILITY_FORWARD = 'FORWARD';
    public const COMPATIBILITY_FORWARD_TRANSITIVE = 'FORWARD_TRANSITIVE';
    public const COMPATIBILITY_FULL = 'FULL';
    public const COMPATIBILITY_FULL_TRANSITIVE = 'FULL_TRANSITIVE';
    public const COMPATIBILITY_NONE = 'NONE';

    public function __construct(
        private readonly Client $httpClient
    ) {}

    public function retrieveSchema(string $schemaId): object
    {
        $response = $this->httpClient
            ->get(
                sprintf('/subjects/%s/versions/latest/schema', $schemaId),
                [
                    RequestOptions::HEADERS => ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    RequestOptions::HTTP_ERRORS => false,
                ]
            );

        if (200 !== $response->getStatusCode()) {
            throw new \Exception(
                sprintf('Unable to retrieve schema for schemaId "%s" (HTTP %d)', $schemaId, $response->getStatusCode())
            );
        }

        return (object) json_decode($response->getBody()->getContents(), false);
    }

    public function registerSchema(string $type, string $schemaId, string $schema): void
    {
        $this->setCompatibility($schemaId, self::COMPATIBILITY_NONE);
        $this->httpClient->post(
            sprintf('/subjects/%s/versions', $schemaId),
            [
                'headers' => ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                'json' => [
                    'schemaType' => $type,
                    'schema' => $schema,
                ],
            ]
        );
    }

    private function setCompatibility(string $subjectName, string $compatibility): void
    {
        $this->httpClient->put(
            sprintf('/config/%s', $subjectName),
            [
                'headers' => ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                'json' => [
                    'compatibility' => $compatibility,
                ],
            ]
        );
    }

    public function removeSchema(string $schemaId): void
    {
        $this->httpClient->delete(
            sprintf('/subjects/%s', $schemaId)
        );
    }
}
