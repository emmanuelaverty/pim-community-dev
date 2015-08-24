<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\AbstractFilter;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Product Value filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueLocaleRightFilter extends AbstractFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var LocaleManager */
    protected $localeManager;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LocaleManager                 $localeManager
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, LocaleManager $localeManager)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->localeManager        = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        return $productValue->getAttribute()->isLocalizable() &&
            !$this->authorizationChecker->isGranted(
                Attributes::VIEW_ITEMS,
                $this->localeManager->getLocaleByCode($productValue->getLocale())
            );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }
}
