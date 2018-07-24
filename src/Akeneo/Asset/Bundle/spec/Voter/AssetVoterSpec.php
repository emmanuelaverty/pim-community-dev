<?php

namespace spec\Akeneo\Asset\Bundle\Voter;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Asset\Bundle\Voter\AssetVoter;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Asset\Component\Model\AssetInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AssetVoterSpec extends ObjectBehavior
{
    protected $attributes = [ Attributes::VIEW, Attributes::EDIT];

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Asset\Bundle\Voter\AssetVoter');
    }

    function let(CategoryAccessRepository $categoryAccessRepository, TokenInterface $token, UserInterface $user)
    {
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($categoryAccessRepository);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, AssetVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $categoryAccessRepository,
        $token,
        $user,
        AssetInterface $asset,
        CategoryInterface $categoryFive,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->isCategoriesGranted($user, Attributes::EDIT_ITEMS, [5, 6])->willReturn(false);
        $asset->getCategories()->willReturn([$categoryFive, $categorySix]);
        $categoryFive->getId()->willReturn(5);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $asset, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $categoryAccessRepository,
        $token,
        $user,
        AssetInterface $asset,
        CategoryInterface $categoryOne,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->isCategoriesGranted($user, Attributes::EDIT_ITEMS, [1, 6])->willReturn(true);
        $asset->getCategories()->willReturn([$categoryOne, $categorySix]);
        $categoryOne->getId()->willReturn(1);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $asset, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}