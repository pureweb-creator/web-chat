<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
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
        $this->table('user')
            ->addColumn('user_name', 'string', ['limit'=>30])
            ->addColumn('email', 'string', ['limit'=>319])
            ->addColumn('register_date', 'timestamp', ['default'=>'CURRENT_TIMESTAMP'])
            ->addColumn('confirmed', 'boolean', ['default'=>0])
            ->addColumn('confirmation_code', 'string', ['limit'=>5, 'default'=>''])
            ->create();
    }
}