<?php

namespace ComposerRuntime\Tests;

class Base extends \PHPUnit_Framework_TestCase
{
    public $package;
    public $values;

    public function setUp()
    {
        $this->package = new \JohnStevenson\ComposerRuntime\Package();
        $this->values = array(
            'vendor' => 'bloggs',
            'name' => 'test',
            'author' => 'Fred Bloggs',
            'email' => 'fred@somewhere.org',
            'description' => 'Package Test',
        );
    }

    public function getExpected($test)
    {
        return file_get_contents($this->getFilename($test));
    }

    public function getFilename($test)
    {
        return __DIR__.'/Package/Fixtures/'.$test.'.json';
    }

}

