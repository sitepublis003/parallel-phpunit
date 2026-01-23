<?php
use PHPUnit\Framework\TestCase;

class BTest extends TestCase
{
    /**
     * @group b
     */
    public function testIt()
    {
        sleep(1);
        $this->assertTrue(true);
    }
}
