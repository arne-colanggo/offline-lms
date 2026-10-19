<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizModel extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'quizcode',
        'classid',
        'sectionid',
        'schoolyearid',
        'term',
        'quiz_type',
        'show_answer',
        'isactive',
        'shuffle_questions',
        'shuffle_options',
        'time_limit',
        'max_violations',
    ];
    // created_at / updated_at are handled by the database defaults
    protected $useTimestamps = false;
}