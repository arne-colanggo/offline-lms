<?php

namespace App\Models;

use CodeIgniter\Model;

class Classes extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'sectionid',
        'subjectid',
        'teacherid',
        'name',
        'schoolyearid',
        'created_at',
        'updated_at'

    ];

}
