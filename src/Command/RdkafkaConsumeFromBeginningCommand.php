<?php

namespace App\Command;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\TopicPartition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:rdkafka-consume-from-beginning'
)]
class RdkafkaConsumeFromBeginningCommand extends Command
{
    public function __construct(
        private string $kafkaTopicName
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Rdkafka Consume From Beginning");

        $conf = new Conf();
        $conf->set('client.id', 'php-consumer');
        $conf->set('group.id', 'php-consumer-from-beginning');
        $conf->set('metadata.broker.list', 'kafka:9092');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.partition.eof', 'true');

        $consumer = new KafkaConsumer($conf);

        $output->writeln(sprintf("Subscribe to topic: %s", $this->kafkaTopicName));

        $low = null;        // The oldest/beginning offset for the partition
        $high = null;       // The newest/end offset for the partition
        $timeout = 10000;   // Timeout in milliseconds for this operation
        $partition = 0;


        $metadata = $consumer->getMetadata(
            false,
            $consumer->newTopic($this->kafkaTopicName),
            $timeout
        );

        $topics = $metadata->getTopics();

        $partitions = 0;
        foreach($topics as $topic) {
            if ($topic->getTopic() === $this->kafkaTopicName) {
                $partitions = count($topic->getPartitions());
                break;
            }
        }

        $assignedTopics = [];
        for ($p = 0; $p < $partitions; $p++) {
            // Query broker for low (oldest/beginning) or high (newest/end) offsets for a partition
            $consumer->queryWatermarkOffsets(
                $this->kafkaTopicName,
                $p,
                $low,
                $high,
                $timeout
            );

            $offset = $low;

            $output->writeln(sprintf(
                "Assign partition #%d with offset #%d (range: %d -> %d)",
                $p,
                $offset,
                $low,
                $high - 1
            ));
            $assignedTopics[] = new TopicPartition($this->kafkaTopicName, $p, $offset);
        }
        $consumer->assign($assignedTopics);

        // Consume
        $output->writeln("Consume...");
        while (true) {
            $message = $consumer->consume(10000);

            if (RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err) {
                continue;
            } elseif (RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err) {
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
