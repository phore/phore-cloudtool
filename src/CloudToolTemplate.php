<?php


namespace Phore\CloudTool;


use Phore\FileSystem\PhoreFile;

class CloudToolTemplate
{
    private static $instance = null;


    public static function Get() : self
    {
        return self::$instance;
    }

    public static function __Reset($environment, PhoreFile $targetFile)
    {
        self::$instance = new self($environment, $targetFile);
    }

    protected $environment;


    protected $status = [
        "ignore_global_reload" => false,
        "on_file_change_action" => null,
        "target_file" => null
    ];

    private function __construct($environment, PhoreFile $targetFile)
    {
        $this->environment = $environment;
        $this->status["target_file"] = $targetFile;
    }


    public function getTargetFile() : PhoreFile
    {
        return $this->status["target_file"];
    }

    /**
     * Access the environment generated by the PhoreCloudTool::Config()->environmentLoader
     * function
     *
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set the target filename of this template (overriding
     * the default config)
     *
     * @param $filename
     * @return $this
     */
    public function setTargetFile($filename) : self
    {
        $this->status["target_file"] = phore_file($filename);
        return $this;
    }

    /**
     * Do not trigger the global change action if this file was changed
     *
     * @return $this
     */
    public function ignoreGlobalChangeAction() : self
    {
        $this->status["ignore_global_reload"] = true;
        return $this;
    }

    /**
     * Register a callback function to be run if this file was changed
     *
     * @param callable $callback
     * @return $this
     */
    public function setOnChangeAction(callable $callback) : self
    {
        $this->status["on_file_change_action"] = $callback;
        return $this;
    }

    public function __getStatus() : array
    {
        return $this->status;
    }
}
