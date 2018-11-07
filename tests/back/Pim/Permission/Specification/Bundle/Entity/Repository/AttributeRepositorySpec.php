<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeRepository;
use Akeneo\Pim\Permission\Bundle\Filter\AttributeViewRightFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata, AttributeViewRightFilter $attributeFilter)
    {
        $classMetadata->name = 'attribute';

        $em->getClassMetadata('attributeClass')->willReturn($classMetadata);

        $this->beConstructedWith($em, $attributeFilter, 'attributeClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRepository::class);
    }

    function it_is_translated_label_provider()
    {
        $this->shouldImplement(TranslatedLabelsProviderInterface::class);
    }

    function it_is_doctrine_provider()
    {
        $this->shouldHaveType(EntityRepository::class);
    }

    function it_provides_translated_labels(
        $em,
        $attributeFilter,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ArrayCollection $attributes,
        AttributeInterface $attribute,
        GroupInterface $group,
        Expr $expr
    ) {
        $queryBuilder->expr()->willReturn($expr);
        $expr->eq('a.useableAsGridFilter', true)->willReturn($expr);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('a')->willReturn($queryBuilder);
        $queryBuilder->from('attribute', 'a', null)->willReturn($queryBuilder);
        $queryBuilder->where($expr)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn($attributes);

        $attributeFilter->filterCollection($attributes, 'pim.internal_api.attribute.view')->willReturn([$attribute]);

        $attribute->getCode()->willReturn('code');
        $attribute->getLabel()->willReturn('label');
        $attribute->getGroup()->willReturn($group);
        $group->getLabel()->willReturn('group_label');

        $this->findTranslatedLabels()->shouldReturn([
            'group_label' => [
                'label' => 'code'
            ]
        ]);
    }
}