<?php
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testError()
    {
        throw new Exception("Error");
    }
}
