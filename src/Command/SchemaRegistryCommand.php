<?php

declare(strict_types=1);

namespace App\Command;

use App\SchemaRegistry\SchemaRegistryFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:schema-registry', description: 'App Schema Registry learning')]
class SchemaRegistryCommand extends Command
{
    public function __construct(
        private SchemaRegistryFactoryInterface $schemaRegistryFactory
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemaRegistry = $this->schemaRegistryFactory->create();
        $schemaArray = [
            'type' => 'record',
            'name' => 'something',
            'namespace' => 'whatever.you.want',
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'name',
                    'type' => 'string',
                ],
            ],
        ];
        $schema = json_encode($schemaArray);
        $subjectName = 'some.subject.name';

        $results = $schemaRegistry->registerNewSchemaVersion($subjectName, $schema);
        print_r($results);

        $results = $schemaRegistry->getVersionForSchema($subjectName, $schema);
        printf("Schema version: %s", $results);

        echo PHP_EOL;
        return Command::SUCCESS;
    }
}
