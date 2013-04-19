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
        $items = array();
        $items['vendor'] = Utils::get($values, 'vendor');
        $items['name'] = Utils::get($values, 'name');
        $items['type'] = Utils::get($values, 'type', 'library');
        $items['description'] = Utils::get($values, 'description', $items['name']);
        $items['author'] = Utils::get($values, 'author');
        $items['email'] = Utils::get($values, 'email');
        $items['stability'] = Utils::get($values, 'stability');
        $items['php'] = Utils::get($values, 'php', '5.3.2');

        if ($library = $items['vendor'] || $items['name']) {
            $errors = array();

            if (!$items['vendor']) {
                $errors[] = 'vendor';
            }

            if (!$items['name']) {
                $errors[] = 'name';
            }

            if ($errors) {
                $msg = 'Missing required value: '. implode(' and ' , $errors);
                throw new \RuntimeException($msg);
            }

            $this->addValue('/name', $items['vendor'].'/'.$items['name']);
            $this->addValue('/type', $items['type']);
            $this->addValue('/description', $items['description']);
            $this->linkAdd('require', 'php', '>='.$items['php']);

        }

        if ($items['author']) {
            $this->addValue('/authors/0/name', $items['author']);
        }

        if ($items['email']) {
            $this->addValue('/authors/0/email', $items['email']);
        }

        if ($items['stability']) {
            $this->addValue('/minimum-stability', $items['stability']);
        }
    }

    public function open($filename)
    {
        if ($json = file_get_contents($filename)) {
            $this->jsonTabs = preg_match('/^\t+["\{\[]/m', $json);
        }

        $this->document = new Document($json, $this->getSchema());
        $this->validate(true);
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
        $this->document->tidy(true);
        return $this->document->toJson(true, $this->jsonTabs);
    }

    public function validate($lax = true)
    {
        if (!$this->document->validate($lax)) {
            throw new \RuntimeException($this->document->error);
        }
    }

    public function autoloadAdd($type, $name, $source = '')
    {
        $path = Utils::pathAdd('/autoload', $type);

        if ('psr-0' === $type) {
            $path = Utils::pathAdd($path, $name);
            $this->addValue($path, $source);
        } else {
            $items = array();

            if ($existing = $this->getValue($path)) {
                $items = (array) $existing;
            }

            $items[] = $name;
            $this->addValue($path, array_unique($items));
        }
    }

    public function licenceAdd($license)
    {
        $licenses = array();

        if ($existing = $this->getValue('/license')) {
            $licenses = (array) $existing;
        }

        $licenses[] = $license;
        $licenses = array_unique($licenses);

        if (1 === count($licenses)) {
            $licenses = $licenses[0];
        }

        $this->addValue('/license', $licenses);
    }

    public function linkAdd($type, $package, $version)
    {
        $this->addValue(array($type, $package), $version);
    }

    public function linkDelete($type, $package)
    {
        return $this->deleteValue(array($type, $package));
    }

    public function linkGet($type, $package)
    {
        return $this->getValue(array($type, $package));
    }

    public function repositoryAdd($data)
    {
        $repos = array();

        if ($existing = $this->getValue('/repositories')) {

            if (is_object($existing)) {
                $repos[] = $existing;
            } else {
                $repos = (array) $existing;
            }
        }

        $repos[] = Utils::pathDataEncode($data);
        $this->addValue('/repositories', Utils::uniqueArray($repos));
    }

    public function addValue($path, $value)
    {
        if (!$this->document->addValue($path, Utils::pathDataEncode($value))) {
            throw new \RuntimeException($this->document->error);
        }
    }

    public function deleteValue($path)
    {
        return $this->document->deleteValue($path);
    }

    public function getValue($path)
    {
        return $this->document->getValue($path);
    }

    public function hasValue($path, &$value)
    {
        return $this->document->hasValue($path, $value);
    }

    protected function getSchema()
    {
        return __DIR__.'/Schema/package-v4.json';
    }
}
