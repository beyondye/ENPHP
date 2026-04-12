<?php
declare(strict_types=1);

namespace system\model;

use system\Model;
use system\model\ModelException;

class Entity
{

    public function __construct(private Model $model)
    {
        $this->model = $model;
    }

    public function save(): bool
    {
        return $this->model->db->upsert($this->model->fillable, $this->model->wheres) > 0;
    }


    public function delete(): bool
    {

        return $this->model->db->delete($this->model->wheres) > 0;
    }

    

}
