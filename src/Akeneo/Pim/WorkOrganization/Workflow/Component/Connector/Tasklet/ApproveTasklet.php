<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Tasklet;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\DraftNotReviewableException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;

/**
 * Tasklet for product drafts mass approval.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ApproveTasklet extends AbstractReviewTasklet
{
    /** @staticvar string */
    const TASKLET_NAME = 'approve';

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $this->initSecurityContext($this->stepExecution);

        $jobParameters = $this->stepExecution->getJobParameters();
        $productDrafts = $this->productDraftRepository->findByIds($jobParameters->get('productDraftIds'));
        $productModelDrafts = $this->productModelDraftRepository->findByIds($jobParameters->get('productModelDraftIds'));
        $context = ['comment' => $jobParameters->get('comment')];

        if (null !== $productDrafts) {
            $this->processDrafts($productDrafts, $context);
        }

        if (null !== $productModelDrafts) {
            $this->processDrafts($productModelDrafts, $context);
        }
    }

    /**
     * Skip or approve given $drafts depending on permission
     *
     * @param mixed $drafts
     * @param array $context
     */
    protected function processDrafts($drafts, array $context): void
    {
        foreach ($drafts as $draft) {
            if ($this->permissionHelper->canEditOneChangeToReview($draft)) {
                try {
                    $this->approveDraft($draft, $context);
                    $this->stepExecution->incrementSummaryInfo('approved');
                } catch (DraftNotReviewableException $e) {
                    $this->skipWithWarning(
                        $this->stepExecution,
                        self::TASKLET_NAME,
                        $e->getMessage(),
                        ($prev = $e->getPrevious()) ? ['%error%' => $prev->getMessage()] : [],
                        $draft
                    );
                }
            } else {
                $this->skipWithWarning(
                    $this->stepExecution,
                    self::TASKLET_NAME,
                    self::ERROR_CANNOT_EDIT_ATTR,
                    [],
                    $draft->getEntityWithValue()
                );
            }
        }
    }

    /**
     * @param EntityWithValuesDraftInterface $draft
     * @param array                          $context
     *
     * @throws DraftNotReviewableException If draft cannot be approved
     */
    protected function approveDraft(EntityWithValuesDraftInterface $draft, array $context): void
    {
        if (EntityWithValuesDraftInterface::READY !== $draft->getStatus()) {
            throw new DraftNotReviewableException(self::ERROR_DRAFT_NOT_READY);
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $draft->getEntityWithValue())) {
            throw new DraftNotReviewableException(self::ERROR_NOT_PRODUCT_OWNER);
        }

        if ($draft instanceof ProductDraft) {
            $this->productDraftManager->approve($draft, $context);
        } elseif ($draft instanceof ProductModelDraft) {
            $this->productModelDraftManager->approve($draft, $context);
        }
    }
}