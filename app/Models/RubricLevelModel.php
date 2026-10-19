<?php

namespace App\Models;

use CodeIgniter\Model;

class RubricLevelModel extends Model
{
    protected $table         = 'rubric_levels';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['rubric_id','label','points','ordering'];
}
