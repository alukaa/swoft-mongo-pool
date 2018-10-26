<?php

namespace App\Lib\MongoDB;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\{
    BulkWrite, Command, Manager, Query, WriteConcern
};
use MongoDB\Driver\Exception\{
    AuthenticationException, ConnectionException, Exception, InvalidArgumentException, RuntimeException
};

use Swoft\App;
use Swoft\Db\Bean\Annotation\Connection;
use Swoft\Db\Driver\DriverType;
use Swoft\Pool\AbstractConnection;


/**
 * @Connection(driver="MongoDB", type=DriverType::SYNC)
 *
 * Class MongoDBConnection
 * @package App\MongoDB
 */
class MongoDBConnection extends AbstractConnection
{
    /**
     * @var Manager
     */
    private $connection;

    /**
     * Create connectioin
     *
     * @return void
     * @throws MongoDBException
     */
    public function createConnection()
    {
        try {
            /**
             * http://php.net/manual/zh/mongodb-driver-manager.construct.php
             */
            $poolConfig = $this->pool->getPoolConfig();
            $uri = sprintf(
                'mongodb://%s:%s@%s:%d',
                $poolConfig->getUserName(),
                $poolConfig->getPassword(),
                $poolConfig->getHost(),
                $poolConfig->getPort()
            );
            $this->connection = new Manager($uri);
        } catch (InvalidArgumentException $e) {
            throw MongoDBException::managerError('mongo 连接参数错误');
        } catch (RuntimeException $e) {
            throw MongoDBException::managerError('mongo uri格式错误');
        }
    }

    /**
     * Reconnect
     * @throws MongoDBException
     */
    public function reconnect()
    {
       $this->createConnection();
    }

    /**
     * 判断当前的数据库连接是否已经超时
     *
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws MongoDBException
     */
    public function check(): bool
    {
        try {
            $command = new Command(['ping' => 1]);
            $this->connection->executeCommand('admin', $command);
            return true;
        } catch (\Throwable $e) {
            return $this->catchMongoException($e);
        }
    }

