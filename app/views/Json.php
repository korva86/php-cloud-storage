<?php

namespace app\views;

/**
 * Class Json
 * @package app\views
 */
class Json implements Renderable
{
    private array $data;

    public function __construct( $data)
    {
        $this->data = $data;
    }

    public function render(): void
    {
        header('Content-type: application/json');
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}