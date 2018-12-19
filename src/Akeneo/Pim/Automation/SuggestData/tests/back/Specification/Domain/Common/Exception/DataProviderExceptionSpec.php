<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception\DataProviderException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DataProviderExceptionSpec extends ObjectBehavior
{
    public function it_is_an_identifier_mapping_exception(): void
    {
        $this->beConstructedWith('', [], 0, new \Exception());

        $this->shouldHaveType(DataProviderException::class);
        $this->shouldHaveType(\Exception::class);
    }

    public function it_builds_an_exception_if_ask_franklin_server_is_down(): void
    {
        $previousException = new \Exception();
        $this->beConstructedThrough('serverIsDown', [$previousException]);

        $this->getMessage()->shouldReturn('akeneo_suggest_data.entity.data_provider.constraint.ask_franklin_down');
        $this->getMessageParams()->shouldReturn([]);
        $this->getCode()->shouldReturn(500);
        $this->getPrevious()->shouldReturn($previousException);
    }
}
