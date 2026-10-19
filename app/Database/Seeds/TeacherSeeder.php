<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run()
    {
        //
        $data = [
            'username' => 'teacher',
            'password' => password_hash('teacher', PASSWORD_BCRYPT),
            'email' => 'teacher@localhost.com',
            'role' => 'teacher',
            'status' => 1,
        ];

        $data_profile = [
            'firstname' => 'Teacher',
            'middlename' => 'Mananagna',
            'lastname' => 'Kho',
            'gender' => 'M'
        ];

        $this->db->query('INSERT INTO profiles(firstname,middlename,lastname,gender) VALUES(:firstname:,:middlename:,:lastname:,:gender:);', $data_profile);

        $data['profile_id'] = $this->db->insertID();

        $this->db->query('INSERT INTO users(username,`password`,email,`role`,`status`,profile_id) VALUES(:username:, :password:, :email:, :role:, :status:,:profile_id:)', $data);
    }

}
