<?php

use PHPUnit\Framework\TestCase;

class ParallelPhpUnitTest extends TestCase
{
    public function testRetries()
    {
        $arguments = " --test-suffix FailEverySecondTime.php " . (__DIR__ . "/retries");
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

    public function testStatusCounts()
    {
        $testDir = __DIR__ . "/status";
        $output = $this->runParallelPHPUnit($testDir, 1); // Expect exit 1 due to failures
        $lines = explode("\n", $output);
        $summary = end($lines);

        if (preg_match('/Success: (\d+) Fail: (\d+) Error: (\d+) Skip: (\d+) Incomplete: (\d+) Risky: (\d+) Warning: (\d+) Deprecation: (\d+)/', $summary, $matches)) {
            $this->assertEquals(1, (int)$matches[1]); // SuccessTest
            $this->assertEquals(1, (int)$matches[2]); // FailTest
            $this->assertEquals(1, (int)$matches[3]); // ErrorTest
            $this->assertEquals(1, (int)$matches[4]); // SkipTest
            $this->assertEquals(1, (int)$matches[5]); // IncompleteTest
            $this->assertEquals(1, (int)$matches[6]); // RiskyTest
            $this->assertEquals(0, (int)$matches[7]); // No WarningTest
            $this->assertEquals(0, (int)$matches[8]); // No DeprecationTest
        } else {
            $this->fail("Summary format incorrect: " . $summary);
        }
    }

    public function testLargeStatusCounts()
    {
        $testDir = __DIR__ . "/large";
        $output = $this->runParallelPHPUnit("--test-suffix LargeTest.php " . $testDir, 1); // Expect failures from LargeTest
        $lines = explode("\n", $output);
        $summary = end($lines);

        if (preg_match('/Success: (\d+) Fail: (\d+) Error: (\d+) Skip: (\d+) Incomplete: (\d+) Risky: (\d+) Warning: (\d+) Deprecation: (\d+)/', $summary, $matches)) {
            $this->assertEquals(9, (int)$matches[1]); // successes
            $this->assertEquals(8, (int)$matches[2]); // failures
            $this->assertEquals(8, (int)$matches[3]); // errors
            $this->assertEquals(9, (int)$matches[4]); // skipped
            $this->assertEquals(8, (int)$matches[5]); // incomplete
            $this->assertEquals(8, (int)$matches[6]); // risky
            $this->assertEquals(0, (int)$matches[7]); // No warning
            $this->assertEquals(0, (int)$matches[8]); // No deprecation
        } else {
            $this->fail("Summary format incorrect: " . $summary);
        }
    }

    public function testParallelFiveThreadsCompletesQuickly()
    {
        $testDir = __DIR__ . "/parallel";
        $arguments = "--pu-threads 5 " . $testDir;

        $start = microtime(true);
        $output = $this->runParallelPHPUnit($arguments, 0);
        $duration = microtime(true) - $start;

        $lines = explode("\n", $output);
        $summary = end($lines);

        if (preg_match('/Success: (\d+) Fail: (\d+) Error: (\d+) Skip: (\d+) Incomplete: (\d+) Risky: (\d+) Warning: (\d+) Deprecation: (\d+)/', $summary, $matches)) {
            $this->assertEquals(10, (int)$matches[1]);
            $this->assertEquals(0, (int)$matches[2]);
            $this->assertEquals(0, (int)$matches[3]);
        } else {
            $this->fail("Summary format incorrect: " . $summary);
        }

        $this->assertLessThanOrEqual(4.0, $duration, "Parallel run took too long: {$duration} seconds");
    }

    public function testFatalErrorNotRetriedAndReported()
    {
        $junit = sys_get_temp_dir() . '/parallel-phpunit-fatal.junit';
        if (file_exists($junit)) {
            unlink($junit);
        }

        $testDir = __DIR__ . '/fatal';
        $arguments = "--pu-retries 1 --log-junit $junit " . $testDir;

        $output = $this->runParallelPHPUnit($arguments, 1);

        // 1) Fatal Error should not be retried (no "Retry(" lines)
        $this->assertFalse(strstr($output, 'Retry('));

        // 2) Fatal Errors should be counted as Errors in the summary
        $lines = explode("\n", $output);
        $summary = end($lines);
        $this->assertTrue(strpos($summary, 'Error: 2') !== false, "Expected Error: 2 in summary, got: $summary");

        // 3) JUnit XML should contain the FatalError entry / message
        $this->assertFileExists($junit);
        $xml = file_get_contents($junit);
        // If DOMDocument is available, validate JUnit XML syntax and fail on parse errors.
        if (class_exists('DOMDocument')) {
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $ok = $doc->loadXML($xml);
            if (! $ok) {
                $errors = [];
                foreach (libxml_get_errors() as $e) {
                    $errors[] = trim($e->message) . ' at line ' . $e->line;
                }
                libxml_clear_errors();
                $this->fail('Invalid JUnit XML: ' . implode('; ', $errors));
            }
        }
        $this->assertTrue(strpos($xml, 'FatalError') !== false, 'Expected FatalError entry in junit xml');
        $this->assertTrue(strpos($xml, 'Parse error') !== false || strpos($xml, 'syntax error') !== false, 'Expected fatal message in junit xml');
        $this->assertTrue(strpos($xml, 'Declaration of ChildForTest') !== false || strpos($xml, 'someMethod(): string must be compatible') !== false, 'Expected incompatible interface message in junit xml');
    }
}
