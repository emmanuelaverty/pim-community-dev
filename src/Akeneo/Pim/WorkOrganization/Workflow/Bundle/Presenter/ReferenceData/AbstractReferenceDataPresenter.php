<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Abstract Present changes of reference data
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
abstract class AbstractReferenceDataPresenter extends AbstractProductValuePresenter
{
    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /** @var string */
    protected $referenceDataName;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        parent::__construct($attributeRepository);

        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data)
    {
        $supports = parent::supports($data);
        if ($supports) {
            $attribute = $this->attributeRepository->findOneByIdentifier($data->getAttributeCode());

            if (null !== $attribute) {
                $this->referenceDataName = $attribute->getReferenceDataName();

                return true;
            }
        }

        return false;
    }
}