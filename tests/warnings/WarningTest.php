<?php

use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase
{
    public function testWarning()
    {
        trigger_error('This is a user warning', E_USER_WARNING);
        $this->assertTrue(true);
    }
}
