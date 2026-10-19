<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizDetailModel extends Model
{
    protected $table         = 'quizzes_details';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['quizid', 'questionid'];
    protected $useTimestamps = false;
}
