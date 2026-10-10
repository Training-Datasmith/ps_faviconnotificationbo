<?php

class UpgradeModule210Test extends PHPUnit_Framework_TestCase
{
    /** @var Ps_faviconnotificationbo */
    private $module;

    protected function setUp()
    {
        PrestaShopTestState::resetAll();
        $this->module = new Ps_faviconnotificationbo();
    }

    public function testUpgradeDeletesLegacyMetaAndReinstallsTabs()
    {
        $meta = new Meta(7);
        PrestaShopCollection::$items = array($meta);
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 0;
        Language::$ids = array(1);

        $this->assertTrue(upgrade_module_2_1_0($this->module));

        $this->assertSame(array('Meta'), PrestaShopCollection::$constructedEntities);
        $this->assertSame(
            array(array('page', 'like', 'module-ps_faviconnotificationbo-Admin%')),
            PrestaShopCollection::$whereCalls
        );
        $this->assertSame(array(7), Meta::$deletedIds);
        $this->assertCount(1, Tab::$addedTabs);
        $this->assertSame('AdminConfigureFaviconBo', Tab::$addedTabs[0]['class_name']);
    }

    public function testUpgradeReinstallsTabsWhenNoMetaMatches()
    {
        PrestaShopCollection::$items = array();
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 0;

        $this->assertTrue(upgrade_module_2_1_0($this->module));
        $this->assertCount(1, Tab::$addedTabs);
    }

    public function testUpgradeReturnsFalseWhenUninstallTabsFails()
    {
        PrestaShopCollection::$items = array();
        $tab = new Tab();
        $tab->deleteResult = false;
        Tab::$collectionByModule['ps_faviconnotificationbo'] = array($tab);

        $this->assertFalse(upgrade_module_2_1_0($this->module));
        $this->assertSame(array(), Tab::$addedTabs);
    }

    public function testUpgradeReturnsFalseWhenNewTabAddFails()
    {
        PrestaShopCollection::$items = array();
        Tab::$collectionByModule['ps_faviconnotificationbo'] = array();
        Tab::$idFromClassName['AdminConfigureFaviconBo'] = 0;
        Tab::$addReturn = false;

        $this->assertFalse(upgrade_module_2_1_0($this->module));
        $this->assertCount(1, Tab::$addedTabs);
    }

    public function testUpgradeMetaLoopAttemptsEveryRowDespiteFailure()
    {
        $metaOne = new Meta(1);
        $metaOne->deleteResult = false;
        $metaTwo = new Meta(2);
        $metaTwo->deleteResult = true;
        PrestaShopCollection::$items = array($metaOne, $metaTwo);
        Tab::$collectionByModule['ps_faviconnotificationbo'] = array();

        $this->assertFalse(upgrade_module_2_1_0($this->module));
        $this->assertSame(array(1, 2), Meta::$deletedIds);
        $this->assertSame(array(), Tab::$addedTabs);
    }
}
