<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Workdays extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('workdays');
        $table->addColumn('date', 'date', ['null' => false])
              ->addColumn('visits', 'integer', ['default' => 0, 'null' => false])
              ->addColumn('completed', 'integer', ['default' => 0, 'null' => false])
              ->addColumn('duration', 'integer', ['default' => 0, 'null' => false])
              ->addIndex(['date'])
              ->create();
    }
}
