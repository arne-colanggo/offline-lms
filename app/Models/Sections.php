<?php

namespace App\Models;

use CodeIgniter\Model;

class Sections extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'grade_level_id',
        'ordering',
        'created_at',
        'updated_at'
    ];

}
