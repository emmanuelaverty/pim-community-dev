<?php

namespace Specification\Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Asset\Bundle\Connector\Processor\MassEdit\Asset\AddTagsToAssetsProcessor;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\TagInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddTagsToAssetsProcessorSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $repository,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($repository, $validator, $authorizationChecker);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddTagsToAssetsProcessor::class);
    }

    function it_is_a_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_adds_existing_tags_to_an_asset(
        $stepExecution,
        $repository,
        $validator,
        $authorizationChecker,
        AssetInterface $asset,
        JobParameters $jobParameters,
        TagInterface $foo,
        TagInterface $bar
    ) {
        $actions = [
            [
                'field' => 'tags',
                'value' => [
                    'foo',
                    'bar',
                ],
            ],
        ];

        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $repository->findOneByIdentifier('foo')->willReturn($foo);
        $repository->findOneByIdentifier('bar')->willReturn($bar);

        $asset->addTag($foo)->shouldBeCalled();
        $asset->addTag($bar)->shouldBeCalled();
        $validator->validate($asset)->willReturn(new ConstraintViolationList([]));

        $this->process($asset);
    }

    function it_does_not_add_non_existing_tags_to_an_asset(
        $stepExecution,
        $repository,
        $validator,
        $authorizationChecker,
        AssetInterface $asset,
        JobParameters $jobParameters
    ) {
        $actions = [
            [
                'field' => 'tags',
                'value' => ['foo'],
            ],
        ];

        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn($actions);
        $repository->findOneByIdentifier('foo')->willReturn(null);

        $asset->addTag()->shouldNotBeCalled();
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.add-tags-to-assets.message.error',
            [],
            Argument::type(InvalidItemInterface::class)
        )->shouldBeCalled();
        $validator->validate($asset)->willReturn(new ConstraintViolationList([]));

        $this->process($asset);
    }

    function it_does_not_add_tags_to_an_asset_non_editable_by_the_user(
        $stepExecution,
        $authorizationChecker,
        AssetInterface $asset
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $asset)->willReturn(false);

        $asset->getCode()->willReturn('akene');
        $asset->addTag()->shouldNotBeCalled();

        $stepExecution->addWarning(
            'pimee_product_asset.not_editable',
            ['%code%' => 'akene'],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skipped_assets')->shouldBeCalled();

        $this->process($asset);
    }
}
