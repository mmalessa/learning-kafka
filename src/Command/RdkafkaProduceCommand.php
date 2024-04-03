<?php

namespace App\Command;

use RdKafka\Message;
use RdKafka\Producer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RdKafka\Conf;


#[AsCommand(
    name: 'app:rdkafka-produce'
)]
class RdkafkaProduceCommand extends Command
{
    public function __construct(
        private string $kafkaTopicName
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Send Test Message");

        $conf = new Conf();
        $conf->set('client.id', 'test-producer');
        $conf->set('metadata.broker.list', 'kafka:9092');
        $conf->set('message.timeout.ms', '5000');
        $conf->set('log_level', (string) LOG_DEBUG);

        $conf->setDrMsgCb(function (Producer $producer, Message $message){
            if(RD_KAFKA_RESP_ERR_NO_ERROR !== $message->err) {
                $errorMsg = rd_kafka_err2str($message->err);
                printf("FAILED: %s\n", $errorMsg);
                return;
            }
            echo "SUCCESS\n";
        });

        $producer = new Producer($conf);

        $messagePayload = sprintf('Random word from PHP: %s', $this->getRandomWord(rand(4,10)));
        $messageKey = 'test-key-1';
        $messageHeaders = [];

        $topic = $producer->newTopic($this->kafkaTopicName);

        $topic->producev(
            RD_KAFKA_PARTITION_UA,
            RD_KAFKA_MSG_F_BLOCK,
            $messagePayload,
            $messageKey,
            $messageHeaders
        );

        $producer->poll(0);

        $result = $producer->flush(20000);
        if(RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            $output->writeln("Problem with shutdown");
        }

        return Command::SUCCESS;
    }

    private function getRandomWord($len = 10): string {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
}
