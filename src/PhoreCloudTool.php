<?php


namespace Phore\CloudTool;


use Phore\FileSystem\PhoreFile;
use Phore\FileSystem\PhoreUri;
use Psr\Log\LoggerInterface;

class PhoreCloudTool
{
    /**
     * @var \Phore\FileSystem\PhoreDirectory
     */
    private $templateDir;

    /**
     * @var \Phore\FileSystem\PhoreDirectory
     */
    private $targetDir;


    /**
     * @var LoggerInterface
     */
    private $logger;

    private $environment = [];

    private $isFileModified = false;


    private static $config;


    /**
     * Set configuration options for cloudTool
     *
     * @return PhoreCloudToolConfig
     */
    public static function Config() : PhoreCloudToolConfig
    {
        if (self::$config === null)
            self::$config = new PhoreCloudToolConfig();
        return self::$config;
    }


    public function __construct(string $templateDir, string $targetDir, LoggerInterface $logger)
    {
        $this->templateDir = phore_dir($templateDir)->assertDirectory();
        $this->targetDir = phore_dir($targetDir)->assertDirectory();
        $this->logger = $logger;
    }

    public function setEnvironment(array $environment)
    {
        $this->environment = $environment;
    }

    /**
     *
     */
    public function isFileModified() : bool
    {
        return $this->isFileModified;
    }


    /**
     * Parse all files in template directory - parse them and
     * write (if changed) to destination directory.
     *
     * If files were modified during processing, $this->isFileModified
     * will be true;
     */
    public function parseRecursive()
    {
        $this->isFileModified = false;
        $inputFile = new PhoreInputFile($this->logger);

        $envLoader = self::Config()->environmentLoader;
        if (is_callable($envLoader)) {
            $this->logger->debug("Running environment loader (defined in config)");
            $this->environment = $envLoader();
            if ( ! is_array($this->environment))
                throw new \InvalidArgumentException("environment-loader must return array. It returned " . gettype($this->environment));
        }

        $this->templateDir->walkR(function(PhoreUri $relpath) use ($inputFile) {
            $relpath = phore_uri( substr( $relpath->getUri(), strlen($this->templateDir)));

            $this->logger->debug("Walking: $this->templateDir / $relpath...");

            $inputFile->rewriteFile($relpath, $this->templateDir, $this->targetDir, $this->environment);
        });
        $this->isFileModified = $inputFile->isModified();
    }

}
