<?php
use PHPUnit\Framework\TestCase;

class Slow010Test extends TestCase
{
    public function testSleep()
    {
        sleep(1);
        $this->assertTrue(true);
    }
}
