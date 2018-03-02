<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\UserBundle\Manager\UserManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Publish tasklet for products
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class PublishProductTasklet extends AbstractProductPublisherTasklet implements TaskletInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /**
     * @param PublishedProductManager             $manager
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ValidatorInterface                  $validator
     * @param ObjectDetacherInterface             $objectDetacher
     * @param UserManager                         $userManager
     * @param TokenStorageInterface               $tokenStorage
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     */
    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        parent::__construct(
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher,
            $userManager,
            $tokenStorage
        );

        $this->authorizationChecker = $authorizationChecker;
        $this->pqbFactory = $pqbFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->initSecurityContext($this->stepExecution);

        $jobParameters = $this->stepExecution->getJobParameters();
        $cursor = $this->getProductsCursor($jobParameters->get('filters'));
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $productsPage) {
            $invalidProducts = [];
            foreach ($productsPage as $index => $product) {
                $violations = $this->validator->validate($product);
                $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

                if (0 === $violations->count() && $isAuthorized) {
                    $this->stepExecution->incrementSummaryInfo('mass_published');
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $invalidProducts[$index] = $product;

                    if (0 < $violations->count()) {
                        $this->addWarningMessage($violations, $product);
                    }
                    if (!$isAuthorized) {
                        $this->stepExecution->addWarning(
                            'pim_enrich.mass_edit_action.publish.message.error',
                            [],
                            new DataInvalidItem($product)
                        );
                    }
                }
            }

            $productsPage = array_diff_key($productsPage, $invalidProducts);
            $this->detachProducts($invalidProducts);
            $this->manager->publishAll($productsPage);
            $this->detachProducts($productsPage);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder(array $filters = [])
    {
        $pqb = $this->pqbFactory->create(['filters' => $filters]);

        // dirty trick to skip the product models
        // TODO: revert when PIM-6565 is done properly
        $searchQueryBuilder = $pqb->getQueryBuilder();
        $searchQueryBuilder->addFilter(['term' => ['document_type' => ProductInterface::class]]);

        $pqb->setQueryBuilder($searchQueryBuilder);

        return $pqb;
    }
}
