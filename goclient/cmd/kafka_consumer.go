package cmd

import (
	"fmt"
	"time"

	"github.com/confluentinc/confluent-kafka-go/v2/kafka"
	"github.com/sirupsen/logrus"
	"github.com/spf13/cobra"
)

func init() {
	rootCmd.AddCommand(consumeCmd)
}

var consumeCmd = &cobra.Command{
	Use:   "consume",
	Short: "Consume topic",
	Long:  `Consume topic`,
	Run: func(cmd *cobra.Command, args []string) {
		logrus.Info("Consume topic: ", kafkaTopic)
		c, err := kafka.NewConsumer(&kafka.ConfigMap{
			"bootstrap.servers": kafkaBrokers,
			"group.id":          kafkaConsumerGroup,
			"auto.offset.reset": "earliest",
		})

		if err != nil {
			panic(err)
		}

		c.SubscribeTopics([]string{kafkaTopic}, nil)

		// 	// A signal handler or similar could be used to set this to false to break the loop.
		run := true

		for run {
			msg, err := c.ReadMessage(time.Second)
			if err == nil {
				fmt.Printf("Message on %s: %s\n", msg.TopicPartition, string(msg.Value))
			} else if !err.(kafka.Error).IsTimeout() {
				// The client will automatically try to recover from all errors.
				// Timeout is not considered an error because it is raised by
				// ReadMessage in absence of messages.
				fmt.Printf("Consumer error: %v (%v)\n", err, msg)
			}
		}

		c.Close()

	},
}

// func consume() {

// 	c, err := kafka.NewConsumer(&kafka.ConfigMap{
// 		"bootstrap.servers": "localhost",
// 		"group.id":          "myGroup",
// 		"auto.offset.reset": "earliest",
// 	})

// 	if err != nil {
// 		panic(err)
// 	}

// 	// A signal handler or similar could be used to set this to false to break the loop.
// 	run := true

// 	for run {
// 		msg, err := c.ReadMessage(time.Second)
// 		if err == nil {
// 			fmt.Printf("Message on %s: %s\n", msg.TopicPartition, string(msg.Value))
// 		} else if !err.(kafka.Error).IsTimeout() {
// 			// The client will automatically try to recover from all errors.
// 			// Timeout is not considered an error because it is raised by
// 			// ReadMessage in absence of messages.
// 			fmt.Printf("Consumer error: %v (%v)\n", err, msg)
// 		}
// 	}

// 	c.Close()
// }
