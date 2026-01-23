<?php

use PHPUnit\Framework\TestCase;

class IncompatibleInterfaceTest extends TestCase
{
    public function testIncompatibleInterface()
    {
        $i = new ChildForTest();
        $this->assertSame('zero', $i->someMethod('test'));
    }
}

class ParentForTest
{
    public function someMethod(): int
    {
        return 0;
    }
}

class ChildForTest extends ParentForTest
{
    public function someMethod(): string
    {
        return 'zero';
    }
}