<?php

namespace JohnStevenson\ComposerRuntime;

/**
* The Process class
*
* @author John Stevenson <john-stevenson@blueyonder.co.uk>
*/
class Process
{
    protected $command;
    protected $workingDirectory;

    /**
    * Runs a composer CLI command and captures the output.
    *
    * @param string|array $params The composer params
    * @param array $output The returned output
    * @param string|null $workingDirectory The working directory
    * @return boolean Whether the exit code is 0
    */
    public function capture($params, &$output, $workingDirectory = null)
    {
        return $this->processWork($params, $workingDirectory, $output, true);
    }

    /**
    * Runs a composer CLI command.
    *
    * @param string|array $params The composer params
    * @param string|null $workingDirectory The working directory
    * @return boolean Whether the exit code is 0
    */
    public function run($params, $workingDirectory = null)
    {
        return $this->processWork($params, $workingDirectory, $dummy, false);
    }

   /**
    * Returns the command for calling the composer CLI. If composer.phar is in
    * the current project directory this will be 'php "full/path/to/composer.phar"',
    * otherwise it will be 'composer'.
    *
    * @return string The command
    */
    public function getCommand()
    {
        if (!$this->command) {
            if ($composerPhar = $this->getComposerPhar(false)) {
                $this->command = 'php '.escapeshellarg($composerPhar);
            } else {
                $this->command = 'composer';
            }
        }

        return $this->command;
    }

    /**
    * Searches for composer.phar and returns its full path
    *
    * @return string|null The full path to composer.phar
    */
    public function getComposerPhar()
    {
        return $this->getComposerPhar(true);
    }

    /**
    * Sets workingDirectory for process calls. Can be unset by passing null or an empty string
    *
    * @param mixed $path
    */
    public function setWorkingDirectory($path)
    {
      $this->workingDirectory = $path && is_string($path) ? $path : null;
    }

    /**
    * Creates a new Package
    *
    * @param array $values The basic package properties
    * @return Package
    */
    public function packageCreate($values)
    {
        $package = new Package();
        $package->create($values);

        return $package;
    }

    /**
    * Creates a Package from an existing composer.json file
    *
    * @param string $filename
    * @return Package
    */
    public function packageOpen($filename)
    {
        $package = new Package();
        $package->open($filename);

        return $package;
    }

    /**
    * Runs composer install on the package
    *
    * @param mixed $package Either a Package or filename
    * @param string|array $params
    * @return boolean
    */
    public function packageInstall($package, $params = array())
    {
        return $this->packageWork($package, $params, true);
    }

    /**
    * Runs composer unpdate on the package
    *
    * @param mixed $package Either a Package or filename
    * @param string|array $params
    * @return boolean
    */
    public function packageUpdate($package, $params = array())
    {
        return $this->packageWork($package, $params, false);
    }

    /**
    * Searches for composer.phar and returns its full path
    *
    * @param boolean $global Whether to search outside the current project directory
    * @return string|null The full path to composer.phar
    */
    protected function getComposerPhar($global)
    {
        $composerPhar = null;
        $path = __DIR__;

        while ($pos = strrpos($path, DIRECTORY_SEPARATOR.'vendor')) {
            $path = substr($path, 0, $pos + 1).'composer.phar';

            if (file_exists($path)) {
                $composerPhar = $path;
                break;
            }
        }

        if (!$composerPhar && $global) {
            $envPaths = explode(PATH_SEPARATOR, getenv('path'));

            foreach ($envPaths as $path) {
               $path .= '/composer.phar';
               if (file_exists($path)) {
                   $composerPhar = $path;
                   break;
               }
            }

            if (!$composerPhar) {

                foreach ($envPaths as $path) {
                   $path .= '/composer';
                   if (file_exists($path) && is_file($path)) {
                       $composerPhar = $path;
                       break;
                   }
                }

            }

            if (!$composerPhar) {
                $composerPhar = stream_resolve_include_path('composer.phar');
            }

            if (!$composerPhar) {
                $composerPhar = stream_resolve_include_path('composer');
            }
        }

        return $composerPhar ? strtr($composerPhar, '\\', '/') : null;
    }

    /**
    * Escapes individual arguments if passed an array
    *
    * @param string|array $params
    */
    protected function getParams($params)
    {
        if (is_array($params)) {
            $parts = array();

            foreach (array_map('trim', $params) as $param) {
                if ($param) {
                    $parts[] = escapeshellarg($param);
                }
            }

            $params = implode(' ', $parts);
        }

        return is_string($params) ? $params : '';
    }

    /**
    * Runs a Composer CLI command from capture or run.
    *
    * @param string|array $params The composer params
    * @param string|null $workingDirectory The working directory
    * @param array $output The returned output, if required
    * @param boolean $capture Whether to capture the output
    * @return boolean Whether the exit code is 0
    */
    protected function processWork($params, $workingDirectory, &$output, $capture)
    {
        $cwd = $this->changeWorkingDirectory($workingDirectory);
        $command = $this->processGetCommand().' '.$this->getParams($params);

        if ($capture) {
            $output = array();
            exec($command, $output, $exitCode);
        } else {
            passthru($command, $exitCode);
        }

        if ($cwd) {
            chdir($cwd);
        }

        return $exitCode === 0;
    }

    /**
    * Changes the working directory for the current processWork call, if necessary.
    *
    * @param string|null $directory
    */
    protected function changeWorkingDirectory($directory)
    {
        $result = null;

        if ($directory = $directory ?: $this->workingDirectory) {
            $result = getcwd();
            chdir($directory);
        }

        return $result;
    }

    /**
    * Runs install or update on the package
    *
    * @param mixed $package
    * @param string|array $params
    * @param boolean $install
    * @return boolean
    */
    protected function packageWork($package, $params, $install)
    {
        if (!($package instanceof Package)) {
            $package = $this->packageOpen($package);
        }

        $params = (array) $params;
        array_unshift($params, $install ? 'install' : 'update');
        return $this->processRun($params, dirname($package->filename));
    }
}
