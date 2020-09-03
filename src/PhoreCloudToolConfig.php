<?php


namespace Phore\CloudTool;


class PhoreCloudToolConfig
{

    /**
     * Define a user defined env loader.
     *
     * This method is called every time before template
     * parsing starts. The return value is passed to the
     * templates as environment.
     *
     * <example>
     * PhoreCloudTool::Config()->environmentLoader = function () {
     *      return ["some"=>"env];
     * };
     * </example>
     *
     * @var callable|null
     */
    public $environmentLoader = null;

    /**
     * The Interval (in seconds) to sleep after each run when
     * running in watch mode (--watch is presend)
     *
     * @var int
     */
    public $watchSleepInval = 1;
}
