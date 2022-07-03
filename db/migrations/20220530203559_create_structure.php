<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStructure extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if ($this->hasTable('user'))
            $this->table('user')->drop()->save();

        $this->table('user')
            ->addColumn('user_name', 'string', ['limit'=>30, 'null'=>false])
            ->addColumn('email', 'string', ['null'=>false, 'limit'=>255])
            ->addColumn('register_date', 'timestamp', ['default'=>'CURRENT_TIMESTAMP'])
            ->create();

        if ($this->hasTable('message'))
            $this->table('message')->drop()->save();

        $this->table('message')
            ->addColumn('message_text','string', ['limit'=>4096, 'null'=>false])
            ->addColumn('user_id', 'smallinteger', ['null'=>false])
            ->addColumn('user_name', 'string', ['limit'=>30, 'null'=>false])
            ->addColumn('message_pub_date', 'timestamp', ['default'=>'CURRENT_TIMESTAMP'])
            ->create();
    }
}
