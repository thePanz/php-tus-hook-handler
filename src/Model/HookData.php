<?php

namespace Pnz\TusHookHandler\Model;

/**
 * Main data model for the Hook data.
 *
 * @see https://github.com/tus/tusd/blob/master/docs/hooks.md#the-hooks-environment
 */
class HookData
{
    // https://github.com/tus/tusd/blob/master/docs/hooks.md#pre-create
    public const HOOK_PRE_CREATE = 'pre-create';

    // https://github.com/tus/tusd/blob/master/docs/hooks.md#post-create
    public const HOOK_POST_CREATE = 'post-create';

    // https://github.com/tus/tusd/blob/master/docs/hooks.md#post-finish
    public const HOOK_POST_FINISH = 'post-finish';

    // https://github.com/tus/tusd/blob/master/docs/hooks.md#post-receive
    const HOOK_POST_RECEIVE = 'post-receive';

    // https://github.com/tus/tusd/blob/master/docs/hooks.md#post-terminate
    public const HOOK_POST_TERMINATE = 'post-terminate';

    /**
     * The hook name.
     *
     * @var string
     */
    public $hookName;

    /**
     * The upload's ID. Will be empty during the pre-create event.
     *
     * @var string|null
     */
    public $id;

    /**
     * The upload's total size in bytes.
     *
     * @var int|null
     */
    public $size;

    /**
     * The upload's current offset in bytes.
     *
     * @var int
     */
    public $offset;

    /**
     * Set to true if the upload is final.
     *
     * See the Concatenation extension for details http://tus.io/protocols/resumable-upload.html#concatenation
     *
     * @var bool
     */
    public $isFinal;

    /**
     * Set to true if the upload is partial.
     *
     * See the Concatenation extension for details http://tus.io/protocols/resumable-upload.html#concatenation
     *
     * @var bool
     */
    public $isPartial;

    /**
     * If the upload is a final one, this value will be an array of upload IDs which are concatenated to produce the upload.
     *
     * @var string[]
     */
    public $partialUploads = [];

    /**
     * The upload's meta data which can be supplied by the clients as it wishes.
     * Key-value pairs as strings.
     *
     * @var string[]
     */
    public $metaData = [];

    /**
     * @param string $hookBody
     *
     * @return HookData
     */
    public static function buildFromBody(string $hookBody): self
    {
        $json = json_decode($hookBody, true, 3);

        if (json_last_error()) {
            throw new \InvalidArgumentException('Invalid data: '.json_last_error_msg());
        }

        $hookData = new self();
        $hookData->id = $json['ID'] ?? null;
        $hookData->size = $json['Size'] ?? null;
        $hookData->offset = $json['Offset'] ?? 0;
        $hookData->metaData = $json['MetaData'] ?? [];
        $hookData->isPartial = $json['IsPartial'] ?? false;
        $hookData->isFinal = $json['IsFinal'] ?? false;
        $hookData->partialUploads = $json['PartialUploads'] ?? [];

        return $hookData;
    }
}
