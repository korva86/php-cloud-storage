<?php


namespace wfm;

use Illuminate\Database\Capsule\Manager as Capsule;
use RedBeanPHP\R;

class Db
{

    use TSingleton;

    private function __construct()
    {
        $db = require_once CONFIG . '/config_db.php';
        R::setup($db['dsn'], $db['user'], $db['password']);
        if (!R::testConnection()) {
            throw new \Exception('No connection to DB', 500);
        }
        R::freeze(true);
        if (DEBUG) {
            R::debug(true, 3);
        }

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $db['host'],
            'database' => $db['dbname'],
            'username' => $db['user'],
            'password' => $db['password'],
            'charset' => $db['charset'],
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }


}