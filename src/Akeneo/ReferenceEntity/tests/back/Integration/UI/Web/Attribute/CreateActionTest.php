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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Attribute;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ATTRIBUTE_ROUTE = 'akeneo_reference_entities_attribute_create_rest';
    private const RESPONSES_DIR = 'Attribute/Create/';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_creates_a_text_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'attribute_text_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_image_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'attribute_image_ok.json');
    }

    /**
     * TODO: This test should be an acceptance test once we'll move the logic from the controller
     *
     * @test
     */
    public function it_automatically_increment_the_attribute_order_on_creation(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'type'                       => 'text',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'reference_entity_identifier' => 'designer',
                'code'                       => 'description',
                'labels'                     => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'type'                       => 'text',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $descriptionAttribute = $attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString(
                sprintf('%s_%s_%s', 'description', 'designer', md5('designer_description'))
            )
        );

        $this->assertEquals(1, $descriptionAttribute->getOrder()->intValue());
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_attribute_type_is_not_provided($invalidAttributeType)
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'attribute_type_not_provided.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'referenceEntityIdentifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_create', true);
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_create', false);
    }
}
