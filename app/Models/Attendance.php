<?php

namespace App\Models;

use CodeIgniter\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'studentid',
        'sectionid',
        'classdate',
        'classtime',
        'attendancestatus'
    ];

}
