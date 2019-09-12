<?php namespace Kodeine\Acl\Tests\Models;

use Kodeine\Acl\Tests\TestCase;

abstract class ModelsTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->migrate();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