    /**
     * 查询返回结果的全部数据
     *
     * @param string $namespace
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function executeQueryAll(string $namespace, array $filter = [], array $options = [])
    {
        if(!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)){
            $filter['_id'] = new ObjectId($filter['_id']);
        }
        // 查询数据
        $result = [];
        try {
            $query = new Query($filter, $options);
            $cursor = $this->connection->executeQuery($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $query);

            foreach ($cursor as $document) {
                $document = (array)$document;
                $document['_id'] = (string)$document['_id'];
                $result[] = $document;
            }
        } catch (\Exception $e) {
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } catch (Exception $e) {
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } finally {
            $this->pool->release($this);
            return $result;
        }
    }

    /**
     * 返回分页数据，默认每页10条
     *
     * @param string $namespace
     * @param int $limit
     * @param int $currentPage
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function execQueryPagination(string $namespace, int $limit = 10, int $currentPage = 0, array $filter = [], array $options = [])
    {
        if(!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)){
            $filter['_id'] = new ObjectId($filter['_id']);
        }
        // 查询数据
        $data = [];
        $result = [];

        //每次最多返回10条记录
        if (!isset($options['limit']) || (int)$options['limit']<=0) {
            $options['limit'] = $limit;
        }

        if (!isset($options['skip']) || (int)$options['skip']<=0) {
            $options['skip'] = $currentPage*$limit;
        }

        try {
            $query = new Query($filter, $options);
            $cursor = $this->connection->executeQuery($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $query);

            foreach ($cursor as $document) {
                $document = (array)$document;
                $document['_id'] = (string)$document['_id'];
                $data[] = $document;
            }

            $result['totalCount'] = $this->count($namespace, $filter);
            $result['currentPage'] = $currentPage;
            $result['perPage'] = $limit;
            $result['list'] = $data;

        } catch (\Exception $e) {
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } catch (Exception $e) {
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } finally {
            $this->pool->release($this);
            return $result;
        }
    }

    /**
     * 数据插入
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.insert.php
     * $data1 = ['title' => 'one'];
     * $data2 = ['_id' => 'custom ID', 'title' => 'two'];
     * $data3 = ['_id' => new MongoDB\BSON\ObjectId, 'title' => 'three'];
     *
     * @param string $namespace
     * @param array $data
     * @return bool|string
     */
    public function insert(string $namespace, array $data = [])
    {
        try{
            $bulk = new BulkWrite($data);
            $insertId = (string)$bulk->insert($data);
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $bulk, $written);
        }catch (\Exception $e) {
            $insertId = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } finally {
            $this->pool->release($this);
            return $insertId;
        }
    }

    /**
     * 数据更新,效果是满足filter的行,只更新$newObj中的$set出现的字段
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.update.php
     * $bulk->update(
     *   ['x' => 2],
     *   ['$set' => ['y' => 3]],
     *   ['multi' => false, 'upsert' => false]
     * );
     *
     * @param string $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     */
    public function updateRow(string $namespace, array $filter = [], array $newObj = []): bool
    {
        try {
            if(!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)){
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->update(
                $filter,
                $newObj,
                ['multi' => true, 'upsert'=> false]
            );
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $bulk, $written);
            $update = true;
        } catch (\Exception $e) {
            $update = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        }  finally {
            $this->pool->release($this);
            return $update;
        }
    }

    /**
     * 数据更新, 效果是满足filter的行数据更新成$newObj
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.update.php
     * $bulk->update(
     *   ['x' => 2],
     *   [['y' => 3]],
     *   ['multi' => false, 'upsert' => false]
     * );
     *
     * @param string $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     */
    public function updateColumn(string $namespace, array $filter = [], array $newObj = []): bool
    {
        try {
            if(!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)){
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->update(
                $filter,
                ['$set' => $newObj],
                ['multi' => false, 'upsert'=> false]
            );
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $bulk, $written);
            $update = true;
        } catch (\Exception $e) {
            $update = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        }  finally {
            $this->pool->release($this);
            return $update;
        }
    }

    /**
     * 删除数据
     *
     * @param string $namespace
     * @param array $filter
     * @param bool $limit
     * @return bool
     */
    public function delete(string $namespace, array $filter = [], bool $limit = false): bool
    {
        try {
            if(!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)){
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->delete($filter, ['limit' => $limit]);
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->pool->getPoolConfig()->getDatabaseName().'.'.$namespace, $bulk, $written);
            $delete = true;
        } catch (\Exception $e) {
            $delete = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } finally {
            $this->pool->release($this);
            return $delete;
        }
    }

    /**
     * 获取collection 中满足条件的条数
     *
     * @param string $namespace
     * @param array $filter
     * @return bool
     */
    public function count(string $namespace, array $filter = [])
    {
        try {
            $command = new Command([
                'count' => $namespace,
                'query' => $filter
            ]);
            $cursor = $this->connection->executeCommand($this->pool->getPoolConfig()->getDatabaseName(), $command);
            $count = $cursor->toArray()[0]->n;
            return $count;
        } catch (\Exception $e) {
            $count = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } catch (Exception $e) {
            $count = false;
            App::error($e->getFile().$e->getLine().$e->getMessage());
        } finally {
            return $count;
        }
    }

    /**
     * @param \Throwable $e
     * @return bool
     * @throws MongoDBException
     */
    private function catchMongoException(\Throwable $e)
    {
        switch ($e) {
            case ($e instanceof InvalidArgumentException): {
                throw MongoDBException::managerError('mongo argument exception: '.$e->getMessage());
            }
            case ($e instanceof AuthenticationException): {
                throw MongoDBException::managerError('mongo数据库连接授权失败');
            }
            case ($e instanceof ConnectionException): {
                /**
                 * https://cloud.tencent.com/document/product/240/4980
                 * 存在连接失败的，那么进行重连
                 */
                for ($counts = 1; $counts <= 5; $counts++) {
                    try {
                        $this->createConnection();
                    } catch (\Exception $e) {
                        App::error('mongo connection error'. $e->getFile().$e->getLine().$e->getMessage());
                        continue;
                    }
                    break;
                }
                return true;
            }
            case ($e instanceof RuntimeException): {
                throw MongoDBException::managerError('mongo runtime exception: '.$e->getMessage());
            }
            default: {
                throw MongoDBException::managerError('mongo unexpected exception: '.$e->getMessage());
            }
        }
    }
}
