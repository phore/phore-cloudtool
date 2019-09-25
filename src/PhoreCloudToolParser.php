<?php


namespace Phore\CloudTool;


use Leuffen\TextTemplate\TextTemplate;
use mysql_xdevapi\Exception;
use Phore\FileSystem\PhoreDirectory;
use Phore\FileSystem\PhoreFile;
use Phore\FileSystem\PhoreUri;
use Psr\Log\LoggerInterface;

class PhoreCloudToolParser extends TextTemplate
{
    /**
     * @var LoggerInterface
     */
    private $log;


    public function __construct($text = "", LoggerInterface $logger)
    {
        parent::__construct($text);
        $this->log = $logger;

        $this->addSection("on_modify", function ($content) {
            $this->onModified[] = function () use ($content) {
                $this->log->notice("on_modify: executing: $content > " . phore_exec($content));
            };
            return "";
        });

        $this->addFunction("target", function ($paramArr, $command, $context, $cmdParam) {
            if (isset ($paramArr["owner"])) {
                $this->onAfterSave[] = function () use ($context, $paramArr) {
                    $this->log->notice("chown " . $paramArr["owner"]);
                    chown($context["target_file"], $paramArr["owner"]);
                };
            }
            if (isset ($paramArr["mode"])) {
                $this->onAfterSave[] = function () use ($context, $paramArr) {
                    $this->log->notice("chmod " . $paramArr["mode"]);
                    chmod($context["target_file"], (int)$paramArr["mode"]);
                };
            }
            return "";
        });

        $this->addFunction("load", function($paramArr, $command, $context, $cmdParam) {
            if (isset ($paramArr["env"]))
                return getenv($paramArr["env"]);
            return "";
        });
    }


    public $onAfterSave = [];

    public $onModified = [];

    public function parseFile(PhoreUri $relPath, PhoreDirectory $templateRoot, PhoreDirectory $targetDirectory)
    {


        $this->onAfterParse = [];
        $this->onModified = [];

        $templateFile = $templateRoot->withSubPath($relPath)->assertFile();
        $targetFile = $targetDirectory->withSubPath($relPath)->asFile();

        $this->log->notice("Parsing $templateFile -> $targetFile");

        $this->loadTemplate($templateFile->get_contents());
        try {
            $configText = $this->apply([
                "target_file" => $targetFile->getUri()
            ], false);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Parsing $templateFile: " . $e->getMessage());
        }
        if ($targetFile->isFile()) {
            if ($targetFile->get_contents() === $configText) {
                $this->log->notice("File not modified.");
                return false;
            }
        }

        $targetFile->getDirname()->asDirectory()->mkdir(0755);
        $targetFile->set_contents($configText);
        $this->log->notice("Saving modified file and running triggers.");
        foreach ($this->onAfterSave as $fn)
            $fn();

        foreach ($this->onModified as $fn)
            $fn();


    }


}
