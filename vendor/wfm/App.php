<?php


namespace wfm;


use Illuminate\Database\Capsule\Manager as Capsule;

class App
{

    public static $app;

    public function __construct()
    {
        $query = trim(urldecode($_SERVER['QUERY_STRING']), '/');
        new ErrorHandler();
        self::$app = Registry::getInstance();
        $this->getParams();
        Router::dispatch($query);
        $this->dbConnect();
    }

    protected function getParams()
    {
        $params = require_once CONFIG . '/params.php';
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                self::$app->setProperty($k, $v);
            }
        }
    }

    protected function dbConnect()
    {
        $db = require_once CONFIG . '/config_db.php';
        $capsule = new Capsule;
//debug($db, 1);
        $capsule->addConnection([
//            'database' => $db['dbname'],
//            'username' => $db['user'],
//            'password' => $db['password'],
//            'charset' => $db['charset'],
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'cloud_storage',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

}