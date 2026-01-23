<?php

use PHPUnit\Framework\TestCase;

class ParallelPhpUnitTest extends TestCase
{
    public function testRetries()
    {
        $arguments = " --test-suffix FailEverySecondTime.php " . __DIR__;
        file_put_contents("/tmp/failTheTest", "");
        $this->runParallelPHPUnit($arguments, 1);
        file_put_contents("/tmp/failTheTest", "");
        $this->runParallelPHPUnit("--pu-retries 1" . $arguments, 0);
    }

    public function testFiltering()
    {
        $emptyOutput = "Running parallel-phpunit 1.3.0\nSuccess: 0 Fail: 0 Error: 0 Skip: 0 Incomplete: 0 Risky: 0 Warning: 0 Deprecation: 0";
        $testDir = __DIR__ . "/../example";
        $output = $this->runParallelPHPUnit("--filter noTestsFound " . $testDir, 0);
        $this->assertEquals($emptyOutput, $output);
        $output = $this->runParallelPHPUnit("--filter ATest::testIt " . $testDir, 0);
        $lines = explode("\n", $output);
        $this->assertEquals("Success: 1 Fail: 0 Error: 0 Skip: 0 Incomplete: 0 Risky: 0 Warning: 0 Deprecation: 0", end($lines));
        $this->assertFalse(strstr($output, "No tests"));
    }

    private function runParallelPHPUnit($arguments, $expectedExitStatus = 0)
    {
        $command = __DIR__ . "/../bin/parallel-phpunit " . $arguments;
        $output = array();
        $exitStatus = -1;
        exec($command, $output, $exitStatus);
        $this->assertEquals($expectedExitStatus, $exitStatus);

        return implode("\n", $output);
    }
}
