# TUS hook handler for PHP

Quick helper to handle TUSd HTTP hooks.
The handler exposes two methods to process the hook data and to dispatch TUSd events
by using and EventDispatcher.

Example of triggered events:

- `tus.hook.pre-create`: Pre create event (https://github.com/tus/tusd/blob/master/docs/hooks.md#pre-create)
- `tus.hook-post-create`: Post crete event (see: https://github.com/tus/tusd/blob/master/docs/hooks.md#post-create)
- `tus.hook-post-finish`: Post finish event (see: https://github.com/tus/tusd/blob/master/docs/hooks.md#post-finish)
- `tus.hook-post-receive`: Post receive event (see: https://github.com/tus/tusd/blob/master/docs/hooks.md#post-receive)
- `tus.hook-post-terminate`: Post terminate event (see: https://github.com/tus/tusd/blob/master/docs/hooks.md#post-terminate)

Due to the TUSd hook implementation, your controller *MUST* respond with a proper response to the `pre-create` http hook,
to confirm the upload (response code 200), or to deny it (response code 400).

Example of the hook controller:

```php
    public function tusdHookAction(Request $request): Response
    {
        try {
            $data = $this->tusHookHandler->buildHookData($request);
        } catch (TusException $e) {
            // Do not proceed, as the the request is invalid.
            // The TUSd server will abort the upload
            return new Response('Invalid request: '.$e->getMessage(), 400);
        }

        if (HookData::HOOK_PRE_CREATE === $data->hookName) {
            if (!$this->isValidUpload($data)) {
                // Return a failure response, TUSd server will abort the upload
                return new Response('Invalid request: invalid token', 400);
            }
        }

        // Let the handler dispatch the event
        $this->tusHookHandler->handleHook($data);

        // Return an empty response, TUSd server will handle it as a positive answer.
        return new Response();
    }

    private function isValidUpload(HookData $data): bool
    {
      ...
    }
```
