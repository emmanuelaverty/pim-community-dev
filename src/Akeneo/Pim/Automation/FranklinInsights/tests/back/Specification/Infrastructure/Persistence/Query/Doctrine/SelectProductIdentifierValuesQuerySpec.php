<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectProductIdentifierValuesQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectProductIdentifierValuesQuerySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_select_product_identifier_values_query(): void
    {
        $this->shouldImplement(SelectProductIdentifierValuesQueryInterface::class);
    }

    public function it_is_a_doctrine_implementation_of_a_select_product_identifier_values_query(): void
    {
        $this->shouldBeAnInstanceOf(SelectProductIdentifierValuesQuery::class);
    }

    public function it_returns_product_identifier_values_collection_read_model(
        $connection,
        Statement $statement
    ): void {
        $statement->fetchAll()->willReturn(
            [
                [
                    'productId' => '42',
                    'mapped_identifier_values' => '{"asin":"ABC456789","upc":"012345678912"}',
                ],
            ]
        );
        $connection->executeQuery(
            Argument::type('string'),
            ['product_ids' => [42]],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        )->willReturn($statement);

        $result = $this->execute([42]);
        $result->shouldHaveType(ProductIdentifierValuesCollection::class);
        $values = $result->get(42);
        $values->shouldHaveType(ProductIdentifierValues::class);

        $values->getValue('asin')->shouldReturn('ABC456789');
        $values->getValue('upc')->shouldReturn('012345678912');
        $values->getValue('mpn')->shouldReturn(null);
        $values->getValue('brand')->shouldReturn(null);
    }
}