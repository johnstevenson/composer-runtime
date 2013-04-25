<?php

namespace ComposerRuntime\Tests\Package;

class JsonTest extends \ComposerRuntime\Tests\Base
{
    /**
    * Tests that a tabbed json file is saved with tabs.
    *
    */
    public function testJsonWithTabs()
    {
        $data = $this->getExpected('testJsonData', true);
        $filename = $this->getTempFile($data);

        $this->package->open($filename);
        @unlink($filename);

        $expected = $this->getExpected('testJsonData', true);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that empty items are removed from output
    *
    */
    public function testJsonEmptyItem()
    {
        $filename = $this->getFilename('testJsonData');
        $this->package->open($filename);

        $this->package->linkDelete('require', 'php');
        $this->package->linkDelete('require', 'monolog/monolog');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that items are ordered correctly
    *
    */
    public function testJsonOrder()
    {
        $filename = $this->getFilename(__FUNCTION__);
        $this->package->open($filename);

        $expected = $this->getExpected('testJsonData');
        $this->assertEquals($expected, $this->package->toJson());
    }
}

