<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Saver for an asset variation
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetVariationSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var CompletenessGeneratorInterface */
    protected $compGenerator;

    /**
     * @param ObjectManager                  $objectManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param CompletenessGeneratorInterface $compGenerator
     */
    public function __construct(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        CompletenessGeneratorInterface $compGenerator
    ) {
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->compGenerator   = $compGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function save($variation, array $options = [])
    {
        if (!$variation instanceof VariationInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\VariationInterface", "%s" provided.',
                    ClassUtils::getClass($variation)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($variation);

        if (true === $options['schedule']) {
            $this->compGenerator->scheduleForAsset($variation->getAsset());
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Save many objects
     *
     * @param VariationInterface[] $variations
     * @param array                $options    The saving options
     */
    public function saveAll(array $variations, array $options = [])
    {
        $options = [
            'flush'    => false,
            'schedule' => false,
        ];

        foreach ($variations as $variation) {
            $this->save($variation, $options);
        }

        $this->objectManager->flush();
    }
}
