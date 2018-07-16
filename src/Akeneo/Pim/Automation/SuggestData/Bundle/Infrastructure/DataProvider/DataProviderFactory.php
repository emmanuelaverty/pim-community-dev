<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\Memory\InMemoryAdapter;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Subscription\SubscriptionApiInterface;

/**
 * Data provider factory
 * Creates the right adapter depending of the data provider used
 * and configures it
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class DataProviderFactory
{
    /** @var DeserializeSuggestedDataCollection */
    protected $deserializer;
    
    private $subcriptionApi;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     * @param SubscriptionApiInterface $subcriptionApi
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, SubscriptionApiInterface $subcriptionApi)
    {
        $this->deserializer = $deserializer;
        $this->subcriptionApi = $subcriptionApi;
    }

    /**
     * @return DataProviderAdapterInterface
     */
    public function create()
    {
        $adapter = $this->initialize();

        return $adapter;
    }

    /**
     * Create and configure the data provider
     */
    private function initialize()
    {
        // TODO: Remove hardcoded configuration
        $config = ['url' => 'pim.ai.host', 'token' => 'my_personal_token'];
        
        return new InMemoryAdapter($this->deserializer, $this->subcriptionApi, $config);
    }
}
