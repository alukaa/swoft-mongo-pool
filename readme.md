# swoft mongo db pool

```
composer require alukaa/swoft-mongo-pool
```

## config 
create new file in config/properties. named mongo.php
```php
return [
    'appName'          => 'mongodb-swoole-connection',
    'minActive'        => 5,
    'maxActive'        => 8,
    'userName'         => 'adminUser',
    'password'         => 'adminPass',
    'host'             => 'mongodb',
    'port'             => 27017,
    'dbName'           => 'test',
    'authMechanism'    => 'SCRAM-SHA-256'
];

```
add ``` 'mongo' => require __DIR__ . DS . 'mongo.php',``` to ```config/properties/app.php```


## useage

```php
\SwoftMongo\MongoDB\Mongo 下面的静态方法直接使用
 eg: $list = Mongo::fetchPagination('article', 10, 0, ['author' => $author]);
```
