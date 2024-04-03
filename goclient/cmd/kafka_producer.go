package cmd

import (
	"fmt"
	"math/rand"

	"github.com/confluentinc/confluent-kafka-go/v2/kafka"
	"github.com/sirupsen/logrus"
	"github.com/spf13/cobra"
	"github.com/zhexuany/wordGenerator"
)

func init() {
	rootCmd.AddCommand(produceCmd)
}

var produceCmd = &cobra.Command{
	Use:   "produce",
	Short: "Produce some messages",
	Long:  `Produce some messages`,
	Run: func(cmd *cobra.Command, args []string) {
		logrus.Info("Produce somme messages for topic: ", kafkaTopic)

		p, err := kafka.NewProducer(&kafka.ConfigMap{"bootstrap.servers": kafkaBrokers})
		if err != nil {
			panic(err)
		}
		defer p.Close()

		// Delivery report handler for produced messages
		go func() {
			for e := range p.Events() {
				switch ev := e.(type) {
				case *kafka.Message:
					if ev.TopicPartition.Error != nil {
						fmt.Printf("Delivery failed: %v\n", ev.TopicPartition)
					} else {
						fmt.Printf("Delivered message to %v\n", ev.TopicPartition)
					}
				}
			}
		}()

		// Produce messages to topic (asynchronously)
		topic := kafkaTopic
		word := "Random word from GO: " + wordGenerator.GetWord(rand.Intn(6)+4)
		p.Produce(&kafka.Message{
			TopicPartition: kafka.TopicPartition{Topic: &topic, Partition: kafka.PartitionAny},
			Value:          []byte(word),
		}, nil)

		// Wait for message deliveries before shutting down
		p.Flush(15 * 1000)
	},
}
