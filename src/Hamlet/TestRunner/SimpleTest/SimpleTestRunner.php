<?php

namespace Hamlet\TestRunner\SimpleTest;

use Exception;
use Hamlet\TestRunner\TestRunnerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;
use SimpleReporter;

class SimpleTestRunner implements TestRunnerInterface
{
    protected $reporter;

    public function __construct(SimpleReporter $reporter)
    {
        $this->reporter = $reporter;
    }

    public function execute($rootDirectoryPath, $className = null)
    {
        $directoryIterator = new RecursiveDirectoryIterator($rootDirectoryPath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $fileIterator = new RegexIterator($recursiveIterator, '/^.+Test\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $success = true;
        foreach ($fileIterator as $file) {
            $name = str_replace('/', '\\', substr($file[0], strlen($rootDirectoryPath), -4));
            if ($className != null && $className != $name) {
                continue;
            }
            try {
                $reflectionClass = new ReflectionClass($name);
                if (!$reflectionClass->isAbstract() and $reflectionClass->isSubclassOf('UnitTestCase')) {
                    $object = $reflectionClass->newInstance();
                    $success = $object->run($this->reporter) and $success;
                }
            } catch (Exception $e) {
                $this->reporter->paintException($e);
                $success = false;
            }
        }
        return $success;
    }
}