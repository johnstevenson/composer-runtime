<?php

namespace ComposerRuntime\Tests\Package;

class RepositoryAddTest extends \ComposerRuntime\Tests\Base
{
    /**
    * Tests that an object is replaced as an array of objects.
    *
    */
    public function testRepository1()
    {
        $filename = $this->getFilename('testRepositoryObject');
        $this->package->open($filename);

        $repo = array (
            'type' => 'vcs',
            'url' => 'https://github.com/bloggs/test'
        );
        $this->package->repositoryAdd($repo);

        $expected = $this->getExpected(__FUNCTION__);
        $this->assertEquals($expected, $this->package->toJson());
    }

    /**
    * Tests that duplicate data is not stored
    *
    */
    public function testRepository2()
    {
        $filename = $this->getFilename('testRepository1');
        $this->package->open($filename);

        $repo = array (
            'type' => 'vcs',
            'url' => 'https://github.com/bloggs/test'
        );
        $this->package->repositoryAdd($repo);

        $expected = $this->getExpected('testRepository1');
        $this->assertEquals($expected, $this->package->toJson());
    }
}

