<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    public function run()
    {
        //
        $data = [
            'username' => 'admin',
            'password' => password_hash('1245', PASSWORD_BCRYPT),
            'email' => 'admin@localhost',
            'role' => 'admin',
            'status' => 1,
            'profile_id' => 1
        ];

        $data_profile = [
            'firstname' => 'Jon Paul',
            'middlename' => 'Mananagna',
            'lastname' => 'Kho',
            'gender' => 'M'
        ];

        $this->db->query('INSERT INTO profiles(firstname,middlename,lastname,gender) VALUES(:firstname:,:middlename:,:lastname:,:gender:);', $data_profile);
        $this->db->query('INSERT INTO users(username,`password`,email,`role`,`status`,profile_id) VALUES(:username:, :password:, :email:, :role:, :status:,:profile_id:)', $data);
    }

}
