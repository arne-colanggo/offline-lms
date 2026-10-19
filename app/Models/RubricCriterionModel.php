<?php

namespace App\Models;

use CodeIgniter\Model;

class RubricCriterionModel extends Model
{
    protected $table         = 'rubric_criteria';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['rubric_id','name','weight','ordering'];
}
