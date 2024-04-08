<?php

namespace App\SchemaRegistry;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;

interface SchemaRegistryFactoryInterface
{
    public function create(): KafkaSchemaRegistryApiClientInterface;
}
