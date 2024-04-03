<?php

namespace App\Command;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:rdkafka-consume'
)]
class RdkafkaConsumeCommand extends Command
{
    public function __construct(
        private string $kafkaTopicName
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conf = new Conf();
        $conf->set('client.id', 'php-consumer');
        $conf->set('group.id', 'php-consumer-1');
        $conf->set('metadata.broker.list', 'kafka:9092');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.partition.eof', 'true');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe([$this->kafkaTopicName]);

        while (true) {
            $message = $consumer->consume(10000);

            if (RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err) {
//                echo "partition EOF\n";
                continue;
            } elseif (RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err) {
//                echo "waiting...\n";
                continue;
            } elseif ( (RD_KAFKA_RESP_ERR_NO_ERROR !== $message->err)) {
                echo rd_kafka_err2str($message->err) . PHP_EOL;
                continue;
            }

            printf("Message on %s[%d]@%d: %s\n", $message->topic_name, $message->partition, $message->offset, $message->payload);
            $consumer->commit($message);
        }

        return Command::SUCCESS;
    }
}
