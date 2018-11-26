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
    'authMechanism'    => 'SCRAM-SHA-256',
    //设置复制集,没有不设置
    //'replica'          => 'rs0'
    
];
```

## config/properties/app.php 添加
```
mongo' => require __DIR__ . DS . 'mongo.php,
'components' => [
        'custom' => [
            // Your package namespace.
            'SwoftMongo\\',

        ],
    ]
```



## useage

```php
\SwoftMongo\MongoDB\Mongo 下面的静态方法直接使用
 eg: $list = Mongo::fetchPagination('article', 10, 0, ['author' => $author]);
```
