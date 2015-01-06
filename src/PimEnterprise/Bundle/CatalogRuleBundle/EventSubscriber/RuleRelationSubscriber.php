<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Component\Persistence\RemoverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use PimEnterprise\Bundle\RuleEngineBundle\Event\BulkRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Rule relations subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationSubscriber implements EventSubscriberInterface
{
    /** @var RuleRelationManager */
    protected $ruleRelationManager;

    /** @var SaverInterface */
    protected $ruleRelationSaver;

    /** @var RemoverInterface */
    protected $ruleRelationRemover;

    //TODO: use a real interface here
    /** @var EntityRepository */
    protected $ruleRelationRepo;

    /** @var ProductRuleBuilder */
    protected $productRuleBuilder;

    /** @var string */
    protected $ruleRelationClass;

    /**
     * Constructor
     *
     * @param RuleRelationManager $ruleRelationManager
     * @param SaverInterface      $ruleRelationSaver
     * @param RemoverInterface    $ruleRelationRemover
     * @param EntityRepository    $ruleRelationRepo
     * @param ProductRuleBuilder  $productRuleBuilder
     * @param string              $ruleRelationClass
     */
    public function __construct(
        RuleRelationManager $ruleRelationManager,
        SaverInterface $ruleRelationSaver,
        RemoverInterface $ruleRelationRemover,
        EntityRepository $ruleRelationRepo,
        ProductRuleBuilder $productRuleBuilder,
        $ruleRelationClass
    ) {
        $this->ruleRelationManager = $ruleRelationManager;
        $this->ruleRelationSaver = $ruleRelationSaver;
        $this->ruleRelationRemover = $ruleRelationRemover;
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->productRuleBuilder = $productRuleBuilder;
        $this->ruleRelationClass = $ruleRelationClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::PRE_REMOVE => 'removeAttribute',
            RuleEvents::POST_SAVE       => 'saveRule',
            RuleEvents::POST_SAVE_ALL   => 'saveRules'
        ];
    }

    /**
     * Deletes a rule relation
     *
     * @param GenericEvent $event
     */
    public function removeAttribute(GenericEvent $event)
    {
        $entity = $event->getSubject();
        $ruleRelations = [];

        if ($entity instanceof AttributeInterface) {
            $ruleRelations = $this->ruleRelationRepo
                ->findBy(['resourceId' => $entity->getId(), 'resourceName' => ClassUtils::getClass($entity)]);
        }
        // TODO else InvalidArgumentException

        // TODO: use a bulk
        foreach ($ruleRelations as $ruleRelation) {
            $this->ruleRelationRemover->remove($ruleRelation);
        }
    }

    /**
     * When saves a single rule
     *
     * @param RuleEvent $event
     */
    public function saveRule(RuleEvent $event)
    {
        $definition = $event->getDefinition();
        $this->saveRuleRelations($definition);
    }

    /**
     * When saves many rules, via import for instance
     *
     * @param BulkRuleEvent $event
     */
    public function saveRules(BulkRuleEvent $event)
    {
        $definitions = $event->getDefinitions();
        foreach ($definitions as $definition) {
            $this->saveRuleRelations($definition);
        }
    }

    /**
     * Saves a rule relation
     *
     * @param RuleDefinitionInterface $definition
     */
    protected function saveRuleRelations(RuleDefinitionInterface $definition)
    {
        if (null === $definition->getId()) {
            return;
        }

        $this->removeRuleRelations($definition);
        $this->addRuleRelations($definition);
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function addRuleRelations(RuleDefinitionInterface $definition)
    {
        $rule = $this->productRuleBuilder->build($definition);
        $actions = $rule->getActions();
        $relatedAttributes = $this->ruleRelationManager->getImpactedAttributes($actions);

        foreach ($relatedAttributes as $relatedAttribute) {
            $ruleRelation = new $this->ruleRelationClass();
            $ruleRelation->setRule($definition);
            $ruleRelation->setResourceName(ClassUtils::getClass($relatedAttribute));
            $ruleRelation->setResourceId($relatedAttribute->getId());

            $this->ruleRelationSaver->save($ruleRelation);
        }
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function removeRuleRelations(RuleDefinitionInterface $definition)
    {
        $ruleRelations = $this->ruleRelationRepo->findBy(['rule' => $definition->getId()]);

        //TODO: use a bulk
        foreach ($ruleRelations as $resource) {
            $this->ruleRelationRemover->remove($resource);
        }
    }
}
