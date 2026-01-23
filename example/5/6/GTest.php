<?php
use PHPUnit\Framework\TestCase;

class GTest extends TestCase
{
    public function testIt()
    {
        sleep(2);
        $this->assertTrue(true);
    }
}
