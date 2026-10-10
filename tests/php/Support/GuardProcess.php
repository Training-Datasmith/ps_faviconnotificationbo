<?php

class GuardProcess
{
    const SENTINEL = 'GUARD_SENTINEL_REACHED';

    /** @var string */
    private static $phpBinary = 'php';

    /**
     * @param string $phpBinary
     */
    public static function setPhpBinary($phpBinary)
    {
        self::$phpBinary = $phpBinary;
    }

    /**
     * @return string
     */
    public static function getRepoRoot()
    {
        return dirname(dirname(dirname(__DIR__)));
    }

    /**
     * @param string $relativePath
     *
     * @return array{exitCode:int,stdout:string,stderr:string}
     */
    public static function runIncludeGuard($relativePath)
    {
        $root = self::getRepoRoot();
        $target = $root . '/' . $relativePath;
        $runner = $root . '/tests/php/Support/guard-runner.php';

        $command = escapeshellarg(self::$phpBinary)
            . ' ' . escapeshellarg($runner)
            . ' ' . escapeshellarg($target)
            . ' ' . escapeshellarg(self::SENTINEL);

        $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $process = proc_open($command, $descriptorSpec, $pipes, $root);
        if (!is_resource($process)) {
            throw new RuntimeException('failed to start guard subprocess');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return array(
            'exitCode' => (int) $exitCode,
            'stdout' => (string) $stdout,
            'stderr' => (string) $stderr,
        );
    }

    /**
     * @param string $scriptBody
     *
     * @return array{exitCode:int,stdout:string,stderr:string}
     */
    public static function runInlineScript($scriptBody)
    {
        $root = self::getRepoRoot();
        $tmp = tempnam(sys_get_temp_dir(), 'psfav-guard-');
        if ($tmp === false) {
            throw new RuntimeException('failed to create temp script');
        }

        file_put_contents($tmp, $scriptBody);

        $command = escapeshellarg(self::$phpBinary) . ' ' . escapeshellarg($tmp);

        $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $process = proc_open($command, $descriptorSpec, $pipes, $root);
        if (!is_resource($process)) {
            unlink($tmp);
            throw new RuntimeException('failed to start inline subprocess');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        unlink($tmp);

        return array(
            'exitCode' => (int) $exitCode,
            'stdout' => (string) $stdout,
            'stderr' => (string) $stderr,
        );
    }
}
