<?php
use PHPUnit\Framework\TestCase;

class Slow009Test extends TestCase
{
    public function testSleep()
    {
        sleep(1);
        $this->assertTrue(true);
    }
}
