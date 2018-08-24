<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsRichTextEditor
{
    /** @var bool */
    private $isRichTextEditor;

    private function __construct(bool $isRichTextEditor)
    {
        $this->isRichTextEditor = $isRichTextEditor;
    }

    public static function fromBoolean(bool $isRichTextEditor): self
    {
        return new self($isRichTextEditor);
    }

    public function isYes(): bool
    {
        return $this->isRichTextEditor;
    }

    public function normalize(): bool
    {
        return $this->isRichTextEditor;
    }
}
