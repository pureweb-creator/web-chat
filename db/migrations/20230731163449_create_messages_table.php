<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMessagesTable extends AbstractMigration
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
        if ($this->hasTable('message'))
            $this->table('message')->drop()->save();

        $this->table('message')
            ->addColumn('message_text', 'string', ['limit'=>4096])
            ->addColumn('message_pub_date', 'timestamp', ['collation'=>'utf8mb4_general_ci', 'default'=>'CURRENT_TIMESTAMP'])
            ->addColumn('message_to', 'biginteger')
            ->addColumn('message_from', 'biginteger')
            ->save();
    }
}
