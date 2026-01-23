<?php
use PHPUnit\Framework\TestCase;

class ATest extends TestCase
{
    /**
     * @group a
     */
    public function testIt()
    {
        sleep(2);
        $this->assertTrue(true);
    }
}
