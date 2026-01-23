<?php
use PHPUnit\Framework\TestCase;

class FailTest extends TestCase
{
    public function testFail()
    {
        $this->assertTrue(false);
    }
}
