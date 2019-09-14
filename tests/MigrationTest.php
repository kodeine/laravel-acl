<?php namespace Kodeine\Acl\Tests;

use Illuminate\Support\Facades\Schema;

class MigrationsTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */

    /** @test */
    public function itCanMigrate()
    {
        $this->migrate();

        foreach ($this->getTablesNames() as $table) {
            $this->assertTrue(Schema::hasTable($table), "The table [$table] not found in the database.");
        }
    }
}
