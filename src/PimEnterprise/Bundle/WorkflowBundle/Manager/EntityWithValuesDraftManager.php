<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Applier\DraftApplierInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Exception\DraftNotReviewableException;
use PimEnterprise\Component\Workflow\Factory\EntityWithValuesDraftFactory;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Manage product product drafts
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EntityWithValuesDraftManager
{
    /** @var SaverInterface */
    protected $workingCopySaver;

    /** @var UserContext */
    protected $userContext;

    /** @var EntityWithValuesDraftFactory */
    protected $factory;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /** @var DraftApplierInterface */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var SaverInterface */
    protected $productDraftSaver;

    /** @var RemoverInterface */
    protected $productDraftRemover;

    /** @var CollectionFilterInterface */
    protected $valuesFilter;

    public function __construct(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        EntityWithValuesDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $repository,
        DraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $productDraftSaver,
        RemoverInterface $productDraftRemover,
        CollectionFilterInterface $valuesFilter
    ) {
        $this->workingCopySaver = $workingCopySaver;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->productDraftSaver = $productDraftSaver;
        $this->productDraftRemover = $productDraftRemover;
        $this->valuesFilter = $valuesFilter;
    }

    /**
     * Approve a single "ready to review" change of the given $productDraft.
     * This approval is only applied if current user have edit rights on the change.
     *
     * To do that we create a temporary draft that contains the change that we want to apply,
     * then we apply this temporary draft and remove this change from the original one.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param AttributeInterface                   $attribute
     * @param LocaleInterface|null                 $locale
     * @param ChannelInterface|null                $channel
     * @param array                                $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $productDraft is not ready to be reviewed or if no permission
     *                                     to approve the given change.
     */
    public function approveChange(
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_APPROVE, new GenericEvent($productDraft, $context));

        if (EntityWithValuesDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException('A product draft not in ready state can not be partially approved');
        }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $data = $productDraft->getChange($attribute->getCode(), $localeCode, $channelCode);
        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale' => $localeCode, 'scope' => $channelCode, 'data' => $data]]
            ],
            'pim.internal_api.attribute.edit'
        );

        if (empty($filteredValues)) {
            throw new DraftNotReviewableException('Impossible to approve a single change without permission on it');
        }

        $partialDraft = $this->createDraft($productDraft, $filteredValues);
        $this->applyDraftOnProduct($partialDraft);
        $this->removeDraftChanges($productDraft, $filteredValues);

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_PARTIAL_APPROVE,
            new GenericEvent($productDraft, $context)
        );
    }

    /**
     * Approve all "ready to review" changes of the given $productDraft.
     * This approval is only applied if current user have edit rights on the change, so if
     * not all changes can be approved, a "partial approval" is done instead.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $productDraft is not ready to be reviewed.
     */
    public function approve(EntityWithValuesDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, new GenericEvent($productDraft, $context));

        if (EntityWithValuesDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException('A product draft not in ready state can not be approved');
        }

        $productDraftChanges = $productDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $productDraftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        $isPartial = ($filteredValues != $productDraftChanges['values']);

        if (!empty($filteredValues)) {
            $draftToApply = $isPartial ? $this->createDraft($productDraft, $filteredValues) : $productDraft;

            $this->applyDraftOnProduct($draftToApply);
            $this->removeDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $productDraftChanges['values'];
        $context['isPartial'] = $isPartial;

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Refuse a single "ready to review" change of the given $productDraft.
     * This refusal is only applied if current user have edit rights on the change.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param AttributeInterface                   $attribute
     * @param LocaleInterface|null                 $locale
     * @param ChannelInterface|null                $channel
     * @param array                                $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $productDraft is not ready to be reviewed or if no permission to
     *                                     refuse the given change.
     */
    public function refuseChange(
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_REFUSE, new GenericEvent($productDraft, $context));

        if (EntityWithValuesDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException('A product draft not in ready state can not be partially rejected');
        }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale'  => $localeCode, 'scope' => $channelCode]]
            ],
            'pim.internal_api.attribute.edit'
        );

        if (empty($filteredValues)) {
            throw new DraftNotReviewableException('Impossible to refuse a single change without permission on it');
        }

        $this->refuseDraftChanges($productDraft, $filteredValues);

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_PARTIAL_REFUSE,
            new GenericEvent($productDraft, $context)
        );
    }

    /**
     * Refuse all "ready to review" changes of the given $productDraft.
     * This refusal is only applied if current user have edit rights on the change, so if
     * not all changes can be refused, a "partial refusal" is done instead.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $context
     *
     * @throws DraftNotReviewableException If the $productDraft is not ready to be reviewed.
     */
    public function refuse(EntityWithValuesDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, new GenericEvent($productDraft, $context));

        if (EntityWithValuesDraftInterface::READY !== $productDraft->getStatus()) {
            throw new DraftNotReviewableException('A product draft not in ready state can not be rejected');
        }

        $productDraftChanges = $productDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $productDraftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        if (!empty($filteredValues)) {
            $this->refuseDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $productDraftChanges['values'];
        $context['isPartial'] = ($filteredValues != $productDraftChanges['values']);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REFUSE, new GenericEvent($productDraft, $context));
    }

    /**
     * Remove an in progress product draft.
     * This removal is only applied if current user have edit rights on the change, so if
     * not all changes can be removed, a "partial removal" is done instead.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $context
     *
     * @throws DraftNotReviewableException If the $productDraft is not in progress or if no permission to remove the draft.
     */
    public function remove(EntityWithValuesDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REMOVE, new GenericEvent($productDraft, $context));

        if (EntityWithValuesDraftInterface::READY === $productDraft->getStatus()) {
            throw new DraftNotReviewableException('A product draft in ready state can not be removed');
        }

        $productDraftChanges = $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection(
            $productDraftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        if (empty($filteredValues)) {
            throw new DraftNotReviewableException('Impossible to delete a draft if no permission at all on it');
        }

        $isPartial = ($filteredValues != $productDraftChanges['values']);

        if (!$isPartial) {
            $this->productDraftRemover->remove($productDraft);
        } else {
            $this->removeDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $productDraftChanges['values'];
        $context['isPartial'] = $isPartial;

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REMOVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Find or create a product draft
     *
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return EntityWithValuesDraftInterface
     */
    public function findOrCreate(ProductInterface $product)
    {
        if (null === $this->userContext->getUser()) {
            throw new \LogicException('Current user cannot be resolved');
        }
        $username = $this->userContext->getUser()->getUsername();
        $productDraft = $this->repository->findUserEntityWithValuesDraft($product, $username);

        if (null === $productDraft) {
            $productDraft = $this->factory->createEntityWithValueDraft($product, $username);
        }

        return $productDraft;
    }

    /**
     * Mark a product draft as ready
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param string                               $comment
     */
    public function markAsReady(EntityWithValuesDraftInterface $productDraft, $comment = null)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_READY, new GenericEvent($productDraft));

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $this->productDraftSaver->save($productDraft);

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_READY,
            new GenericEvent($productDraft, ['comment' => $comment])
        );
    }

    /**
     * Create a draft with the given changes.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $draftChanges
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createDraft(EntityWithValuesDraftInterface $productDraft, array $draftChanges)
    {
        $partialDraft = $this->factory->createEntityWithValueDraft($productDraft->getEntityWithValue(), $productDraft->getAuthor());
        $partialDraft->setChanges([
            'values' => $draftChanges
        ]);

        return $partialDraft;
    }

    /**
     * Apply a draft on a product. The product is saved.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     */
    protected function applyDraftOnProduct(EntityWithValuesDraftInterface $productDraft)
    {
        $product = $productDraft->getEntityWithValue();
        $isPartialDraft = null === $productDraft->getId();

        if ($isPartialDraft) {
            $this->applier->applyAllChanges($product, $productDraft);
        } else {
            $this->applier->applyToReviewChanges($product, $productDraft);
        }

        $this->workingCopySaver->save($product);
    }

    /**
     * Refuse changes from a draft. The draft is saved.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $refusedChanges
     */
    protected function refuseDraftChanges(EntityWithValuesDraftInterface $productDraft, array $refusedChanges)
    {
        foreach ($refusedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $productDraft->setReviewStatusForChange(
                    EntityWithValuesDraftInterface::CHANGE_DRAFT,
                    $attributeCode,
                    $value['locale'],
                    $value['scope']
                );
                $valueToRemove = $productDraft->getValues()->getByCodes(
                    $attributeCode,
                    $value['scope'],
                    $value['locale']
                );
                if (null !== $valueToRemove) {
                    $productDraft->getValues()->remove($valueToRemove);
                }
            }
        }

        $this->productDraftSaver->save($productDraft);
    }

    /**
     * Remove the given changes from a draft and saves it.
     * It the draft has no more changes, it is removed.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $appliedChanges
     */
    protected function removeDraftChanges(EntityWithValuesDraftInterface $productDraft, array $appliedChanges)
    {
        foreach ($appliedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $productDraft->removeChange($attributeCode, $value['locale'], $value['scope']);
                $valueToRemove = $productDraft->getValues()->getByCodes(
                    $attributeCode,
                    $value['scope'],
                    $value['locale']
                );
                if (null !== $valueToRemove) {
                    $productDraft->getValues()->remove($valueToRemove);
                }
            }
        }

        if (!$productDraft->hasChanges()) {
            $this->productDraftRemover->remove($productDraft);
        } else {
            $this->productDraftSaver->save($productDraft);
        }
    }
}
