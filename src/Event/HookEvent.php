<?php

namespace Pnz\TusHookHandler\Event;

use Pnz\TusHookHandler\Model\HookData;
use Symfony\Component\EventDispatcher\Event;

class HookEvent extends Event
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var HookData
     */
    private $hookData;

    public function __construct(HookData $hookData)
    {
        $this->name = $hookData->hookName;
        $this->hookData = $hookData;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return HookData
     */
    public function getHookData(): HookData
    {
        return $this->hookData;
    }
}
