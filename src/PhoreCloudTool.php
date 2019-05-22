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

    public function __construct(string $templateDir, string $targetDir)
    {

        $this->templateDir = phore_dir($templateDir)->assertDirectory();
        $this->targetDir = phore_dir($targetDir)->assertDirectory();
    }





    public function parseRecursive()
    {
        $this->templateDir->walkR(function(PhoreUri $relpath) {
            $relpath = phore_uri( substr( $relpath->getUri(), strlen($this->templateDir)));
            $tpl = new PhoreCloudToolParser();
            $tpl->parseFile($relpath, $this->templateDir, $this->targetDir);
        });
    }

}