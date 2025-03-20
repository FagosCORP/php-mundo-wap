<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class Visits extends AbstractMigration
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
        $table = $this->table('visits');
        $table->addColumn('date', 'date', ['null' => false])
            ->addColumn('completed', 'boolean', ['default' => 0, 'null' => false])
            ->addColumn('forms', 'integer', ['null' => false])
            ->addColumn('products', 'integer', ['null' => false])
            ->addColumn('duration', 'integer', ['default' => 0, 'null' => false])
            ->addIndex(['date'])
            ->create();
    }
}
