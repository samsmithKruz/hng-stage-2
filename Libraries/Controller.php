<?php

namespace App\Libraries;

class Controller
{
    public $model;

    /**
     * Loads a model by requiring its corresponding file and instantiating it.
     *
     * @param string $model The name of the model class to load.
     * @return void
     */
    public function model($model)
    {
        // Require the model file based on the provided $model parameter
        require_once __DIR__ . '/../Models/' . $model . '.php';

        // Instantiate the model class and assign it to $this->model
        $this->model = new $model();
    }
}

?>
