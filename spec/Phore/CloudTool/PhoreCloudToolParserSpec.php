<?php

namespace spec\Phore\CloudTool;

use PhpSpec\ObjectBehavior;
use Phore\FileSystem\PhoreFile;
use Psr\Log\LoggerInterface;

class PhoreCloudToolParserSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith("", $logger);
    }

    function it_renders_template_values(PhoreFile $templateFile)
    {
        $templateFile->get_contents()->willReturn("Hello {{ name }}!");

        $this->parseFile($templateFile, ["name" => "CloudTool"])
            ->shouldReturn("Hello CloudTool!");
    }
}
