<?php

namespace App\Models;

use CodeIgniter\Model;

class PerformanceTaskScoreModel extends Model
{
    protected $table         = 'performance_task_scores';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['submission_id','criterion_id','level_id','score'];
}
