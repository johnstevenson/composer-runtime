<?php

namespace JohnStevenson\ComposerRuntime;

use \JohnStevenson\JsonWorks\Document as Document;
use \JohnStevenson\JsonWorks\Utils as Utils;

/**
* The Package class
*
* @author John Stevenson <john-stevenson@blueyonder.co.uk>
*/
class Package
{
    /**
    * @var JohnStevenson\JsonWorks\Document
    */
    public $document;

    /**
    * @var boolean
    */
    public $jsonTabs;

    /**
    * @var string
    */
    public $filename;

    public function __construct($param = null)
    {
        if (is_string($param)) {
            $this->open($param);
        } else {
            $this->document = new Document(null, $this->getSchema());
            if (null !== $param) {
                $this->create($param);
            }
        }
    }

    public function create($values)
    {
        $vendor = Utils::get($values, 'vendor');
        $name = Utils::get($values, 'name');
        $description = Utils::get($values, 'description', $name);
        $author = Utils::get($values, 'author');
        $email = Utils::get($values, 'email');
        $stability = Utils::get($values, 'stability');
        $php = Utils::get($values, 'php', '5.3.2');

        if (!$vendor || !$name) {
            throw new \RuntimeException('Required options missing');
        }

        $this->valueAdd('/name', $vendor.'/'.$name);
        $this->valueAdd('/type', 'library');
        $this->valueAdd('/description', $description);

        if ($author && $email) {
            $this->valueAdd('/authors/0/name', $author);
            $this->valueAdd('/authors/0/email', $email);
        }

        if ($stability) {
            $this->valueAdd('/minimum-stability', $stability);
        }

        $this->linkAdd('require', 'php', '>='.$php);
    }

    public function open($filename)
    {
        if ($json = file_get_contents($filename)) {
            $this->jsonTabs = preg_match('/^\t+["\{\[]/m', $json);
        }

        $this->document = new Document($json, $this->getSchema());
        $this->validate();
        $this->filename = $filename;
    }

    public function save($filename)
    {
        $json = $this->toJson();
        if (!file_put_contents($filename, $json)) {
            throw new \RuntimeException('Unable to write file: '.$filename);
        }

        $this->filename = $filename;
    }

    public function toJson()
    {
        if (!$this->document->toJsonEx($json, true, true, true, $this->jsonTabs)) {
            throw new \RuntimeException($this->document->error);
        }
        return $json;
    }

    public function autoloadAdd($type, $name, $source = '')
    {
        $path = Utils::addToPath('/autoload', $type);

        if ('psr-0' === $type) {
            $path = Utils::addToPath($path, $name);
            $this->valueAdd($path, $source);
        } else {
            $items = array();

            if ($existing = $this->valueGet($path)) {
                $items = (array) $existing;
            }

            $items[] = $name;
            $this->valueAdd($path, array_unique($items));
        }
    }

    public function licenceAdd($license)
    {
        $licenses = array();

        if ($existing = $this->valueGet('/license')) {
            $licenses = (array) $existing;
        }

        $licenses[] = $license;
        $licenses = array_unique($licenses);

        if (1 === count($licenses)) {
            $licenses = $licenses[0];
        }

        $this->valueAdd('/license', $licenses);
    }

    public function linkAdd($type, $package, $version)
    {
        $this->valueAdd(array($type, $package), $version);
    }

    public function linkDelete($type, $package)
    {
        return $this->valueDelete(array($type, $package));
    }

    public function linkGet($type, $package)
    {
        return $this->valueGet(array($type, $package));
    }

    public function repositoryAdd($data)
    {
        $repos = array();

        if ($existing = $this->valueGet('/repositories')) {

            if (is_object($existing)) {
                $repos[] = $existing;
            } else {
                $repos = (array) $existing;
            }
        }

        $repos[] = Utils::encodeDataKeys($data);
        $this->valueAdd('/repositories', Utils::uniqueArray($repos));
    }

    public function valueAdd($path, $value)
    {
        if (!$this->document->addValue($path, Utils::encodeDataKeys($value))) {
            throw new \RuntimeException($this->document->error);
        }
    }

    public function valueDelete($path)
    {
        return $this->document->deleteValue($path);
    }

    public function valueGet($path)
    {
        if ($this->document->getValue($path, $value)) {
            return $value;
        }
    }

    protected function validate()
    {
        if (!$this->document->validate()) {
            throw new \RuntimeException($this->document->error);
        }
    }

    protected function getSchema()
    {
        return __DIR__.'/Schema/package-v4.json';
    }
}
