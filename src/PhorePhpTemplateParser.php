<?php


namespace Phore\CloudTool;


use Phore\FileSystem\PhoreDirectory;
use Phore\FileSystem\PhoreFile;

class PhorePhpTemplateParser
{

    public function parseFile(PhoreFile $inputFile, $environment) : string
    {
        $output = "";
        ob_start(function ($obBuf) use (&$output) {
            $output = $obBuf;
            return "";
        });
        try {
            require $inputFile;
            ob_end_clean();
            return $output;
        } catch (\Error $error) {
            ob_end_clean();
            throw new \Exception("Error on $inputFile: " . $error->getMessage() . " in {$error->getFile()}:{$error->getLine()}");
        }
    }


}
