<?php
use Illuminate\Database\Capsule\Manager as Capsule;
$dotenv = Dotenv\Dotenv::createImmutable($current_dir.'/');
$dotenv->load();

$capsule = new Capsule;
$capsule->addConnection([
   "driver" => "mysql",
   "host" => $_ENV['DB_HOST'],
   "database" => $_ENV['DB_NAME'],
   "username" => $_ENV['DB_USER'],
   "password" => $_ENV['DB_PASS']
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
