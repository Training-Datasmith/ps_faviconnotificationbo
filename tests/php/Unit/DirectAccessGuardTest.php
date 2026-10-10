<?php

require_once dirname(__DIR__) . '/Support/GuardProcess.php';

class DirectAccessGuardTest extends PHPUnit_Framework_TestCase
{
    const EXPECTED_GUARD_COUNT = 15;

    /** @var array */
    private static $indexManifest = array(
        'controllers/admin/index.php',
        'controllers/index.php',
        'index.php',
        'tests/index.php',
        'tests/php/phpstan/index.php',
        'translations/index.php',
        'upgrade/index.php',
        'views/index.php',
        'views/js/index.php',
        'views/templates/admin/configure_favicon_bo/helpers/index.php',
        'views/templates/admin/configure_favicon_bo/helpers/options/index.php',
        'views/templates/admin/configure_favicon_bo/index.php',
        'views/templates/admin/index.php',
        'views/templates/hook/index.php',
        'views/templates/index.php',
    );

    public static function setUpBeforeClass()
    {
        GuardProcess::setPhpBinary('php');
    }

    protected function setUp()
    {
        PrestaShopTestState::resetAll();
    }

    public function testEveryIndexPhpIsCovered()
    {
        $discovered = array();
        $root = GuardProcess::getRepoRoot();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getFilename() !== 'index.php') {
                continue;
            }

            $path = $fileInfo->getPathname();
            if (strpos($path, '/vendor/') !== false) {
                continue;
            }

            $discovered[] = substr($path, strlen($root) + 1);
        }

        sort($discovered);

        $this->assertSame(self::EXPECTED_GUARD_COUNT, count($discovered));
        $this->assertSame(self::$indexManifest, $discovered);
    }

    /**
     * @return array
     */
    public function indexGuardProvider()
    {
        $cases = array();
        foreach (self::$indexManifest as $relativePath) {
            $cases[] = array($relativePath);
        }

        return $cases;
    }

    /**
     * @dataProvider indexGuardProvider
     *
     * @param string $relativePath
     */
    public function testIndexGuardExitsBeforeSentinel($relativePath)
    {
        $result = GuardProcess::runIncludeGuard($relativePath);

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertSame('', $result['stderr']);
        $this->assertNotContains(GuardProcess::SENTINEL, $result['stdout']);
    }

    public function testModuleExitsWhenPsVersionMissing()
    {
        $modulePath = GuardProcess::getRepoRoot() . '/ps_faviconnotificationbo.php';
        $script = '<?php' . "\n"
            . '$target = ' . var_export($modulePath, true) . ";\n"
            . 'include $target;' . "\n"
            . 'echo ' . var_export('MODULE_SENTINEL', true) . ';';

        $result = GuardProcess::runInlineScript($script);

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertNotContains('MODULE_SENTINEL', $result['stdout']);
        $this->assertNotContains('Fatal error', $result['stderr']);
    }

    public function testModuleLoadsWhenPsVersionDefined()
    {
        $root = GuardProcess::getRepoRoot();
        $script = '<?php' . "\n"
            . "define('_PS_VERSION_', '8.1.7-test');\n"
            . 'require ' . var_export($root . '/tests/php/stubs/PrestaShopDoubles.php', true) . ";\n"
            . 'require ' . var_export($root . '/ps_faviconnotificationbo.php', true) . ";\n"
            . 'echo ' . var_export('MODULE_LOAD_SENTINEL', true) . ";\n"
            . "echo class_exists('Ps_faviconnotificationbo', false) ? 'CLASS_YES' : 'CLASS_NO';";

        $result = GuardProcess::runInlineScript($script);

        $this->assertSame(0, $result['exitCode']);
        $this->assertContains('MODULE_LOAD_SENTINEL', $result['stdout']);
        $this->assertContains('CLASS_YES', $result['stdout']);
        $this->assertNotContains('Fatal error', $result['stderr']);
    }

    public function testUpgradeScriptExitsWhenPsVersionMissing()
    {
        $upgradePath = GuardProcess::getRepoRoot() . '/upgrade/upgrade-2.1.0.php';
        $script = '<?php' . "\n"
            . '$target = ' . var_export($upgradePath, true) . ";\n"
            . 'include $target;' . "\n"
            . 'echo ' . var_export('UPGRADE_SENTINEL', true) . ';';

        $result = GuardProcess::runInlineScript($script);

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertNotContains('UPGRADE_SENTINEL', $result['stdout']);
        $this->assertNotContains('Fatal error', $result['stderr']);
    }

    public function testUpgradeScriptLoadsWhenPsVersionDefined()
    {
        $root = GuardProcess::getRepoRoot();
        $script = '<?php' . "\n"
            . "define('_PS_VERSION_', '8.1.7-test');\n"
            . 'require ' . var_export($root . '/tests/php/stubs/PrestaShopDoubles.php', true) . ";\n"
            . 'require ' . var_export($root . '/ps_faviconnotificationbo.php', true) . ";\n"
            . 'require ' . var_export($root . '/upgrade/upgrade-2.1.0.php', true) . ";\n"
            . 'echo ' . var_export('UPGRADE_LOAD_SENTINEL', true) . ";\n"
            . 'echo function_exists(' . var_export('upgrade_module_2_1_0', true) . ") ? 'FN_YES' : 'FN_NO';";

        $result = GuardProcess::runInlineScript($script);

        $this->assertSame(0, $result['exitCode']);
        $this->assertContains('UPGRADE_LOAD_SENTINEL', $result['stdout']);
        $this->assertContains('FN_YES', $result['stdout']);
        $this->assertNotContains('Fatal error', $result['stderr']);
    }
}
