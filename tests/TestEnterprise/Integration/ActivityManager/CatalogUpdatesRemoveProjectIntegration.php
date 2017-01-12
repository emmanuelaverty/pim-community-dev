<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\TestEnterprise\Integration\ActivityManager;

class CatalogUpdatesRemoveProjectIntegration extends ActivityManagerTestCase
{
    /** {@inheritdoc} */
    protected $purgeDatabaseForEachTest = true;

    /**
     * A project has to be removed if its channel is removed.
     */
    public function testRemoveChannelRemovesAssociatedProjects()
    {
        $channelRemover = $this->get('pim_catalog.remover.channel');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $project = $this->createProject([
            'label' => 'High-Tech project',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'mobile',
            'product_filters' =>[
                [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['high_tech'],
                    'context' => ['locale' => 'en_US', 'scope' => 'mobile'],
                ],
            ],
        ]);
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $mobileChannel = $channelRepository->findOneByIdentifier('mobile');
        $channelRemover->remove($mobileChannel);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertNull($result, 'Project not removed after its channel has been removed.');
    }

    /**
     * A project has to be removed if its locale is now deactivated or if its locale is no longer part
     * of its channel locales.
     */
    public function testDeactivateLocaleRemovesAssociatedProjects()
    {
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $channelSaver = $this->get('pim_catalog.saver.channel');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $project = $this->createProject([
            'label' => 'High-Tech project',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'mobile',
            'product_filters' =>[
                [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['high_tech'],
                    'context' => ['locale' => 'en_US', 'scope' => 'mobile'],
                ],
            ],
        ]);
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $locale = $localeRepository->findOneByIdentifier('en_US');
        $channel = $channelRepository->findOneByIdentifier('mobile');
        $channel->removeLocale($locale);
        $channelSaver->save($channel);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertTrue(null === $result, 'Project not removed after its locale has been deactivated.');
    }
}
