<?php

namespace ComposerRuntime\Tests\Package;

class LinkAddTest extends \ComposerRuntime\Tests\Base
{
    /**
    * Tests that the value is stored correctly.
    *
    */
    public function testLinkRequire1()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->linkAdd('require', 'php', '>=5.5.0');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that a value with forward-slashes is stored correctly.
    *
    */
    public function testLinkRequire2()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->linkAdd('require', 'monolog/monolog', '1.0.*');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }
}

