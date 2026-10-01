<?php

namespace spec\Phore\CloudTool;

use PhpSpec\ObjectBehavior;

class PhoreCloudToolConfigSpec extends ObjectBehavior
{
    function it_defaults_to_no_environment_loader()
    {
        $this->environmentLoader->shouldBe(null);
    }

    function it_defaults_to_a_one_second_watch_interval()
    {
        $this->watchSleepInval->shouldBe(1);
    }
}
