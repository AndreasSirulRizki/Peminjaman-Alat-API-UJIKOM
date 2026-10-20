<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Andreas(Admin)',
                'email'    => 'admin@gmail.com',
                'password' => 'password123',   // plain text — model cast 'hashed' akan otomatis hash
                'role'     => 'admin',
                'no_hp'    => '081234567890',
                'alamat'   => 'Bandung, West Java',
            ],
            [
                'name'     => 'Dias(Petugas)',
                'email'    => 'petugas@gmail.com',
                'password' => 'password123',
                'role'     => 'petugas',
                'no_hp'    => '082345678901',
                'alamat'   => 'Baleendah, Bandung',
            ],
            [
                'name'     => 'Rian(Peminjam)',
                'email'    => 'rian@gmail.com',
                'password' => 'password123',
                'role'     => 'peminjam',
                'no_hp'    => '083456789012',
                'alamat'   => 'Ciparay, Bandung',
            ],
            [
                'name'     => 'Siti Aminah(Peminjam)',
                'email'    => 'siti@gmail.com',
                'password' => 'password123',
                'role'     => 'peminjam',
                'no_hp'    => '084567890123',
                'alamat'   => 'Dayeuhkolot, Bandung',
            ],
            [
                'name'     => 'Eka Pratama(Peminjam)',
                'email'    => 'eka@gmail.com',
                'password' => 'password123',
                'role'     => 'peminjam',
                'no_hp'    => '085678901234',
                'alamat'   => 'Banjaran, Bandung',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
