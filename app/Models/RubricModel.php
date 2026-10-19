<?php

namespace App\Models;

use CodeIgniter\Model;

class RubricModel extends Model
{
    protected $table = 'rubrics';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'description', 'class_id', 'subject_id', 'created_by'];
}
