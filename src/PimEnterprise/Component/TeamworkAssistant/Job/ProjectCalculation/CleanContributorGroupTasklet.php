<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository;
use PimEnterprise\Component\Security\Attributes;

/**
 * Step executed after a project calculation.
 * Clean the contributor groups depending on the locale permissions
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class CleanContributorGroupTasklet implements TaskletInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var LocaleAccessRepository */
    protected $localeAccessRepository;

    /** @var SaverInterface */
    protected $projectSaver;

    /**
     * @param LocaleAccessRepository                $localeAccessRepository
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param SaverInterface                        $projectSaver
     */
    public function __construct(
        LocaleAccessRepository $localeAccessRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->projectRepository = $projectRepository;
        $this->localeAccessRepository = $localeAccessRepository;
        $this->projectSaver = $projectSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $projectCode = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        $grantedContributorGroups = $this->localeAccessRepository->getGrantedUserGroups(
            $project->getLocale(),
            Attributes::EDIT_ITEMS
        );

        if ($this->isLocaleGrantedToAll($grantedContributorGroups)) {
            return null;
        }

        foreach ($project->getUserGroups() as $projectContributorGroup) {
            if (!in_array($projectContributorGroup, $grantedContributorGroups)) {
                $project->removeUserGroup($projectContributorGroup);
            }
        }

        $this->projectSaver->save($project);
    }

    /**
     * Check if the project local is granted to all user groups
     *
     * @param GroupInterface[] $grantedContributorGroups
     *
     * @return bool
     */
    protected function isLocaleGrantedToAll(array $grantedContributorGroups)
    {
        foreach ($grantedContributorGroups as $grantedContributorGroup) {
            if ('All' === $grantedContributorGroup->getName()) {
                return true;
            }
        }

        return false;
    }
}
