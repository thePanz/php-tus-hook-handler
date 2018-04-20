<?php

namespace Pnz\TusHookHandler\Handler;

use Pnz\TusHookHandler\Event\HookEvent;
use Pnz\TusHookHandler\Exception\InvalidTusRequestDataException;
use Pnz\TusHookHandler\Exception\InvalidTusRequestException;
use Pnz\TusHookHandler\Model\HookData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class TusHookHandler
{
    public const HOOK_HEADER = 'Hook-Name';

    public const HOOKS = [
        HookData::HOOK_PRE_CREATE,
        HookData::HOOK_POST_CREATE,
        HookData::HOOK_POST_FINISH,
        HookData::HOOK_POST_TERMINATE,
        HookData::HOOK_POST_RECEIVE,
    ];

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handleHook(HookData $hookData)
    {
        $event = new HookEvent($hookData);
        $this->dispatcher->dispatch('tus.hook.'.$hookData->hookName, $event);
    }

    /**
     * Builds the Hook data object from the given request.
     *
     * @param Request $request The Hook request
     *
     * @throws InvalidTusRequestException
     * @throws InvalidTusRequestDataException
     *
     * @return HookData the data in the request
     */
    public function buildHookData(Request $request): HookData
    {
        $hookName = (string) $request->headers->get(self::HOOK_HEADER);
        if (!$hookName) {
            throw new InvalidTusRequestException('Invalid hook invocation, name is missing');
        }

        if ('json' !== $request->getContentType()) {
            throw new InvalidTusRequestException('Invalid hook content type');
        }

        if (!in_array($hookName, self::HOOKS, true)) {
            throw new InvalidTusRequestException(sprintf('Invalid hook name: %s given', $hookName));
        }

        try {
            $hookData = HookData::buildFromBody($request->getContent());
        } catch (\InvalidArgumentException $e) {
            throw new InvalidTusRequestDataException($e->getMessage());
        }

        // Assign the hook name
        $hookData->hookName = $hookName;

        return $hookData;
    }
}
