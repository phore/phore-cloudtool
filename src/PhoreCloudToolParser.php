<?php


namespace Phore\CloudTool;


use Leuffen\TextTemplate\TextTemplate;
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

        $this->setOpenCloseTagChars("{{", "}}");

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
                    chown($context["_target_file"], $paramArr["owner"]);
                };
            }
            if (isset ($paramArr["mode"])) {
                $this->onAfterSave[] = function () use ($context, $paramArr) {
                    $this->log->notice("chmod " . $paramArr["mode"]);
                    chmod($context["_target_file"], (int)$paramArr["mode"]);
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

    public function parseFile(PhoreFile $templateFile, $environment) : string
    {
        $this->loadTemplate($templateFile->get_contents());

        $environment["_target_file"] = $templateFile->getUri();

        try {
            return $this->apply($environment, false);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Parsing $templateFile: " . $e->getMessage());
        }
    }



}
