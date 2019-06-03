<?php


namespace Phore\CloudTool;


use Phore\FileSystem\PhoreFile;
use Phore\FileSystem\PhoreUri;

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

    private $environment = [];
    
    private $isFileModified = false;
    
    public function __construct(string $templateDir, string $targetDir)
    {

        $this->templateDir = phore_dir($templateDir)->assertDirectory();
        $this->targetDir = phore_dir($targetDir)->assertDirectory();
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
            $tpl = new PhoreCloudToolParser();
            $tpl->parseFile($relpath, $this->templateDir, $this->targetDir, $this->environment);
            if ($tpl->isFileModified())
                $this->isFileModified = true;
        });
    }

}