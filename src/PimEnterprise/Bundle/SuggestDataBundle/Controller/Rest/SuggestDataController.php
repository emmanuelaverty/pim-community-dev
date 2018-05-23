<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Controller\Rest;

use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DataProviderFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Suggest data REST controller to interact between UI
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestDataController
{
    /** @var ProductRepositoryInterface */
    protected $repository;

    /** @var DataProviderFactory */
    protected $dataProviderFactory;

    /**
     * @param ProductRepositoryInterface $repository
     * @param DataProviderFactory        $dataProviderFactory
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->repository = $repository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param $productId
     *
     * @return JsonResponse
     */
    public function pushAction($productId): JsonResponse
    {
        $product = $this->repository->find($productId);
        $jsonResponse = new JsonResponse();
        if (null === $product) {
            $jsonResponse->setStatusCode(Response::HTTP_NOT_FOUND);
            $jsonResponse->setData([
                'error' => 'Requested product not found.'
            ]);

            return $jsonResponse;
        }

        $dataProvider = $this->dataProviderFactory->create();
        $dataProviderResponse = $dataProvider->push($product);

        return $jsonResponse->setJson($dataProviderResponse);
    }
}
