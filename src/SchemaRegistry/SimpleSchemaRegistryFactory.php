<?php

namespace App\SchemaRegistry;


use GuzzleHttp\Client;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandler;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class SimpleSchemaRegistryFactory implements SchemaRegistryFactoryInterface
{
    public function __construct(
        private string $schemaRegistryUri,
        private string $schemaRegistryUsername,
        private string $schemaRegistryPassword
    ) {
    }

    public function create(): KafkaSchemaRegistryApiClientInterface
    {
        $baseUri = 'http://schema-registry:8085';
        $username = null;
        $password = null;
        $client = new Client();
        $psr17Factory = new Psr17Factory();

        $httpClient = new HttpClient(
            $client,
            $psr17Factory,
            new ErrorHandler(),
            $this->schemaRegistryUri,
            $this->schemaRegistryUsername,
            $this->schemaRegistryPassword,
        );

        $registry = new KafkaSchemaRegistryApiClient($httpClient);
        return $registry;
    }
}
