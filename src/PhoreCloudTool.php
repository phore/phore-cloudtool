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

    public function __construct(string $templateDir, string $targetDir, LoggerInterface $logger)
    {
        $this->templateDir = phore_dir($templateDir)->assertDirectory();
        $this->targetDir = phore_dir($targetDir)->assertDirectory();
        $this->logger = $logger;
    }





    public function parseRecursive()
    {
        $this->templateDir->walkR(function(PhoreUri $relpath) {
            $relpath = phore_uri( substr( $relpath->getUri(), strlen($this->templateDir)));
            $this->logger->notice("Walking: $this->templateDir / $relpath...");
            $tpl = new PhoreCloudToolParser("", $this->logger);
            $tpl->parseFile($relpath, $this->templateDir, $this->targetDir);
        });
    }

}