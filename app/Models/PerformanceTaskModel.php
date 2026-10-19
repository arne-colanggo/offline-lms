<?php

namespace App\Models;

use CodeIgniter\Model;

class PerformanceTaskModel extends Model
{
    protected $table         = 'performance_tasks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['class_id','bow_details_id','rubric_id','title','instructions','term','due_date','is_published','created_by'];
}
