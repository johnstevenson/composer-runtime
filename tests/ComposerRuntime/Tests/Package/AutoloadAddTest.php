<?php

namespace ComposerRuntime\Tests\Package;

class AutoloadAddTest extends \ComposerRuntime\Tests\Base
{
    public function testAutoloadPsr()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->autoloadAdd('psr-0', 'Monolog', 'src/');
        $this->package->autoloadAdd('psr-0', 'Bloggs\\Utils\\', 'src/');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    public function testAutoloadClassmap()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->autoloadAdd('classmap', 'src/');
        $this->package->autoloadAdd('classmap', 'lib/');
        $this->package->autoloadAdd('classmap', 'src/');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    public function testAutoloadFiles()
    {
        $filename = $this->getFilename('testCreate1');
        $this->package->open($filename);

        $this->package->autoloadAdd('files', 'src/MyLibrary/functions.php');
        $this->package->autoloadAdd('files', 'lib/Utils/functions.php');
        $this->package->autoloadAdd('files', 'src/MyLibrary/functions.php');

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }
}

