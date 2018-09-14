# swoft mongo db pool

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
