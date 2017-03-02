<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\ProjectCompletenessRepository;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

class ProjectCompletenessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, TableNameMapper $tableNameMapper)
    {
        $this->beConstructedWith($entityManager, $tableNameMapper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCompletenessRepository::class);
    }

    function it_is_a_project_completeness_repository()
    {
        $this->shouldImplement(ProjectCompletenessRepositoryInterface::class);
    }
}