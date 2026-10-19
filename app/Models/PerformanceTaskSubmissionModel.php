<?php

namespace App\Models;

use CodeIgniter\Model;

class PerformanceTaskSubmissionModel extends Model
{
    protected $table         = 'performance_task_submissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['task_id','lrn','total_score','max_score','feedback','scored_by'];
}
