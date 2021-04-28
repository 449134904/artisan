<?php


namespace migration;


use migration\command\db\Table;
use Phinx\Migration\AbstractMigration;

class Migrator extends AbstractMigration
{
    /**
     * @param string $tableName
     * @param array  $options
     * @return Table
     */
    public function table($tableName, $options = [])
    {

        $table = new Table($tableName, $options, $this->getAdapter());
        $this->tables[] = $table;

        return $table;
    }
}
