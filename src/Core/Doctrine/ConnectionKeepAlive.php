<?php

namespace CultuurNet\ProjectAanvraag\Core\Doctrine;

use Doctrine\DBAL\Connection;

declare(ticks=3000000);

/**
 * Provides a class to keep connections alive.
 */
class ConnectionKeepAlive
{
    /**
     * @var Connection[]
     */
    protected $connections;
    protected $isAttached;

    /**
     * Construct the ConnectionKeepAlive.
     */
    public function __construct()
    {
        $this->connections = [];
        $this->isAttached = false;
    }

    /**
     * Detach the keep alive.
     */
    public function detach()
    {
        unregister_tick_function([$this, 'kick']);
        $this->isAttached = false;
    }

    /**
     * Attach the keep alive.
     */
    public function attach()
    {
        if ($this->isAttached || register_tick_function([$this, 'kick'])) {
            $this->isAttached = true;

            return;
        }
        throw new \RuntimeException('Unable to attach keep alive to the system');
    }

    /**
     * Check if the connection exists.
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Add a connection.
     *
     * @param string $name
     * @param \Doctrine\DBAL\Connection $logConnection
     */
    public function addConnection(string $name, Connection $logConnection)
    {
        $this->connections[$name] = $logConnection;
    }

    /**
     * Execute a query every tick to keep connection alive.
     */
    public function kick()
    {
        foreach ($this->connections as $conn) {
            try {
                $conn->executeQuery('SELECT 1')->closeCursor();
            } catch (\Exception $e) {
                if ($conn === null || stripos($e->getMessage(), 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') === false) {
                    throw $e;
                }
                $conn->close();
                $conn->connect();
            }
        }
    }
}
