<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotificationFactory;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;

class ProjectCreatedNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter
    ) {
        $this->beConstructedWith($projectNotificationFactory, $notifier, $datePresenter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreatedNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_does_not_notify_owner_that_a_project_is_created(
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatus->hasBeenNotified()->willReturn(true);
        $projectCompleteness->isComplete()->willReturn(true);

        $this->notifyUser($owner, $project, $projectStatus, $projectCompleteness)->shouldReturn(false);
    }

    function it_notifies_contributors_that_a_project_is_created(
        $projectNotificationFactory,
        $notifier,
        $datePresenter,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness,
        LocaleInterface $locale
    ) {
        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatus->hasBeenNotified()->willReturn(false);

        $project->getOwner()->willReturn($owner);
        $contributor->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present('01/12/2030', ['locale' => 'en_US'])->willReturn('12/01/2030');
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');
        $project->getDueDate()->willReturn('01/12/2030');

        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('claude');
        $project->getOwner()->willReturn($owner);

        $context = [
            'actionType'  => 'project_calculation',
            'buttonLabel' => 'activity_manager.notification.project_calculation.start'
        ];

        $parameters = ['%project_label%' => 'Project label', '%due_date%' => '12/01/2030'];

        $projectNotificationFactory->create(
            ['identifier' => 'project-code'],
            $parameters,
            $context,
            'activity_manager.notification.message'
        )->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project, $projectStatus, $projectCompleteness)->shouldReturn(true);
    }
}
