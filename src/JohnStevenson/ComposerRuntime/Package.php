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

    /**
    * @var string
    */
    public $error;

    /**
    * @var boolean
    */
    public $throwError;

    public function __construct($throwError = false)
    {
        $this->throwError = $throwError;
    }

    public function create($values)
    {
        $this->init();

        $items = array();
        $items['vendor'] = Utils::get($values, 'vendor');
        $items['name'] = Utils::get($values, 'name');
        $items['type'] = Utils::get($values, 'type', 'library');
        $items['description'] = Utils::get($values, 'description', $items['name']);
        $items['author'] = Utils::get($values, 'author');
        $items['email'] = Utils::get($values, 'email');
        $items['stability'] = Utils::get($values, 'stability');
        $items['php'] = Utils::get($values, 'php', '5.3.2');
        $items['require'] = Utils::get($values, 'require', array());

        $currentThrow = $this->throwError;
        $this->throwError = true;

        try {

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
                    $this->handleError($msg);
                }

                $this->addValue('/name', $items['vendor'].'/'.$items['name']);
                $this->addValue('/type', $items['type']);
                $this->addValue('/description', $items['description']);
                $this->linkAdd('require', 'php', '>='.$items['php']);

                if ($items['author']) {
                    $this->addValue('/authors/0/name', $items['author']);
                }

                if ($items['email']) {
                    $this->addValue('/authors/0/email', $items['email']);
                }
            }

            if ($items['stability']) {
                $this->addValue('/minimum-stability', $items['stability']);
            }

            foreach ($items['require'] as $package => $version) {
                $this->linkAdd('require', $package, $version);
            }

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }

        $this->throwError = $currentThrow;

        if ($this->error) {
            $this->handleError($this->error);
        }

        return empty($this->error);
    }

    public function open($filename)
    {
        $this->init();

        $filename = $filename ?: getcwd().'/composer.json';
        $filename = strtr($filename, '\\', '/');

        if (!$json = @file_get_contents($filename)) {
            $this->handleError('Unable to open file: '.$filename);
            return false;
        }

        $this->jsonTabs = preg_match('/^\t+["\{\[]/m', $json);

        try {
            $this->document->loadData($json);
        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }

        $this->validate(true);
        $this->filename = $filename;

        return empty($this->error);
    }

    public function save($filename)
    {
        $json = $this->toJson();
        $filename = strtr($filename, '\\', '/');

        if (!@file_put_contents($filename, $json)) {
            $this->handleError('Unable to write file: '.$filename);
        }

        $this->filename = $filename;
        return empty($this->error);
    }

    public function toJson()
    {
        $this->document->tidy(true);
        return $this->document->toJson(true, $this->jsonTabs);
    }

    public function validate($lax)
    {
        if (!$result = $this->document->validate($lax)) {
            $this->handleError($this->document->lastError);
        }

        return $result;
    }

    public function autoloadAdd($type, $name, $source = '')
    {
        $path = Utils::pathAdd('/autoload', $type);

        if ('psr-0' === $type) {
            $path = Utils::pathAdd($path, $name);
            $value = $source;
        } else {
            $items = array();

            if ($existing = $this->getValue($path)) {
                $items = (array) $existing;
            }

            $items[] = $name;
            $value = array_unique($items);
        }

        return $this->addValue($path, $value);
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

        return $this->addValue('/license', $licenses);
    }

    public function linkAdd($type, $package, $version)
    {
        return $this->addValue(array($type, $package), $version);
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

        $repos[] = Utils::dataCopy($data);

        $this->addValue('/repositories', Utils::uniqueArray($repos));

        return empty($this->error);
    }

    public function addValue($path, $value)
    {
        if (!$result = $this->document->addValue($path, $value)) {
            $this->handleError($this->document->lastError);
        }

        return $result;
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

    protected function init()
    {
        $this->error = null;
        $this->document = new Document();

        try {
            $this->document->loadSchema($this->getSchema());
        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }
    }

    protected function handleError($msg)
    {
        if ($this->throwError) {
            throw new PackageException($msg);
        }

        $this->error = $msg;
    }

    protected function getSchema()
    {
        return __DIR__.'/Schema/package-v4.json';
    }
}
