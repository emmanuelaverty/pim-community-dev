<?php

namespace spec\PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;

class JobProfilePermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        JobProfileAccessManager $accessManager,
        SecurityContextInterface $securityContext,
        FormEvent $event,
        Form $form,
        JobInstance $jobInstance,
        Form $executeForm,
        Form $editForm
    ) {
        $this->beConstructedWith($accessManager, $securityContext);

        $event->getData()->willReturn($jobInstance);
        $event->getForm()->willReturn($form);

        $form->get('permissions')->willReturn($form);
        $form->get('execute')->willReturn($executeForm);
        $form->get('edit')->willReturn($editForm);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_post_set_data_and_post_submit_form_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                FormEvents::PRE_SET_DATA  => 'preSetData',
                FormEvents::POST_SET_DATA => 'postSetData',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            ]
        );
    }

    function it_adds_permissions_to_the_form(FormEvent $event, Form $form)
    {
        $event->getForm()->willReturn($form);
        $form->add('permissions', 'pimee_import_export_job_profile_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_roles_in_the_form_data(
        $event,
        $jobInstance,
        $executeForm,
        $editForm,
        $accessManager
    ) {
        $jobInstance->getId()->willReturn(1);

        $accessManager->getExecuteRoles($jobInstance)->willReturn(['foo', 'bar', 'baz']);
        $accessManager->getEditRoles($jobInstance)->willReturn(['bar', 'baz']);

        $executeForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid(
        $event,
        $form,
        $jobInstance,
        $executeForm,
        $editForm,
        $accessManager,
        $securityContext
    ) {
        $jobInstance->getType()->willReturn('import');

        $form->isValid()->willReturn(true);
        $securityContext->isGranted(Argument::any())->willReturn(true);

        $executeForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);

        $accessManager->setAccess($jobInstance, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid(
        FormEvent $event,
        JobInstance $jobInstance,
        $form,
        $accessManager
    ) {
        $jobInstance->getType()->willReturn('import');

        $form->isValid()->willReturn(false);
        $accessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_user_has_not_permissions_to_do_it(
        $event,
        $jobInstance,
        $form,
        $accessManager,
        $securityContext
    ) {
        $jobInstance->getType()->willReturn('import');

        $securityContext->isGranted(Argument::any())->willReturn(false);
        $form->isValid()->willReturn(true);

        $accessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
