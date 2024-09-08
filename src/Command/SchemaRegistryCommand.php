<?php

declare(strict_types=1);

namespace App\Command;

use App\SchemaRegistry\SchemaRegistrySchemaStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:schema-registry', description: 'App Schema Registry learning')]
class SchemaRegistryCommand extends Command
{
    public function __construct(
        private readonly SchemaRegistrySchemaStorage $schemaStorage
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonSchemaId = 'my_business.json.test_message';
        $this->schemaStorage->registerSchema(
            'JSON',
            $jsonSchemaId,
            $this->getJsonSchemaMessage($jsonSchemaId)
        );
        print_r($this->schemaStorage->retrieveSchema($jsonSchemaId));

        $avroSchemaId = 'my_business.avro.test_message';
        $this->schemaStorage->registerSchema(
            'AVRO',
            $avroSchemaId,
            $this->getAvroSchemaMessage($avroSchemaId)
        );
        print_r($this->schemaStorage->retrieveSchema($avroSchemaId));

        $protobufSchemaId = 'my_business.protobuf.test_message';
        $this->schemaStorage->registerSchema(
            'PROTOBUF',
            $protobufSchemaId,
            $this->getProtobufSchemaMessage($protobufSchemaId)
        );

        return Command::SUCCESS;
    }

    private function getJsonSchemaMessage(string $schemaId): string
    {
        return json_encode([
            '$schema' => 'http://json-schema.org/draft/2019-09/schema#',
            '$id' => $schemaId,
            'title' => 'Test JSON Schema',
            'type' => 'object',
            'properties' => [
                'foo' => ['type' => 'string'],
                'bar' => ['type' => 'string'],
            ],
            'required' => ['foo', 'bar'],
        ]);
    }

    private function getAvroSchemaMessage(string $schemaId): string
    {
        return json_encode([
            "type" => "record",
            "namespace" => $schemaId,
            "name" => $schemaId,
            "fields" => [
                [ "name" => "Name" , "type" => "string" ],
                [ "name" => "Age" , "type" => "int" ]
            ]
        ]);
    }

    private function getProtobufSchemaMessage(string $schemaId): string
    {
        return 'syntax = "proto3"; package example; message Person { string name = 1; int32 id = 2; string email = 3; }';
    }
}
