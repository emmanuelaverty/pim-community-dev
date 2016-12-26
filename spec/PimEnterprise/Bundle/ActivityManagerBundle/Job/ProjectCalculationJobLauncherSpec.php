<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Job;

use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;

class ProjectCalculationJobLauncherSpec extends ObjectBehavior
{
    function let(JobLauncherInterface $simpleJobLauncher, JobInstanceRepository $jobInstanceRepository)
    {
        $this->beConstructedWith($simpleJobLauncher, $jobInstanceRepository, 'job_name');
    }

    function it_launches_a_project_calculation_job(
        $simpleJobLauncher,
        $jobInstanceRepository,
        UserInterface $user,
        ProjectInterface $project,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);

        $project->getCode()->willReturn('project_code');

        $configuration = ['project_code' => 'project_code'];

        $simpleJobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalled();

        $this->launch($user, $project);
    }
}
