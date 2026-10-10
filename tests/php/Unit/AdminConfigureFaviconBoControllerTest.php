<?php

class AdminConfigureFaviconBoControllerTest extends PHPUnit_Framework_TestCase
{
    /** @var AdminConfigureFaviconBoController */
    private $controller;

    protected function setUp()
    {
        PrestaShopTestState::resetAll();
        $this->controller = new AdminConfigureFaviconBoController();
    }

    public function testConstructBuildsOptionsForm()
    {
        $this->assertTrue($this->controller->bootstrap);
        $this->assertSame('Configuration', $this->controller->className);
        $this->assertSame('configuration', $this->controller->table);

        $this->assertArrayHasKey('ps_faviconnotificationbo', $this->controller->fields_options);
        $section = $this->controller->fields_options['ps_faviconnotificationbo'];

        $this->assertTrue(is_string($section['title']) && $section['title'] !== '');
        $this->assertTrue(is_string($section['info']) && $section['info'] !== '');
        $this->assertTrue(is_string($section['submit']['title']) && $section['submit']['title'] !== '');

        $expectedKeys = array(
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR,
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR,
        );

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $section['fields']);
        }

        $order = $section['fields'][Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION];
        $this->assertSame('bool', $order['type']);
        $this->assertSame('isBool', $order['validation']);
        $this->assertSame('intval', $order['cast']);

        $background = $section['fields'][Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR];
        $this->assertSame('color', $background['type']);
        $this->assertSame('isColor', $background['validation']);
        $this->assertFalse($background['required']);
        $this->assertFalse(array_key_exists('cast', $background));
    }

    public function testConstructDoesNotWriteConfiguration()
    {
        $this->assertSame(array(), Configuration::$updateLog);
        $this->assertSame(array(), Configuration::$deleteLog);
    }
}
