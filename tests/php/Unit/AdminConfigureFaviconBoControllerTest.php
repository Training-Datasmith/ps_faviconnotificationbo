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

        $boolFields = array(
            Ps_faviconnotificationbo::CONFIG_COUNT_ORDER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_CUSTOMER_NOTIFICATION,
            Ps_faviconnotificationbo::CONFIG_COUNT_MSG_NOTIFICATION,
        );
        foreach ($boolFields as $key) {
            $field = $section['fields'][$key];
            $this->assertSame('bool', $field['type']);
            $this->assertSame('isBool', $field['validation']);
            $this->assertSame('intval', $field['cast']);
        }

        $colorFields = array(
            Ps_faviconnotificationbo::CONFIG_FAVICON_BACKGROUND_COLOR,
            Ps_faviconnotificationbo::CONFIG_FAVICON_TXT_COLOR,
        );
        foreach ($colorFields as $key) {
            $field = $section['fields'][$key];
            $this->assertSame('color', $field['type']);
            $this->assertSame('isColor', $field['validation']);
            $this->assertFalse($field['required']);
            $this->assertFalse(array_key_exists('cast', $field));
        }
    }

    public function testConstructDoesNotWriteConfiguration()
    {
        $this->assertSame(array(), Configuration::$updateLog);
        $this->assertSame(array(), Configuration::$deleteLog);
    }
}
