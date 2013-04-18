<?php

namespace ComposerRuntime\Tests;

class Base extends \PHPUnit_Framework_TestCase
{
    public $package;
    public $values;
    public $tmpFiles;

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

        $this->tmpFiles = array();
    }

    public function tearDown()
    {
        foreach ($this->tmpFiles as $tmpFile) {
            @unlink($tmpFile);
        }
    }

    public function getExpected($test, $tabs = false)
    {
        $filename = $this->getFilename($test);

        if ($tabs) {
            $data = file($filename, FILE_IGNORE_NEW_LINES);
            return $this->fileSpacesToTabs($data);
         } else {
            return file_get_contents($filename);
        }
     }

    public function getFilename($test, $tabs = false)
    {
        return __DIR__.'/Package/Fixtures/'.$test.'.json';
    }

    public function getTabFilename($test)
    {
        $data = $this->getExpected($test, true);
        $tmp = tempnam(sys_get_temp_dir(), 'test');

        if (!$fh = fopen($tmp, "w")) {
            throw new \Exception('Cannot create tmp file');
        }

        fwrite($fh, $data);
        fclose($fh);
        $this->tmpFiles[] = $tmp;

        return $tmp;
    }

    protected function fileSpacesToTabs($data)
    {
        $space = str_repeat(chr(32), 4);

        foreach($data as &$line)
        {
            $tabs = '';

            while (0 === strpos($line, $space)) {
                $line = substr($line, 4);
                $tabs .= "\t";
            }

            $line = $tabs.$line;
        }

        return implode("\n", $data)."\n";
    }


}

