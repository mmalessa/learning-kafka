package cmd

import "github.com/spf13/cobra"

var (
	kafkaBrokers string = "kafka:9092"
	kafkaTopic string = "my-super-topic"
	kafkaConsumerGroup string = "go-consumer-1"
	rootCmd = &cobra.Command{
		Use:   "goclient",
		Short: "Go Kafka client",
		Long:  `Go Kafka client`,
	}
)

func Execute() error {
	return rootCmd.Execute()
}
