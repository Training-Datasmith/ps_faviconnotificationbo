<?php

class FaviconNotificationModuleTest extends PHPUnit_Framework_TestCase
{
    /** @var Ps_faviconnotificationbo */
    private $module;

    protected function setUp()
    {
        PrestaShopTestState::resetAll();
        $this->module = new Ps_faviconnotificationbo();
    }

    public function testConstructorPublishesOperationalMetadata()
    {
        $this->assertSame('ps_faviconnotificationbo', $this->module->name);
        $this->assertTrue(is_string($this->module->displayName) && $this->module->displayName !== '');
        $this->assertTrue(is_string($this->module->description) && $this->module->description !== '');
        $this->assertSame('8.1.0', $this->module->ps_versions_compliancy['min']);
        $this->assertSame('8.1.7-test', $this->module->ps_versions_compliancy['max']);
    }

    public function testConfigXmlInstallerContract()
    {
        $xmlPath = dirname(dirname(dirname(__DIR__))) . '/config.xml';
        $xml = simplexml_load_file($xmlPath);
        $this->assertInstanceOf('SimpleXMLElement', $xml);
        $this->assertSame('ps_faviconnotificationbo', (string) $xml->name);
        $this->assertSame('1', (string) $xml->is_configurable);
        $this->assertSame('0', (string) $xml->need_instance);
    }

    public function testInstallAbortsWhenParentInstallFails()
    {
        Module::$parentInstallResult = false;

        $this->assertFalse($this->module->install());
        $this->assertSame(array(), Module::$registeredHookCalls);
        $this->assertSame(array(), Configuration::$updateLog);
        $this->assertSame(array(), Tab::$addedTabs);
    }

    public function testInstallAbortsWhenHookRegistrationFails()
    {
        Module::$hookRegistrationResult = false;

        $this->assertFalse($this->module->install());
        $this->assertTrue($this->hookRegistrationContains('displayBackOfficeHeader'));
        $this->assertSame(array(), Configuration::$updateLog);
        $this->assertSame(array(), Tab::$addedTabs);
    }

    public function testInstallAbortsWhenConfigurationWriteFails()
    {
        Configuration::$updateResults[Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION] = false;

        $this->assertFalse($this->module->install());
        $this->assertTrue($this->hookRegistrationContains('displayBackOfficeHeader'));
        $this->assertSame(array(), Tab::$addedTabs);
    }

    public function testInstallCreatesHiddenTabForEveryLanguage()
    {
        Language::$ids = array(2, 5);
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 0;
        $marker = 'opaque-tab-label-marker';
        $this->module->displayName = $marker;

        $this->assertTrue($this->module->install());
        $this->assertTrue($this->hookRegistrationContains('displayBackOfficeHeader'));

        $expectedConfig = array(
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION => '1',
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION => '1',
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION => '1',
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR => '#DF0067',
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR => '#FFFFFF',
        );
        $actualConfig = $this->configurationMapFromUpdateLog();
        ksort($expectedConfig);
        ksort($actualConfig);
        $this->assertSame($expectedConfig, $actualConfig);

        $this->assertCount(1, Tab::$addedTabs);
        $tab = Tab::$addedTabs[0];
        $this->assertSame('AdminConfigureFaviconBo', $tab['class_name']);
        $this->assertSame('ps_faviconnotificationbo', $tab['module']);
        $this->assertTrue($tab['active']);
        $this->assertSame(-1, $tab['id_parent']);
        $this->assertSame(array(2 => $marker, 5 => $marker), $tab['name']);
        $this->assertSame(array(false), Language::$getIdsCalls);
    }

    public function testInstallSkipsTabWhenClassAlreadyExists()
    {
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 42;

        $this->assertTrue($this->module->install());
        $this->assertSame(array(), Tab::$addedTabs);
    }

    public function testInstallReturnsFalseWhenTabAddFails()
    {
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 0;
        Tab::$addReturn = false;

        $this->assertFalse($this->module->install());
        $this->assertCount(1, Tab::$addedTabs);
        $this->assertSame('AdminConfigureFaviconBo', Tab::$addedTabs[0]['class_name']);
        $this->assertSame(-1, Tab::$addedTabs[0]['id_parent']);
    }

    public function testUninstallAbortsWhenParentUninstallFails()
    {
        Module::$parentUninstallResult = false;

        $this->assertFalse($this->module->uninstall());
        $this->assertSame(array(), Configuration::$deleteLog);
        $this->assertSame(array(), Tab::$deletedTabIds);
    }

