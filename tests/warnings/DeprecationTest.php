<?php

use PHPUnit\Framework\TestCase;

class DeprecationTest extends TestCase
{
    public function testDeprecation()
    {
        trigger_error('This is deprecated', E_USER_DEPRECATED);
        $this->assertTrue(true);
    }
}
