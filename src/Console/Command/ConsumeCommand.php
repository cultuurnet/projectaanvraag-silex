<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use Knp\Command\Command;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

/**
 * Provides a consume command.
 * @codeCoverageIgnore
 */
class ConsumeCommand extends Command
{
    /**
     * @var string
     */
    protected $consumerId;

    /**
     * @var string
     */
    protected $connectionId;

    /**
     * ConsumeCommand constructor.
     * @param null|string $name
     * @param $connectionId
     * @param $consumerId
     */
    public function __construct($name, $connectionId, $consumerId)
    {
        parent::__construct($name);
        $this->connectionId = $connectionId;
        $this->consumerId = $consumerId;
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->addOption('memory-limit', 'l', InputOption::VALUE_OPTIONAL, 'Allowed memory for this process', null)
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (defined('AMQP_DEBUG') === false) {
            define('AMQP_DEBUG', (bool) $input->getOption('debug'));
        }

        /** @var ConsumerInterface $consumer */
        $consumer = $this->getInstance($this->consumerId);
        if (!is_null($input->getOption('memory-limit')) && ctype_digit((string) $input->getOption('memory-limit')) && $input->getOption('memory-limit') > 0) {
            $consumer->setMemoryLimit($input->getOption('memory-limit'));
        }

        /** @var AMQPSocketConnection $connection */
        $connection = $this->getInstance($this->connectionId);
        $channel = $connection->channel();

        // Declare the exchange
        $channel->exchange_declare('asynchronous_commands', 'x-delayed-message', false, true, false, false, false, new AMQPTable(['x-delayed-type' => 'direct']));

        // Declare the queue
        $channel->queue_declare('projectaanvraag', false, true, false, false, false, new AMQPTable(['routing_keys' => ['asynchronous_commands']]));

        // Bind the queue to the async_commands exchange
        $channel->queue_bind('projectaanvraag', 'asynchronous_commands', 'asynchronous_commands');

        $output->writeln(' [*] Waiting for messages. To exit press CTRL+C');

        $callback = function ($msg) use ($consumer, $output, $channel) {
            try {
                $output->writeln(' [x] Received ' . $msg->body);
                $consumer->execute($msg);

                /**
                 * Always acknowledge the message, even on failure. This prevents the automatic requeuing of the item.
                 * Requeueing happens in the event subscriber with a delay.
                 */
                // @codingStandardsIgnoreStart
                if (!empty($msg->delivery_info['delivery_tag'])) {
                    $channel->basic_ack($msg->delivery_info['delivery_tag']);
                }
                // @codingStandardsIgnoreEnd
            } catch (\Throwable $e) {
                print $e->getMessage();
            }
        };

        // Consume the queue
        $channel->basic_consume('projectaanvraag', '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * Get a service instance.
     */
    private function getInstance($serviceId)
    {
        $app = $this->getSilexApplication();

        if (!isset($app[$serviceId])) {
            throw new ProviderNotFoundException($serviceId . ' not found');
        }

        return $app[$serviceId];
    }
}
