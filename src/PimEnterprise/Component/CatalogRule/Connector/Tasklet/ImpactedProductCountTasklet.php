<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Tasklet;

use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * Calculation of the count of impacted products by the rules
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ImpactedProductCountTasklet implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleDefinitionRepo;

    /** @var DryRunnerInterface */
    protected $productRuleRunner;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var BulkObjectDetacherInterface */
    protected $detacher;

    /**
     * @param RuleDefinitionRepositoryInterface $ruleDefinitionRepo
     * @param DryRunnerInterface                $productRuleRunner
     * @param BulkSaverInterface                $saver
     * @param BulkObjectDetacherInterface       $detacher
     */
    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        BulkObjectDetacherInterface $detacher
    ) {
        $this->ruleDefinitionRepo = $ruleDefinitionRepo;
        $this->productRuleRunner = $productRuleRunner;
        $this->saver = $saver;
        $this->detacher = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $ruleDefinitions = $this->ruleDefinitionRepo->findBy(['id' => $jobParameters->get('ruleIds')]);
        foreach ($ruleDefinitions as $ruleDefinition) {
            $ruleSubjectSet = $this->productRuleRunner->dryRun($ruleDefinition);
            $ruleDefinition->setImpactedSubjectCount($ruleSubjectSet->getSubjectsCursor()->count());

            $this->stepExecution->incrementSummaryInfo('rule_calculated');
        }

        $this->saver->saveAll($ruleDefinitions);
        $this->detacher->detachAll($ruleDefinitions);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }
}
