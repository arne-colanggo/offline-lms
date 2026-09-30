<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'lrn',
        'fathersname',
        'mothersname',
        'guardiansname',
        'relationship',
        'parent_guardian_contact',
        'remarks',
        'profile_id'
    ];

}
