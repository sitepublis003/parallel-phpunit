<?php

use PHPUnit\Framework\TestCase;

class FailEverySecondTime extends TestCase
{
    public function test()
    {
        $file = "/tmp/failTheTest";
        if (file_exists($file)) {
            unlink($file);
            $this->assertTrue(false);
        } else {
            file_put_contents($file, "");
        }
    }
}
