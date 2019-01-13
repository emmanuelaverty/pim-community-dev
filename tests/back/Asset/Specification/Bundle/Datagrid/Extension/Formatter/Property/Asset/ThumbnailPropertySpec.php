<?php

namespace Specification\Akeneo\Asset\Bundle\Datagrid\Extension\Formatter\Property\Asset;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Asset\Component\Model\AssetInterface;

class ThumbnailPropertySpec extends ObjectBehavior
{
    function let(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith($environment, $paramsExtractor, $userContext, $localeRepository, $channelRepository);

        $params = new PropertyConfigurationFake(['template' => 'my-template']);
        $this->init($params);
    }

    function it_is_a_property()
    {
        $this->shouldImplement(PropertyInterface::class);
    }

    function it_returns_the_template_with(
        $localeRepository,
        $paramsExtractor,
        $channelRepository,
        $environment,
        ResultRecordInterface $record,
        LocaleInterface $localeEN,
        ChannelInterface $channelMobile,
        AssetInterface $asset,
        FileInfoInterface $fileInfo,
        \Twig_TemplateInterface $template
    ) {
        $paramsExtractor->getParameter('dataLocale')->willReturn('en_US');
        $paramsExtractor->getDatagridParameter('_filter')->willReturn(['scope' => ['value' => 'mobile']]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEN);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);

        $record->getRootEntity()->willReturn($asset);

        $asset->getFileForContext($channelMobile, $localeEN)->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('a/b/c/d/abcdmyimage.jpg');

        $environment->loadTemplate('my-template')->willReturn($template);
        $template->render(['path' => 'a/b/c/d/abcdmyimage.jpg'])->willReturn('<div>My Template !</div>');

        $this->getValue($record)->shouldReturn('<div>My Template !</div>');
    }
}

class PropertyConfigurationFake extends PropertyConfiguration
{
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}