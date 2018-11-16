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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Limit;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToRecordImages;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorRecordsAction
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var Limit */
    private $limit;

    /** @var SearchConnectorRecord */
    private $searchConnectorRecord;

    /** @var PaginatorInterface */
    private $halPaginator;

    /** @var AddHalDownloadLinkToRecordImages */
    private $addHalLinksToImageValues;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        SearchConnectorRecord $searchConnectorRecord,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToRecordImages $addHalLinksToImageValues,
        int $limit,
        ValidatorInterface $validator
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->searchConnectorRecord = $searchConnectorRecord;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
        $this->validator = $validator;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterCode = null !== $searchAfter ? RecordCode::fromString($searchAfter) : null;
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $filterValuesChannel = $this->getFilterValuesChannel($request);
            $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
                $referenceEntityIdentifier,
                $searchAfterCode,
                $this->limit->intValue(),
                $filterValuesChannel
            );
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $violations = $this->validator->validate($recordQuery);

        if ($violations->count() > 0) {
            // FIXME: format message for several violations
            throw new UnprocessableEntityHttpException($violations[0]->getMessage());
        }

        $records = ($this->searchConnectorRecord)($recordQuery);
        $records = array_map(function (ConnectorRecord $record) {
            return $record->normalize();
        }, $records);

        $records = ($this->addHalLinksToImageValues)($referenceEntityIdentifier, $records);
        $paginatedRecords = $this->paginateRecords($records, $request, $referenceEntityIdentifier);

        return new JsonResponse($paginatedRecords);
    }

    private function paginateRecords(array $records, Request $request, ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $lastRecord = end($records);
        reset($records);
        $lastRecordCode = $lastRecord['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_reference_entities_records_rest_connector_get',
            'item_route_name'     => 'akeneo_reference_entities_record_rest_connector_get',
            'search_after'        => [
                'self' => $request->get('search_after', null),
                'next' => $lastRecordCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'uri_parameters'      => [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
            ],
            'query_parameters'    => [
                'channel' => $request->get('channel', null),
            ],
        ];

        return $this->halPaginator->paginate($records, $paginationParameters, count($records));
    }

    private function getFilterValuesChannel(Request $request): ?ChannelIdentifier
    {
        $channel = $request->get('channel', null);

        return null !== $channel ? ChannelIdentifier::fromCode($channel) : null;
    }
}
