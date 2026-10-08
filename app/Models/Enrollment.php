<?php

namespace App\Models;

use CodeIgniter\Model;

class Enrollment extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'sectionid',
        'schoolyearid',
        'studentid',
        'dateenrolled',
        'created_at',
        'updated_at'
    ];


}
