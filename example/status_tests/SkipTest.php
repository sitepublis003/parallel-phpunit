<?php
use PHPUnit\Framework\TestCase;

class SkipTest extends TestCase
{
    public function testSkip()
    {
        $this->markTestSkipped();
    }
}
