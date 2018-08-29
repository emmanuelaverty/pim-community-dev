<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * Read model representing an enriched entity detailled for display purpose (like a form)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityDetails
{
    public const IDENTIFIER = 'identifier';

    public const LABELS = 'labels';

    public const IMAGE = 'image';

    /** @var EnrichedEntityIdentifier */
    public $identifier;

    /** @var LabelCollection */
    public $labels;

    /** @var ?Image */
    public $image;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => (string) $this->identifier,
            self::LABELS     => $this->labels->normalize(),
            self::IMAGE      => (null !== $this->image) ? $this->image->normalize() : null
        ];
    }
}
