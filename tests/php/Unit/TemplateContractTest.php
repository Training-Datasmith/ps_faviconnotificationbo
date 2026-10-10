<?php

class TemplateContractTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        PrestaShopTestState::resetAll();
    }

    public function testBackOfficeHeaderTemplatePassesHookVariables()
    {
        $path = dirname(dirname(dirname(__DIR__))) . '/views/templates/hook/displayBackOfficeHeader.tpl';
        $contents = file_get_contents($path);
        $this->assertTrue(is_string($contents));

        $this->assertContains('ps_faviconnotificationbo.initialize', $contents);
        $this->assertContains("backgroundColor: '{\$bofaviconBgColor|escape:'javascript'}'", $contents);
        $this->assertContains("textColor: '{\$bofaviconTxtColor|escape:'javascript'}'", $contents);
        $this->assertContains("notificationGetUrl: '{\$bofaviconUrl|escape:'javascript'}'", $contents);
        $this->assertContains('CHECKBOX_ORDER: {$bofaviconOrder|intval}', $contents);
        $this->assertContains('CHECKBOX_CUSTOMER: {$bofaviconCustomer|intval}', $contents);
        $this->assertContains('CHECKBOX_MESSAGE: {$bofaviconMsg|intval}', $contents);
        $this->assertContains('timer: 120000', $contents);
    }

    public function testColorOptionsTemplateExtendsHelper()
    {
        $path = dirname(dirname(dirname(__DIR__))) . '/views/templates/admin/configure_favicon_bo/helpers/options/options.tpl';
        $contents = file_get_contents($path);
        $this->assertTrue(is_string($contents));

        $this->assertContains('{extends file="helpers/options/options.tpl"}', $contents);
        $this->assertContains("\$field['type'] == 'color'", $contents);
        $this->assertContains('type="color"', $contents);
        $this->assertContains('name="{$key}"', $contents);
        $this->assertContains("value=\"{\$field['value']|escape:'html':'UTF-8'}\"", $contents);
        $this->assertContains("$.fn.mColorPicker.defaults.imageFolder = baseDir + 'img/admin/';", $contents);

        $this->assertTrue((bool) preg_match('/\{block name="input"\}(.*?)\{\/block\}/s', $contents, $inputBlock));
        $this->assertTrue((bool) preg_match('/\{block name="after"\}(.*?)\{\/block\}/s', $contents, $afterBlock));
        $this->assertContains('{$smarty.block.parent}', $inputBlock[1]);
        $this->assertContains('{$smarty.block.parent}', $afterBlock[1]);
    }
}
