<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Class RuleRelationManager
 *
 * TODO : this "manager"  shortcuts to repository which is a service and could
 * be directly injected to avoid to add bunch of mess in this "Manager", let's avoid the systematic "Manager" naming
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationManager
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    //TODO: use a real interface fir this repo
    /** @var EntityRepository */
    protected $ruleRelationRepo;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param EntityManager       $entityManager
     * @param AttributeRepository $attributeRepository
     * @param EntityRepository    $ruleRelationRepo
     * @param string              $attributeClass
     */
    public function __construct(
        EntityManager $entityManager,
        AttributeRepository $attributeRepository,
        EntityRepository $ruleRelationRepo,
        $attributeClass
    ) {
        $this->entityManager = $entityManager;
        $this->attributeRepository = $attributeRepository;
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Returns all impacted attributes
     *
     * @param array $actions
     *
     * @return array
     */
    public function getImpactedAttributes(array $actions)
    {
        $fields = [];
        foreach ($actions as $action) {
            if ($action instanceof ProductCopyValueActionInterface) {
                $fields[] = $action->getToField();
            } elseif ($action instanceof ProductSetValueActionInterface) {
                $fields[] = $action->getField();
            }
        }

        // TODO : check memory leak (argument var is the same than result)
        $fields = array_unique($fields);

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findByReference($field);
        }

        $impactedAttributes = array_filter($impactedAttributes);

        return $impactedAttributes;
    }

    /**
     * //TODO : phpdoc non-existing argument
     * @param int $attribute
     *
     * @return bool
     */
    public function isAttributeImpacted($attributeId)
    {
        return $this->ruleRelationRepo->isResourceImpactedByRule($attributeId, $this->attributeClass);
    }

    /**
     * @param int $attributeId
     *
     * @return RuleDefinitionInterface[]
     * TODO: rename it getRulesForResource
     * TODO: delete it
     */
    public function getRulesForAttribute($attributeId)
    {
        //TODO: We should do generic methods (not attribute related)
        return $this->getRulesForResource($attributeId, $this->attributeClass);
    }

    /**
     * Get rules related to a resource
     *
     * @param integer $resourceId
     * @param string  $resourceName
     *
     * @return array
     *
     * TODO all these things go in a repo !!!
     * TODO: make it public
     */
    protected function getRulesForResource($resourceId, $resourceName)
    {
        $ruleRelations = $this->getRuleRelationsForResource($resourceId, $resourceName);

        $rules = [];
        foreach ($ruleRelations as $ruleRelation) {
            $rules[] = $ruleRelation->getRule();
        }

        return $rules;
    }

    /**
     * Get rules relations
     * @param string $resourceId
     * @param string $resourceName
     *
     * TODO: it returns rulerelation[]
     * TODO: remove this, it's a shortcut to a repo
     * @return PersistentCollection
     */
    protected function getRuleRelationsForResource($resourceId, $resourceName)
    {
        //@TODO: move this in a repository and create a nice method
        return $this->ruleRelationRepo->findBy([
            'resourceId'   => $resourceId,
            'resourceName' => $resourceName
        ]);
    }
}
