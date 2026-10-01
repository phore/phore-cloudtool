<?php

namespace spec\Phore\CloudTool;

use Phore\CloudTool\PhoreCloudToolParser;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class PhoreCloudToolParserSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger): void
    {
        $this->beConstructedWith('', $logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreCloudToolParser::class);
    }

    public function it_starts_unmodified(): void
    {
        $this->isFileModified()->shouldReturn(false);
    }
}
