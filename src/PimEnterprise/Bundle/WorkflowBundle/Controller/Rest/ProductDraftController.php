<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product draft rest controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftController
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftManager             $manager
     * @param ProductRepositoryInterface      $productRepository
     * @param NormalizerInterface             $normalizer
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ProductDraftRepositoryInterface $repository,
        ProductDraftManager $manager,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer
    ) {
        $this->securityContext   = $securityContext;
        $this->repository        = $repository;
        $this->manager           = $manager;
        $this->productRepository = $productRepository;
        $this->normalizer        = $normalizer;
    }

    /**
     * Mark a product draft as ready
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse
     */
    public function readyAction($productId)
    {
        $product = $this->productRepository->findOneById($productId);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %d not found', $productId));
        }

        if (null === $productDraft = $this->findDraftForProduct($product)) {
            throw new NotFoundHttpException(sprintf('Draft for product %d not found', $productId));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft);

        return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
    }

    /**
     * Find a product draft for a product
     *
     * @param ProductInterface $product
     *
     * @return ProductDraftInterface|null
     */
    protected function findDraftForProduct(ProductInterface $product)
    {
        $username = $this->securityContext->getToken()->getUsername();
        $productDraft = $this->repository->findUserProductDraft($product, $username);

        return $productDraft;
    }
}
