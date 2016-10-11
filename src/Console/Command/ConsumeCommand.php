<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use Knp\Command\Command;
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

    public function __construct($name, $connectionId, $consumerId)
    {
        parent::__construct($name);
        $this->connectionId = $connectionId;
        $this->consumerId = $consumerId;
    }

    protected function configure()
    {
        $this
            ->addOption('memory-limit', 'l', InputOption::VALUE_OPTIONAL, 'Allowed memory for this process', null)
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (defined('AMQP_DEBUG') === false) {
            define('AMQP_DEBUG', (bool) $input->getOption('debug'));
        }

        $consumer = $this->getInstance($this->consumerId);
        if (!is_null($input->getOption('memory-limit')) && ctype_digit((string) $input->getOption('memory-limit')) && $input->getOption('memory-limit') > 0) {
            $consumer->setMemoryLimit($input->getOption('memory-limit'));
        }

        $connection = $this->getInstance($this->connectionId);
        $channel = $connection->channel();

        $channel->queue_declare('projectaanvraag', false, false, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function ($msg) use ($consumer) {
            try {
                echo " [x] Received ", $msg->body, "\n";
                $consumer->execute($msg);
            } catch (\Throwable $e) {
                print $e->getMessage();
            }
        };

        $channel->basic_consume('projectaanvraag', '', false, true, false, false, $callback);

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
