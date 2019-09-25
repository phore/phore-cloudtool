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
    
    
    public function parseRecursive()
    {
        $this->isFileModified = false;
        $this->templateDir->walkR(function(PhoreUri $relpath) {
            $relpath = phore_uri( substr( $relpath->getUri(), strlen($this->templateDir)));

            $this->logger->notice("Walking: $this->templateDir / $relpath...");

            $tpl = new PhoreCloudToolParser("", $this->logger);
            $tpl->parseFile($relpath, $this->templateDir, $this->targetDir, $this->environment);
            if ($tpl->isFileModified())
                $this->isFileModified = true;
        });
    }

}