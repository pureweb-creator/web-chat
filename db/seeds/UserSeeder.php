<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $faker = Faker\Factory::create();

        for ($i=0; $i<10; $i++){
            $color1 = $faker->randomElement(['#B132FF', '#4E95FF', '#2FFFF3', '#FF3489', '#FF8F51', '#3DFF50']);
            $color2 = \App\Core\Helper::darken_color($color1, 2);

            $data[] = [
                'user_name'=>$faker->name(),
                'email'=>$faker->email(),
                'confirmed'=>true,
                'avatar_color1'=>$color1,
                'avatar_color2'=>$color2
            ];
        }

        $this->table('user')
            ->insert($data)
            ->save();
    }
}
