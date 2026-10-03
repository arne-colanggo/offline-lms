<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeLevel extends Model
{
    protected $table = 'gradelevels';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'id',
        'name',
        'gradenumber',
        'created_at',
        'updated_at'
    ];

}
