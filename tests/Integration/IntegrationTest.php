<?php namespace Kodeine\Acl\Tests\Integration;

use Kodeine\Acl\Tests\TestCase;

abstract class IntegrationTest extends TestCase
{

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate();
    }


    public function tearDown()
    {
        parent::tearDown();
    }
}
