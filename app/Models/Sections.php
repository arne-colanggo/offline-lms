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
        'teacherid',
        'ordering',
        'created_at',
        'updated_at'
    ];


    public function getListofTeachers()
    {
        return $this->db->table('users s')
            ->select("s.id, CONCAT(p.lastname, ', ', p.firstname) AS teacher")
            ->join('profiles p', 'p.id = s.profile_id')
            ->where('role', 'teacher')
            ->get()->getResultObject();
    }

}
