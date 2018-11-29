<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalSelfLinkToNormalizedConnectorAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAttributesAction
{
    /** @var FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AddHalSelfLinkToNormalizedConnectorAttribute */
    private $addHalSelfLinkToNormalizedConnectorAttribute;

    public function __construct(
        FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface $findConnectorReferenceEntityAttributes,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntityAttributes = $findConnectorReferenceEntityAttributes;
        $this->addHalSelfLinkToNormalizedConnectorAttribute = $addHalSelfLinkToNormalizedConnectorAttribute;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier): JsonResponse
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntity = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (false === $referenceEntity) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $attributes = ($this->findConnectorReferenceEntityAttributes)($referenceEntityIdentifier);

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute->normalize();
            $normalizedAttribute = ($this->addHalSelfLinkToNormalizedConnectorAttribute)($referenceEntityIdentifier, $normalizedAttribute);
            $normalizedAttributes[] = $normalizedAttribute;
        }

        return new JsonResponse($normalizedAttributes);
    }
}