    public function testUninstallConfigurationAttemptsEveryKeyDespiteFailure()
    {
        Configuration::$deleteResults[Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION] = false;

        $this->assertFalse($this->module->uninstallConfiguration());

        $expectedKeys = array(
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR,
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR,
        );
        $deletedKeys = Configuration::$deleteLog;
        sort($expectedKeys);
        sort($deletedKeys);
        $this->assertSame($expectedKeys, $deletedKeys);
    }

    public function testUninstallRemovesConfigurationAndModuleTabs()
    {
        $tabOne = new Tab();
        $tabTwo = new Tab();
        Tab::$collectionByModule['ps_faviconnotificationbo'] = array($tabOne, $tabTwo);

        $this->assertTrue($this->module->uninstall());

        $deletedKeys = Configuration::$deleteLog;
        sort($deletedKeys);
        $expectedKeys = array(
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR,
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR,
        );
        sort($expectedKeys);
        $this->assertSame($expectedKeys, $deletedKeys);
        $this->assertSame(array($tabOne->id, $tabTwo->id), Tab::$deletedTabIds);
    }

    public function testUninstallTabsAttemptsEveryTabDespiteFailure()
    {
        $tabOne = new Tab();
        $tabOne->deleteResult = false;
        $tabTwo = new Tab();
        $tabTwo->deleteResult = true;
        Tab::$collectionByModule['ps_faviconnotificationbo'] = array($tabOne, $tabTwo);

        $this->assertFalse($this->module->uninstallTabs());
        $this->assertSame(array($tabOne->id, $tabTwo->id), Tab::$deletedTabIds);
    }

    public function testGetContentRedirectsToConfigureController()
    {
        $sentinel = Link::$adminUrlSentinel;

        try {
            $this->module->getContent();
            $this->fail('Expected redirect sentinel');
        } catch (RedirectSentinel $e) {
            $this->assertSame($sentinel, $e->getUrl());
        }

        $this->assertSame(array('AdminConfigureFaviconBo'), Link::$getAdminLinkCalls);
        $this->assertSame(array($sentinel), Tools::$redirectUrls);
    }

    public function testHookAddsJavascriptAndAssignsConfiguration()
    {
        Configuration::$values = array(
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR => 'opaque-bg',
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR => 'opaque-txt',
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION => 'opaque-order',
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION => 'opaque-customer',
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION => 'opaque-msg',
        );

        $this->module->pathUri = 'modules/ps_faviconnotificationbo/';
        $this->module->localPath = '/var/modules/ps_faviconnotificationbo/';
        $fetchSentinel = Smarty::$fetchReturnSentinel;

        $output = $this->module->hookDisplayBackOfficeHeader(array());

        $this->assertSame($fetchSentinel, $output);
        $this->assertSame(
            '/var/modules/ps_faviconnotificationbo/views/templates/hook/displayBackOfficeHeader.tpl',
            Smarty::$lastFetchPath
        );

        $expectedAssignments = array(
            'bofaviconBgColor' => 'opaque-bg',
            'bofaviconTxtColor' => 'opaque-txt',
            'bofaviconOrder' => 'opaque-order',
            'bofaviconCustomer' => 'opaque-customer',
            'bofaviconMsg' => 'opaque-msg',
            'bofaviconUrl' => Link::$adminUrlSentinel,
        );
        $actualAssignments = Smarty::$assignments;
        ksort($expectedAssignments);
        ksort($actualAssignments);
        $this->assertSame($expectedAssignments, $actualAssignments);

        $this->assertCount(1, Controller::$jsFiles);
        $this->assertSame(
            array(
                'modules/ps_faviconnotificationbo/views/js/favico.js',
                'modules/ps_faviconnotificationbo/views/js/ps_faviconnotificationbo.js',
            ),
            Controller::$jsFiles[0]
        );
        $this->assertNotEmpty(Link::$getAdminLinkCalls);
    }

    /**
     * @return bool
     */
    private function hookRegistrationContains($hook)
    {
        foreach (Module::$registeredHookCalls as $call) {
            if (is_array($call)) {
                foreach ($call as $registeredHook) {
                    if ($registeredHook === $hook) {
                        return true;
                    }
                }
            } elseif ($call === $hook) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function configurationMapFromUpdateLog()
    {
        $map = array();
        foreach (Configuration::$updateLog as $entry) {
            $map[$entry[0]] = $entry[1];
        }

        return $map;
    }
}
