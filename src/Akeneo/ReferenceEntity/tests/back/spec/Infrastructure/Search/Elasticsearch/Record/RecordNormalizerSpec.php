<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordSearchMatrixNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNormalizerSpec extends ObjectBehavior
{
    function let(RecordSearchMatrixNormalizer $searchMatrixNormalizer)
    {
        $this->beConstructedWith($searchMatrixNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordNormalizer::class);
    }

    function it_normalizes_the_code_in_the_search_field($searchMatrixNormalizer)
    {
        $record = Record::create(
            RecordIdentifier::create('designer', 'stark', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('stark'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $searchMatrixNormalizer->generate($record)->willReturn(['search_matrix']);

        $normalizedRecord = $this->normalize($record);
        $normalizedRecord['identifier']->shouldBeEqualTo('designer_stark_fingerprint');
        $normalizedRecord['code']->shouldBeEqualTo('stark');
        $normalizedRecord['reference_entity_code']->shouldBeEqualTo('designer');
        $normalizedRecord['record_list_search']->shouldBeEqualTo(['search_matrix']);
        $normalizedRecord['updated_at']->shouldBeString();
    }
}

