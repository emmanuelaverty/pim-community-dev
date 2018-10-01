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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Record get action.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    public function __construct(FindRecordDetailsInterface $findRecordDetailsQuery)
    {
        $this->findRecordDetailsQuery = $findRecordDetailsQuery;
    }

    public function __invoke(string $referenceEntityIdentifier, string $recordCode): JsonResponse
    {
        $recordCode = $this->getRecordCodeOr404($recordCode);
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);
        $recordDetails = $this->findRecordDetailsOr404($referenceEntityIdentifier, $recordCode);

        return new JsonResponse($recordDetails->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getRecordCodeOr404(string $recordCode): RecordCode
    {
        try {
            return RecordCode::fromString($recordCode);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getReferenceEntityIdentifierOr404(string $referenceEntityIdentifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function findRecordDetailsOr404(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode
    ): RecordDetails {
        $result = ($this->findRecordDetailsQuery)($referenceEntityIdentifier, $recordCode);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $result;
    }
}
