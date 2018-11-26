<?php

namespace SwoftMongo\Config;


use Swoft\Bean\Annotation\{Bean, Value};
use Swoft\Pool\PoolConfigInterface;

/**
 * @Bean()
 * Class MongoDBPoolConfig
 * @package App\MongoDB\Config
 */
class MongoDBPoolConfig implements PoolConfigInterface
{
    /**
     * @Value(name="${config.mongo.appName}", env="${MONGO_APP_NAME}")
     * @var string 连接名称
     */
    protected $appName = '';

    /**
     * @Value(name="${config.mongo.minActive}", env="${MONGO_MIN_ACTIVE}")
     * @var int 最小连接数
     */
    protected $minActive = 5;

    /**
     * @Value(name="${config.mongo.maxActive}", env="${MONGO_MAX_ACTIVE}")
     * @var  int 最大连接数
     */
    protected $maxActive = 10;

    /**
     * @Value(name="${config.mongo.timeout}", env="${MONGO_TIMEOUT}")
     * @var int 超时等待时间(ms)
     */
    protected $timeout = 20;

    /**
     * @Value(name="${config.mongo.userName}", env="${MONGO_USER_NAME}")
     * @var string 数据库用户名
     */
    protected $userName;

    /**
     * @Value(name="${config.mongo.password}", env="${MONGO_PASSWORD}")
     * @var string 数据库连接密码
     */
    protected $password;

    /**
     * @Value(name="${config.mongo.host}", env="${MONGO_HOST}")
     * @var string 数据库连接host
     */
    protected $host;

    /**
     * @Value(name="${config.mongo.port}", env="${MONGO_PORT}")
     * @var int 数据库连接端口，默认27017
     */
    protected $port = 27017;

    /**
     * @Value(name="${config.mongo.dbName}", env="${MONGO_DB_NAME}")
     * @var string 数据库名称
     */
    protected $databaseName;

    /**
     * @Value(name="${config.mongo.authMechanism}", env="${MONGO_AUTH_MECHAINISM}")
     * @var string mongo 身份连接验证机制
     */
    protected $authMechanism;

    /**
     * @Value(name="${config.mongo.driver}", env="${MONGO_DRIVER}")
     * @var string 数据库驱动
     */
    protected $driver = 'MongoDB';

    /**
     * @Value(name="${config.mongo.replica}", env="${MONGO_REPLICA}")
     * @var string string 数据集，默认为空
     */
    protected $replica = '';


    /**
     * @return int
     */
    public function getMaxActive(): int
    {
        return $this->maxActive;
    }

    /**
     * @param int $maxActive
     */
    public function setMaxActive(int $maxActive)
    {
        $this->maxActive = $maxActive;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getMinActive(): int
    {
        return $this->minActive;
    }

    /**
     * @param int $minActive
     */
    public function setMinActive(int $minActive)
    {
        $this->minActive = $minActive;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     */
    public function setAppName(string $appName)
    {
        $this->appName = $appName;
    }



    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUri(): array
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isUseProvider(): bool
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getBalancer(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getProvider(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxWait(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     */
    public function getAuthMechanism(): string
    {
        return $this->authMechanism;
    }

    /**
     * @param string $authMechanism
     */
    public function setAuthMechanism(string $authMechanism)
    {
        $this->authMechanism = $authMechanism;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxIdleTime(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxWaitTime(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     */
    public function getReplica(): string
    {
        return $this->replica;
    }

    /**
     * @param string $replica
     */
    public function setReplica(string $replica)
    {
        $this->replica = $replica;
    }
}
