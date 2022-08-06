<?php


use Phinx\Seed\AbstractSeed;

class FirstSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        if ($this->table('user')){
            $data  = [
                [
                    'user_name'=>'lorenzo',
                    'confirmed'=>true,
                    'tel_basic'=>'380509502686',
                    'tel_prettified'=>'+380990652902',
                    'secure_code'=>'122990',
                    'register_date'=>'2022-03-31 14:00:00'
                ]
            ];
            $this->table('user')->insert($data)->update();
        }
    }
}
