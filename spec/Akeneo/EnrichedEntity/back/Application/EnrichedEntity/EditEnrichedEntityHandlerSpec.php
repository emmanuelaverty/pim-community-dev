<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EditEnrichedEntityCommand;
use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditEnrichedEntityHandlerSpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditEnrichedEntityHandler::class);
    }

    function it_edits_an_enriched_entity(
        EnrichedEntityRepository $repository,
        EnrichedEntity $enrichedEntity,
        EditEnrichedEntityCommand $editEnrichedEntityCommand
    ) {
        $editEnrichedEntityCommand->identifier = 'designer';
        $editEnrichedEntityCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];

        $repository->findOneByIdentifier(Argument::type(EnrichedEntityIdentifier::class))
            ->willReturn($enrichedEntity);

        $enrichedEntity->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $repository->update($enrichedEntity)->shouldBeCalled();

        $this->__invoke($editEnrichedEntityCommand);
    }
}
