<?php

namespace SwoftMongo;

use SwoftMongo\Config\MongoDBPoolConfig;
use Swoft\Bean\Annotation\{Inject, Pool};
use Swoft\Db\Bean\Collector\ConnectionCollector;
use Swoft\Db\Driver\DriverType;
use Swoft\Db\Exception\DbException;
use Swoft\Exception\ConnectionException;
use Swoft\Pool\{ConnectionInterface, PoolConfigInterface, PoolInterface};


/**
 * @Pool(name="mongo")
 *
 * Class MongoDBPool
 * @package App\MongoDB
 */
class MongoDBPool implements PoolInterface
{

    /**
     * @Inject()
     *
     * @var MongoDBPoolConfig
     */
    protected $poolConfig;

    /**
     * @var \SplQueue;
     */
    protected $queue;

    /**
     * 当前的连接数量
     *
     * @var int
     */
    protected $currentCount = 0;


    /**
     * 创建MongoDB数据库连接
     *
     * @return ConnectionInterface
     * @throws DbException
     */
    public function createConnection(): ConnectionInterface
    {
        $driver = $this->poolConfig->getDriver();
        $collector = ConnectionCollector::getCollector();

        /**
         * MongoDB swoole层面上不提供任何有关协程或者异步方面的支持
         * 目前做的只是使用php mongodb extension 实现mongodb 同步返回
         * 通过swoole 可以利用task投递实现异步，这部分留到后面再完善
         *  https://github.com/keaixiaou/zphp/blob/master/ZPHP/Coroutine/Mongo/MongoTask.php
         *  http://rango.swoole.com/archives/265
         */
        $className = $this->getSyncConnectClassName($collector, $driver);
        $connection =  new $className($this);
        if ( !$this->queue ) {
            $this->queue = new \SplQueue();
        }
        $this->queue->push($connection);

        return $connection;
    }

    /**
     * @param array $collector
     * @param string $driver
     * @return string
     * @throws DbException
     */
    private function getSyncConnectClassName(array $collector, string $driver): string
    {
        if (!isset($collector[$driver]) || !isset($collector[$driver][DriverType::SYNC])) {
            throw new DbException('The synchronous driver of ' . $driver . ' is not exist!');
        }

        return $collector[$driver][DriverType::SYNC];
    }

    /**
     * @return PoolConfigInterface
     */
    public function getPoolConfig(): PoolConfigInterface
    {
        return $this->poolConfig;
    }


    /**
     * 获取当前连接池中的可用连接
     *
     * @return ConnectionInterface
     * @throws ConnectionException
     * @throws DbException
     */
    public function getConnection(): ConnectionInterface
    {
        $connection = $this->getConnectionByQueue();

        //判断当前的连接是否已经超时
        if ($connection->check() === false) {
            $connection->reconnect();
        }

        return $connection;
    }

    /**
     * Relesea the connection
     *
     * @param ConnectionInterface $connection
     */
    public function release(ConnectionInterface $connection)
    {
        $connection->updateLastTime();
        $connection->setRecv(true);
        $connection->setAutoRelease(true);

        if ($this->queue->count() < $this->poolConfig->getMaxActive()) {
            $this->queue->push($connection);
        }
    }

    /**
     * 返回当前队列中的一个可用连接
     *
     * @return ConnectionInterface
     * @throws ConnectionException
     * @throws DbException
     */
    protected function getConnectionByQueue(): ConnectionInterface
    {
        if ( !$this->queue ) {
            $this->queue = new \SplQueue();
        }
        if (!$this->queue->isEmpty()) {
            return $this->getOriginalConnection();
        }

        //如果当前并发超过了连接池设置的最大数量，进行提示报错
        if ($this->currentCount >= $this->poolConfig->getMaxActive()) {
            throw new ConnectionException('Connection pool queue is full');
        }

        $connect = $this->createConnection();
        $this->currentCount++;

        return $connect;
    }

    /**
     * Get original connection
     *
     * @return ConnectionInterface
     */
    private function getOriginalConnection(): ConnectionInterface
    {
        return $this->queue->shift();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getConnectionAddress(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @throws \Exception
     */
    public function getTimeout(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

}
