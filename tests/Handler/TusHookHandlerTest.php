<?php

namespace Pnz\TusHookHandler\Tests\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pnz\TusHookHandler\Event\HookEvent;
use Pnz\TusHookHandler\Exception\InvalidTusRequestDataException;
use Pnz\TusHookHandler\Exception\InvalidTusRequestException;
use Pnz\TusHookHandler\Handler\TusHookHandler;
use Pnz\TusHookHandler\Model\HookData;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass
 */
class TusHookHandlerTest extends TestCase
{
    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var TusHookHandler|MockObject
     */
    private $handler;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new TusHookHandler($this->eventDispatcher);
    }

    public function handleHookDataprovider(): iterable
    {
        yield ['post-create', 'tus.hook.post-create'];
        yield ['pre-create', 'tus.hook.pre-create'];
        yield ['post-create', 'tus.hook.post-create'];
        yield ['post-finish', 'tus.hook.post-finish'];
        yield ['post-receive', 'tus.hook.post-receive'];
        yield ['post-terminate', 'tus.hook.post-terminate'];
    }

    /**
     * @dataProvider handleHookDataprovider
     */
    public function testHandleHook(string $hookName, string $hookEvent)
    {
        /** @var HookData|MockObject $data */
        $data = $this->createMock(HookData::class);
        $data->hookName = $hookName;

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($hookEvent, $this->callback(function (Event $event) use ($hookName, $data) {
                $this->assertInstanceOf(HookEvent::class, $event);
                /* @var HookEvent $event */
                $this->assertSame($hookName, $event->getName());
                $this->assertSame($data, $event->getHookData());

                return true;
            }));

        $this->handler->handleHook($data);
    }

    public function buildHookDataExceptionDataProvider(): iterable
    {
        yield [$this->buildRequest('GET'), InvalidTusRequestException::class];
        yield [$this->buildRequest('PUT'), InvalidTusRequestException::class];
        yield [$this->buildRequest('PATCH'), InvalidTusRequestException::class];
        yield [$this->buildRequest('OPTIONS'), InvalidTusRequestException::class];

        yield [$this->buildRequest('POST', ''), InvalidTusRequestException::class];
        yield [$this->buildRequest('POST', 'post-create'), InvalidTusRequestException::class];
        yield [$this->buildRequest('POST', 'post-create', 'application/json'), InvalidTusRequestDataException::class];
        yield [$this->buildRequest('POST', 'post-create', 'application/json', 'xxx'), InvalidTusRequestDataException::class];
        yield [$this->buildRequest('POST', 'post-create', 'application/json', ''), InvalidTusRequestDataException::class];
    }

    /**
     * @dataProvider buildHookDataExceptionDataProvider
     */
    public function testBuildHookDataThrowsException(Request $request, string $exception)
    {
        $this->expectException($exception);
        $this->handler->buildHookData($request);
    }

    private function buildRequest(string $method, string $hookName = null, string $contentType = null, string $contents = null): Request
    {
        $request = new Request([], [], [], [], [], [], $contents);
        $request->server->set('REQUEST_METHOD', $method);
        $request->headers->set('HOOK_NAME', $hookName);
        $request->headers->set('CONTENT_TYPE', $contentType);

        return $request;
    }
}
