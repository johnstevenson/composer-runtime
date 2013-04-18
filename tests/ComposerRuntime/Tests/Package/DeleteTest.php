<?php

namespace ComposerRuntime\Tests\Package;

class DeleteTest extends \ComposerRuntime\Tests\Base
{
    /**
    * Tests that a single value is deleted correctly.
    *
    */
    public function testDelete1()
    {
        $filename = $this->getFilename('testDeleteData');
        $this->package->open($filename);

        $result = $this->package->valueDelete('/minimum-stability');
        $this->assertTrue($result);

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that an array value is deleted correctly.
    *
    */
    public function testDelete2()
    {
        $filename = $this->getFilename('testDeleteData');
        $this->package->open($filename);

        $result = $this->package->valueDelete('/license/0');
        $this->assertTrue($result);

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that a link object property is deleted correctly.
    *
    */
    public function testDelete3()
    {
        $filename = $this->getFilename('testDeleteData');
        $this->package->open($filename);

        $result = $this->package->linkDelete('require', 'monolog/monolog');
        $this->assertTrue($result);

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }
}

