<?php

define('PROD_ENV', false);

require '../../vendor/autoload.php';
$config = require '../../config/autoload/doctrine.global.php';
$config = $config['doctrine']['connection']['orm_default'];
$driver = $config['driverClass'];

$file = fopen(__DIR__ . '/kirjastot_raportointipalvelu_nimikoodit.csv', 'r');

$db = (new $driver)->connect($config['params'], $config['params']['user'], $config['params']['password']);
$smt = $db->prepare('UPDATE organisations SET helmet_sierra_id = ? WHERE id = ?');

fgets($file);

while ($data = fgetcsv($file, 0, ';')) {
    $smt->execute([$data[2], $data[0]]);
}
