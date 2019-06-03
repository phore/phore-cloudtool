<?php


namespace Phore\CloudTool;


use Leuffen\TextTemplate\TextTemplate;
use mysql_xdevapi\Exception;
use Phore\FileSystem\PhoreDirectory;
use Phore\FileSystem\PhoreFile;
use Phore\FileSystem\PhoreUri;

class PhoreCloudToolParser extends TextTemplate
{

    public function __construct($text = "")
    {
        parent::__construct($text);

        $this->setOpenCloseTagChars("{{", "}}");
        
        $this->addSection("on_modify", function ($content) {
            $this->onModified[] = function () use ($content) {
                phore_out("on_modify: executing: $content > " . phore_exec($content));
            };
            return "";
        });

        $this->addFunction("target", function ($paramArr, $command, $context, $cmdParam) {
            if (isset ($paramArr["owner"])) {
                $this->onAfterSave[] = function () use ($context, $paramArr) {
                    phore_out("chown " . $paramArr["owner"]);
                    chown($context["target_file"], $paramArr["owner"]);
                };
            }
            if (isset ($paramArr["mode"])) {
                $this->onAfterSave[] = function () use ($context, $paramArr) {
                    phore_out("chmod " . $paramArr["mode"]);
                    chmod($context["target_file"], (int)$paramArr["mode"]);
                };
            }
            return "";
        });

        $this->addFilter("join", function ($input, $joinChar = " ") {
            return implode($joinChar, $input);
        });

        $this->addFunction("load", function($paramArr, $command, $context, $cmdParam) {
            if (isset ($paramArr["env"]))
                return getenv($paramArr["env"]);
            return "";
        });
    }


    public $onAfterSave = [];

    public $onModified = [];

    private $isFileModified = false;
    
    public function isFileModified() : bool
    {
        return $this->isFileModified;
    }
        
    
    public function parseFile(PhoreUri $relPath, PhoreDirectory $templateRoot, PhoreDirectory $targetDirectory, array $environment=[])
    {


        $this->onAfterParse = [];
        $this->onModified = [];

        $templateFile = $templateRoot->withSubPath($relPath)->assertFile();
        $targetFile = $targetDirectory->withSubPath($relPath)->asFile();

        phore_out("Parsing $templateFile -> $targetFile");

        $this->loadTemplate($templateFile->get_contents());
        
        $environment["_target_file"] = $targetFile->getUri();
        
        try {
            $configText = $this->apply($environment, false);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Parsing $templateFile: " . $e->getMessage());
        }
        if ($targetFile->isFile()) {
            if ($targetFile->get_contents() === $configText) {
                
                phore_out("File not modified.");
                return false;
            }
        }
        
        $this->isFileModified = true;
        $targetFile->getDirname()->asDirectory()->mkdir(0755);
        $targetFile->set_contents($configText);
        phore_out("Saving modified file and running triggers.");
        foreach ($this->onAfterSave as $fn)
            $fn();

        foreach ($this->onModified as $fn)
            $fn();


    }


}
