<?php


namespace Phore\CloudTool;


use Phore\FileSystem\PhoreDirectory;
use Phore\Log\PhoreLogger;

class PhoreInputFile
{

    /**
     * @var PhoreLogger
     */
    private $log;

    private $isModified = false;

    public function __construct(PhoreLogger $logger)
    {
        $this->log = $logger;
    }

    public function rewriteFile($relativePath, PhoreDirectory $templateRoot, PhoreDirectory $targetDirectory, $environment) {

        $templateFile = $templateRoot->withSubPath($relativePath)->assertFile();
        $targetFile = $targetDirectory->withSubPath($relativePath)->asFile();

        if ($templateFile->getExtension() === "php") {
            // Remove .php from target filename
            $targetFile = phore_file(substr($targetFile, 0, -4));
            $parser = new PhorePhpTemplateParser();
        } else {
            $parser = new PhoreCloudToolParser("", $this->log);
        }

        CloudToolTemplate::__Reset($environment, $targetFile);
        $this->log->debug("Parsing $templateFile");

        // Parse the file
        $parsedContent = $parser->parseFile($templateFile, $environment);

        $status = CloudToolTemplate::Get()->__getStatus();
        $targetFile = $status["target_file"];

        $this->log->debug(" -> Target file: '$targetFile'");
        if ($targetFile->exists() && $targetFile->get_contents() === $parsedContent) {
            return; // Unmodified content
        }
        $targetFile->getDirname()->asDirectory()->mkdir(0755);
        $targetFile->set_contents($parsedContent);



        if ($status["on_file_change_action"] !== null) {
            $this->log->notice("File {$targetFile} changed. triggering on_file_change_action defined in template");
            ($status["on_file_change_action"])($this);
        }
        if ($status["ignore_global_reload"])
            return; // Don't set modified from this template
        $this->isModified = true;

    }

    public function isModified() : bool
    {
        return $this->isModified;
    }

}
