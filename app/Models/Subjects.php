<?php

namespace App\Models;

use CodeIgniter\Model;

class Subjects extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'description',
        'grade_level_id',
        'created_at',
        'updated_at'
    ];

}
