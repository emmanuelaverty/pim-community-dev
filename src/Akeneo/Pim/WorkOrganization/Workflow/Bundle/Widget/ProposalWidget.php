<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Widget to display proposals
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProposalWidget implements WidgetInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var UserManager */
    protected $userManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var PresenterInterface */
    protected $presenter;

    /** @var RouterInterface */
    protected $router;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->presenter = $presenter;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'proposals';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimEnterpriseDashboardBundle:Widget:proposal.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return ['show' => $this->isDisplayable()];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->isDisplayable()) {
            return [];
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $result = [];
        $productProposals = $this->productDraftRepository->findApprovableByUser($user, 10);
        $productModelProposals = $this->productModelDraftRepository->findApprovableByUser($user, 10);

        $proposals = array_merge($productProposals, $productModelProposals);

        $locale = $user->getUiLocale()->getCode();

        $route = $this->router->generate('pimee_workflow_proposal_index');

        foreach ($proposals as $proposal) {
            $result[] = [
                'productId'        => $proposal->getEntityWithValue()->getId(),
                'productLabel'     => $proposal->getEntityWithValue()->getLabel(),
                'authorFullName'   => $this->getAuthorFullName($proposal->getAuthor()),
                'productReviewUrl' => $route . $this->getProposalGridParametersAsUrl(
                        $proposal->getAuthor(),
                        $proposal instanceof ProductDraft ? $proposal->getEntityWithValue()->getIdentifier() : $proposal->getEntityWithValue()->getCode()
                    ),
                'createdAt' => $this->presenter->present(
                    $proposal->getCreatedAt(),
                    [
                        'locale'   => $locale,
                        'timezone' => $user->getTimezone(),
                    ]
                )
            ];
        }

        return $result;
    }

    /**
     * Indicates if the widget should be displayed to the current user
     *
     * @return bool
     */
    protected function isDisplayable()
    {
        return $this->authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY);
    }

    /**
     * Get author full name for given $authorUsername
     *
     * @param string $authorUsername
     *
     * @return string
     */
    protected function getAuthorFullName($authorUsername)
    {
        $user = $this->userManager->findUserByUsername($authorUsername);
        $authorName = $authorUsername;

        if ($user) {
            $authorName = sprintf('%s %s', $user->getFirstName(), $user->getLastName());
        }

        return $authorName;
    }

    /**
     * @param string     $authorUsername
     * @param string|int $productIdentifier
     *
     * @return string
     */
    protected function getProposalGridParametersAsUrl($authorUsername, $productIdentifier)
    {
        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $authorUsername,
                    ],
                ],
                'identifier'    => [
                    'value' => $productIdentifier,
                    'type' => 1,
                ],
            ],
        ];

        return '|g/' . http_build_query($gridParameters, 'flags_');
    }
}