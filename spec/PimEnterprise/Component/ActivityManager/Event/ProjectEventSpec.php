<?php

namespace spec\Akeneo\ActivityManager\Component\Event;

use Akeneo\ActivityManager\Component\Event\ProjectEvent;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ProjectEventSpec extends ObjectBehavior
{
    function let(ProjectInterface $project)
    {
        $this->beConstructedWith($project);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvent::class);
    }

    function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_has_a_project($project)
    {
        $this->getProject()->shouldReturn($project);
    }
}
