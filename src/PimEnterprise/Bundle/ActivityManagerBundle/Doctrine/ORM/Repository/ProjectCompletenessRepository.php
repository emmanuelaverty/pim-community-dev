<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use Doctrine\ORM\EntityManager;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessRepository implements ProjectCompletenessRepositoryInterface
{
    /** @var EntityManager */
    protected $entityManger;

    /** @var TableNameMapper */
    protected $tableNameMapper;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, TableNameMapper $tableNameMapper)
    {
        $this->entityManger = $entityManager;
        $this->tableNameMapper = $tableNameMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectCompleteness(ProjectInterface $project, $username = null)
    {
        $query = $this->buildSqlQuery($username);
        $parameters = $this->buildQueryParameters($project, $username);
        $completeness = $this->entityManger->getConnection()->fetchAssoc($query, $parameters);

        return new ProjectCompleteness($completeness['todo'], $completeness['in_progress'], $completeness['done']);
    }

    /**
     * Build your query parameters.
     *
     * @param ProjectInterface $project
     * @param string|null      $username
     *
     * @return array
     */
    protected function buildQueryParameters(ProjectInterface $project, $username = null)
    {
        $parameters = [
            'project_id' => $project->getId(),
            'channel_id' => $project->getChannel()->getId(),
            'locale_id'  => $project->getLocale()->getId(),
        ];

        if (null !== $username) {
            $parameters['username'] = $username;
        }

        return $parameters;
    }

    /**
     * Build the project completeness query.
     *
     * @param string|null $username
     *
     * @return string
     */
    protected function buildSqlQuery($username = null)
    {
        $filterByCategoryPermissionJoins =
        $filterByCategoryPermissionByConditions =
        $filterByAttributeGroupPermissions =
        $filterByLocalePermissions = '';

        if (null !== $username) {
            // Filter on the categories the user can edit
            $filterByCategoryPermissionJoins = <<<FILTER_USER
LEFT JOIN `@pim_user.entity.user#groups@` AS `user_group`
    ON `product_category_access`.`user_group_id` = `user_group`.`group_id` 
LEFT JOIN `@pim_user.entity.user@` AS `user`
    ON `user_group`.`user_id` = `user`.`id`
FILTER_USER;

            $filterByCategoryPermissionByConditions = <<<FILTER_USER
AND (`user`.`username` = :username OR `user`.`username` IS NULL)
FILTER_USER;

            // Filter on the attribute groups the user can edit
            $filterByAttributeGroupPermissions = <<<ATTRIBUTE_GROUP_FILTER
AND `completeness_per_attribute_group`.`attribute_group_id` IN (
    SELECT DISTINCT `attribute_group_access`.`attribute_group_id`
    FROM `@pimee_activity_manager.model.project#userGroups@` AS `project_contributor_group`
    INNER JOIN `@pimee_security.entity.attribute_group_access@` AS `attribute_group_access`
        ON `project_contributor_group`.`user_group_id` = `attribute_group_access`.`user_group_id`
    INNER JOIN `@pim_user.entity.user#groups@` AS `user_group`
        ON `user_group`.`group_id` = `project_contributor_group`.`user_group_id`
    INNER JOIN `@pim_user.entity.user@` AS `user`
        ON `user_group`.`user_id` = `user`.`id`
    WHERE `project_contributor_group`.`project_id` = :project_id
    AND `attribute_group_access`.`edit_attributes` = 1
    AND  `user`.`username` = :username 
)
ATTRIBUTE_GROUP_FILTER;

            // Filter on the locale the user can edit
            $filterByLocalePermissions = <<<LOCALE_FILTER
AND `completeness_per_attribute_group`.`locale_id` IN (
    SELECT DISTINCT `locale_access`.`locale_id`
    FROM `@pimee_activity_manager.model.project#userGroups@` AS `project_contributor_group`
    INNER JOIN `@pimee_security.entity.locale_access@` AS `locale_access`
        ON `project_contributor_group`.`user_group_id` = `locale_access`.`user_group_id`
    INNER JOIN `@pim_user.entity.user#groups@` AS `user_group`
        ON `user_group`.`group_id` = `project_contributor_group`.`user_group_id`
    INNER JOIN `@pim_user.entity.user@` AS `user`
        ON `user_group`.`user_id` = `user`.`id`
    WHERE `project_contributor_group`.`project_id` = :project_id
    AND `locale_access`.`edit_products` = 1
    AND  `user`.`username` = :username 
)
LOCALE_FILTER;
        }

        $sql = <<<SQL
SELECT
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_in_progress` = 0 AND `attribute_group_done` = 0
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `todo`,
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_done` <> `total_attribute_group` AND (`attribute_group_in_progress` > 0 OR `attribute_group_done` > 0)
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `in_progress`,
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_done` = `total_attribute_group`
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `done`
FROM (
    SELECT 
		SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) AS `attribute_group_in_progress`,
		SUM(`completeness_per_attribute_group`.`is_complete`) AS `attribute_group_done`,
		COUNT(`completeness_per_attribute_group`.`product_id`) AS `total_attribute_group`
    FROM `@pimee_activity_manager.completeness_per_attribute_group@` AS `completeness_per_attribute_group`
    WHERE `completeness_per_attribute_group`.`channel_id` = :channel_id
    AND `completeness_per_attribute_group`.`locale_id` = :locale_id
    AND `completeness_per_attribute_group`.`product_id` IN (
        SELECT DISTINCT `project_product`.`product_id`
        FROM `@pimee_activity_manager.project_product@` AS `project_product`
        LEFT JOIN `@pim_catalog.entity.product#categories@` AS `category_product`
            ON `project_product`.`product_id` = `category_product`.`product_id`
        LEFT JOIN `@pimee_security.entity.product_category_access@` AS `product_category_access`
            ON `category_product`.`category_id` = `product_category_access`.`category_id`
        $filterByCategoryPermissionJoins
        WHERE `project_product`.`project_id` = :project_id
        AND (`product_category_access`.`edit_items` = 1 OR `product_category_access`.`edit_items` IS NULL)
        $filterByCategoryPermissionByConditions
    )
    $filterByAttributeGroupPermissions
    $filterByLocalePermissions
	GROUP BY `completeness_per_attribute_group`.`product_id`
) `completeness`
SQL;

        return $this->tableNameMapper->createQuery($sql);
    }
}