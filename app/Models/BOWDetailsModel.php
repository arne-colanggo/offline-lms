<?php

namespace App\Models;

use CodeIgniter\Model;

class BOWDetailsModel extends Model
{
    protected $table = 'bow_details';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['competencycode', 'bowid', 'learning_objectives', 'week'];
    protected $useTimestamps = false;

    public function forBow(int $bowId): array
    {
        return $this->where('bowid', $bowId)
            ->orderBy('week', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
