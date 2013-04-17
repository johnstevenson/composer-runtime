<?php

namespace ComposerRuntime\Tests\Package;

class LicenseAddTest extends \ComposerRuntime\Tests\Base
{
    /**
    * Tests that single license is stored as a string.
    *
    */
    public function testLicense1()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->licenceAdd('MIT');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that multiple licenses are stored as an array.
    *
    */
    public function testLicense2()
    {
        $filename = $this->getFilename('testLicense1');
        $this->package->open($filename);

        $this->package->licenceAdd('GPL-3.0+');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that duplicate licenses are not stored.
    *
    */
    public function testLicense3()
    {
        $filename = $this->getFilename('testLicense1');
        $this->package->open($filename);

        $this->package->licenceAdd('MIT');

        $expected = $this->getExpected('testLicense1');
        $this->assertEquals($expected, $this->package->toJson());
    }

}

