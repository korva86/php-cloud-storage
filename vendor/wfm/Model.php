<?php


namespace wfm;


use RedBeanPHP\R;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{

//    public array $attributes = [];
    public array $errors = [];
    public array $rules = [];
    public array $labels = [];

    public function __construct()
    {
        parent::__construct();
        Db::getInstance();
    }

    public function load($post = true)
    {
        $data = $post ? $_POST : $_GET;
        foreach ($this->attributes as $name => $value) {
            if (isset($data[$name])) {
                $this->attributes[$name] = $data[$name];
            }
        }
    }

    public function getErrors()
    {
        $errors = '<ul>';
        foreach ($this->errors as $error) {
            foreach ($error as $item) {
                $errors .= "<li>{$item}</li>";
            }
        }
        $errors .= '</ul>';
        $_SESSION['errors'] = $errors;
    }

    public function getLabels(): array
    {
        $labels = [];
        foreach ($this->labels as $k => $v) {
            $labels[$k] = $v;
        }
        return $labels;
    }

//    public function save($table): int|string
//    {
//        $tbl = R::dispense($table);
//        foreach ($this->attributes as $name => $value) {
//            if ($value != '') {
//                $tbl->$name = $value;
//            }
//        }
//        return R::store($tbl);
//    }

//    public function update($table, $id): int|string
//    {
//        $tbl = R::load($table, $id);
//        foreach ($this->attributes as $name => $value) {
//            if ($value != '') {
//                $tbl->$name = $value;
//            }
//        }
//        return R::store($tbl);
//    }
}