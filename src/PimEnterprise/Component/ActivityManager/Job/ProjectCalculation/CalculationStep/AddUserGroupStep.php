<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\AttributePermissionRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Find contributor groups (user groups which have edit on the product) affected by the project and
 * add them to the project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddUserGroupStep implements CalculationStepInterface
{
    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepository;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var AttributePermissionRepositoryInterface */
    protected $attributePermissionRepository;

    /**
     * @param ObjectDetacherInterface                $objectDetacher
     * @param CategoryAccessRepository               $categoryAccessRepository
     * @param FamilyRequirementRepositoryInterface   $familyRequirementRepository
     * @param AttributePermissionRepositoryInterface $attributePermissionRepository
     */
    public function __construct(
        ObjectDetacherInterface $objectDetacher,
        CategoryAccessRepository $categoryAccessRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        AttributePermissionRepositoryInterface $attributePermissionRepository
    ) {
        $this->objectDetacher = $objectDetacher;
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->attributePermissionRepository = $attributePermissionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $productContributorsGroupNames = $this->findUserGroupNamesForProduct($product);
        $attributeContributorGroups = $this->findUserGroupForAttribute($product, $project);

        foreach ($attributeContributorGroups as $attributeUserGroup) {
            if (0 === count($productContributorsGroupNames) && 0 === count($product->getCategories())) {
                $project->addUserGroup($attributeUserGroup);
            } elseif (in_array($attributeUserGroup->getName(), $productContributorsGroupNames, true)) {
                $project->addUserGroup($attributeUserGroup);
            }
        }
    }

    /**
     * Find contributor group names that can edit a product (category permission).
     *
     * @param ProductInterface $product
     *
     * @return string[]
     */
    protected function findUserGroupNamesForProduct(ProductInterface $product)
    {
        $contributors = $this->categoryAccessRepository->getGrantedUserGroupsForProduct(
            $product,
            Attributes::EDIT_ITEMS
        );

        return array_column($contributors, 'name');
    }

    /**
     * Find contributor groups that can edit at least one product attribute (attribute GroupInterface permission).
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     *
     * @return GroupInterface[]
     */
    protected function findUserGroupForAttribute(ProductInterface $product, ProjectInterface $project)
    {
        $attributeGroupIdentifiers = $this->familyRequirementRepository->findAttributeGroupIdentifiers(
            $product->getFamily(),
            $project->getChannel()
        );

        return $this->attributePermissionRepository->findContributorsUserGroups($attributeGroupIdentifiers);
    }
}