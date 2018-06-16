# 3.0

## Manage enriched entities

- PIM-7380: List enriched entities

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component
- PAV3-4: Regroup PAM Classes

## BC breaks
- Move namespace `PimEnterprise\Component\Catalog\Security` to `PimEnterprise\Component\Security`
- Change constructor of `PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedAssociationFieldSetter`. Add arguments `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface`, `PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Change constructor of `PimEnterprise\Component\Catalog\Security\Merger\NotGrantedAssociatedProductMerger`. Add arguments `PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Change constructor of `PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter`. Add arguments `PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery` two times and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`.
- Move namespace `PimEnterprise\Bundle\VersioningBundle` to `PimEnterprise\Bundle\RevertBundle`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\UpdateGuesser` to `PimEnterprise\Bundle\SecurityBundle\UpdateGuesser`
- Move `PimEnterprise\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\SkipVersionSubscriber`
- Move namespace `PimEnterprise\Bundle\VersioningBundle\Purger` to `PimEnterprise\Bundle\WorkflowBundle\Purger`
- Move all classes from `PimEnterprise\Bundle\ApiBundle\Normalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi`
- Move all classes from `PimEnterprise\Bundle\EnrichBundle\Controller\Rest` to `Akeneo\Asset\Bundle\Controller\Rest`
- Move all classes from `PimEnterprise\Bundle\FilterBundle\Filter\Tag` to `Akeneo\Asset\Bundle\Datagrid\Filter`
- Rename `PimEnterprise\Bundle\EnrichBundle\Controller\Rest\ChannelController` to `Akeneo\Asset\Bundle\Controller\Rest\AssetTransformationController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\InternalApi\AssetNormalizer`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber` to `Akeneo\Asset\Bundle\Security\AssetCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat\AssetCategoryNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Flat\AssetCategoryNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository\AssetRepository` to `Akeneo\Asset\Bundle\Doctrine\ORM\Repository\ExternalApi\AssetRepository`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetCategoryController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetCategoryController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetReferenceController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetTagController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetTagController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController` to `Akeneo\Asset\Bundle\Controller\ExternalApi\AssetVariationController`
- Move `PimEnterprise\Bundle\ApiBundle\Normalizer\AssetReferenceNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi\AssetReferenceNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Normalizer\AssetVariationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi\AssetVariationNormalizer`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductDraft\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\ProductDraft\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\Proposal\ContextConfigurator`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\Proposal\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ProductProposalDatasource` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ProductProposalDatasource`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductDraftHydrator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ResultRecord\ProductDraftHydrator`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProposalGridListener` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener\ConfigureProposalGridListener`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\AttributeChoiceFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\AttributeChoiceFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\AuthorFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\AuthorFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft\ChoiceFilter` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\ChoiceFilter`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\ProductDraftFilterUtility` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\ProductDraftFilterUtility`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler\MassApproveActionHandler`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler\MassRefuseActionHandler`
- Move `PimEnterprise\Bundle\SecurityBundle\Voter\ProductDraftVoter` to `PimEnterprise\Bundle\WorkflowBundle\Security\ProductDraftVoter`
- Move `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget` to `PimEnterprise\Bundle\WorkflowBundle\Widget\ProposalWidget`
- Move `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget` to `PimEnterprise\Bundle\WorkflowBundle\Widget\ProposalWidget`
- Move `PimEnterprise\Component\Api\Updater\AssetUpdater` to `PimEnterprise\Component\ProductAsset\Updater\ExternalApi\AssetUpdater`
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\ProductDraftController`
- Move `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\PublishedProductController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\ExternalApi\PublishedProductController`
- Move `PimEnterprise\Component\Api\Normalizer\PublishedProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\PublishedProductNormalizer`
- Remove `PimEnterprise\Component\Api\Repository\PublishedProductRepositoryInterface`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\PublishedProduct\GridHelper` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Configuration\PublishedProduct\GridHelper`
- Move `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductHistoryHydrator` to `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Datasource\ResultRecord\PublishedProductHistoryHydrator`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\MassPublish` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\JobParameters\ConstraintCollection`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider\MassPublish` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\JobParameters\DefaultValues`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\PublishProductTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\PublishProductTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\UnpublishProductTasklet` to `PimEnterprise\Bundle\WorkflowBundle\MassEditAction\Tasklet\UnpublishProductTasklet`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to `PimEnterprise\Bundle\WorkflowBundle\Normalizer\ProductNormalizer`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\VersionNormalizer` to `PimEnterprise\Bundle\WorkflowBundle\Versioning\VersionNormalizer`
- Remove `PimEnterprise\Bundle\ApiBundle\DependencyInjection\Configuration`
- Remove `PimEnterprise\Bundle\ApiBundle\DependencyInjection\PimEnterpriseApiExtension`
- Change the constructor of `PimEnterprise\Bundle\SecurityBundle\User\UserContext` to remove `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Move namespace `Akeneo\Bundle\FileMetadataBundle` to `Akeneo\Tool\Bundle\FileMetadataBundle`
- Move namespace `Akeneo\Bundle\FileTransformerBundle` to `Akeneo\Tool\Bundle\FileTransformerBundle`
- Move namespace `Akeneo\Bundle\RuleEngineBundle` to `Akeneo\Bundle\Tool\RuleEngineBundle`
- Move namespace `Akeneo\Component\FileMetadata` to `Akeneo\Tool\Component\FileMetadata`
- Move namespace `Akeneo\Component\FileTransformer` to `Akeneo\Tool\Component\FileTransformer`
- Move namespace `PimEnterprise\Bundle\ProductAssetBundle` to `Akeneo\Asset\Bundle`
- Move namespace `PimEnterprise\Component\ProductAsset` to `Akeneo\Asset\Component`
- Move class `PimEnterprise\Component\ProductAsset\Remover\CategoryAssetRemover` to `Akeneo\Asset\Bundle\Doctrine\ORM\Remover\CategoryAssetRemover`
- Move class `PimEnterprise\Component\ProductAsset\Factory\NotificationFactory` to `Akeneo\Asset\Bundle\Notification\NotificationFactory`
- Move class `PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM\AssetEventSubscriber` to `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Asset\AssetEventSubscriber`
- Move class `PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter\AssetsCollectionPresenter` to `PimEnterprise\Bundle\WorkflowBundle\Presenter\AssetsCollectionPresenter`
- Remove class `Akeneo\Asset\Bundle\TwigExtension\ImageExtension`

## Security

- Move `PimEnterprise\Bundle\ApiBundle\Security\AccessDeniedHandler` to `PimEnterprise\Bundle\SecurityBundle\Api\AccessDeniedHandler`
- Move `PimEnterprise\Bundle\ApiBundle\Checker\QueryParametersChecker` to `PimEnterprise\Bundle\SecurityBundle\Api\QueryParametersChecker`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter\DatagridViewFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\AbstractAuthorizationFilter\DatagridViewFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager\ProductController` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Category\CategoryManager\ProductController`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager\ProductModelController` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Category\CategoryManager\ProductModelController`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeViewRightFilter\AttributeRepository` to `PimEnterprise\Bundle\SecurityBundle\Filter\AttributeViewRightFilter\AttributeRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\JobInstanceEditRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\JobInstanceEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\AbstractAuthorizationFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeEditRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\AttributeEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeGroupViewRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\AttributeGroupViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\AttributeViewRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\AttributeViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\LocaleEditRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\LocaleEditRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\LocaleViewRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\LocaleViewRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductAndProductModelDeleteRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\ProductAndProductModelDeleteRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductRightEditFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\ProductRightEditFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductRightViewFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\ProductRightViewFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductValueAttributeGroupRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\ProductValueAttributeGroupRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Filter\ProductValueLocaleRightFilter` to `PimEnterprise\Bundle\SecurityBundle\Filter\ProductValueLocaleRightFilter`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Attribute\AttributeRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Category\CategoryManager`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductMassActionRepository` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\EntityWithValue\ProductMassActionRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelRepository` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\EntityWithValue\ProductModelRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Security\Elasticsearch\ProductQueryBuilderFactory` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory`
- Move `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\EntityWithValue\ProductRepository`
- Move `PimEnterprise\Bundle\CatalogBundle\Security\Doctrine\Common\Saver\FilteredEntitySaver` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\FilteredEntitySaver`
- Move `PimEnterprise\Bundle\DataGridBundle\Filter\DatagridViewFilter` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\DatagridViewFilter`
- Move `PimEnterprise\Bundle\DataGridBundle\Manager\DatagridViewManager` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\DatagridViewManager`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\AddPermissionsToGridListener` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\EventListener\AddPermissionsToGridListener`
- Move `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\EventListener\ConfigureProductGridListener`
- Move `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\MassAction\ProductFieldsBuilder`
- Move `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\Product\RowActionsConfigurator`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter\GrantedCategoryItemsCounter` to `Akeneo\Asset\Bundle\Doctrine\ORM\Query\GrantedCategoryItemsCounter`
- Move `PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter\GrantedCategoryProductsCounter` to `PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Category\Query\GrantedCategoryProductsCounter`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\ProductCategoryAccessSubscriber` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\EventListener\ProductCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\EnrichBundle\EventSubscriber\SavePermissionsSubscriber` to `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\SavePermissionsSubscriber`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Filter` to `PimEnterprise\Bundle\SecurityBundle\Filter`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Form\Subscriber` to `PimEnterprise\Bundle\SecurityBundle\Form\EventListener`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Provider\Form` to `PimEnterprise\Bundle\SecurityBundle\Form\Provider`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Form\Type` to `PimEnterprise\Bundle\SecurityBundle\Form\Type`
- Move namespace `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product` to `PimEnterprise\Bundle\SecurityBundle\MassEdit\Processor`
- Move `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter` to `PimEnterprise\Bundle\SecurityBundle\MassEdit\Writer\ProductAndProductModelWriter`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer` to `PimEnterprise\Bundle\SecurityBundle\Normalizer\InternalApi\IncompleteValuesNormalizer`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\Product\PermissionFilter` to `PimEnterprise\Bundle\SecurityBundle\Datagrid\Filter\PermissionFilter`
- Move `PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber\JobProfilePermissionsSubscriber` to `PimEnterprise\Bundle\SecurityBundle\Form\EventListener\JobProfilePermissionsSubscriber`
- Move `PimEnterprise\Bundle\ImportExportBundle\Form\Type\JobProfilePermissionsType` to `PimEnterprise\Bundle\SecurityBundle\Form\Type\JobProfilePermissionsType`
- Move `PimEnterprise\Bundle\ImportExportBundle\Manager\JobExecutionManager` to `PimEnterprise\Bundle\SecurityBundle\Manager\JobExecutionManager`
- Move `PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter` to `PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\Filter\ProjectCompletenessFilter`
- Move `PimEnterprise\Bundle\UIBundle\Controller\AjaxOptionController` to `Akeneo\Asset\Bundle\Controller\Rest\AjaxOptionController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Twig\ImageExtension` to `Akeneo\Asset\Bundle\TwigExtension\ImageExtension`
- Move `PimEnterprise\Bundle\SecurityBundle\Controller\PermissionRestController` to `PimEnterprise\Bundle\SecurityBundle\Controller\InternalApi\PermissionRestController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Controller\ProductController` to `PimEnterprise\Bundle\SecurityBundle\Controller\ProductController`
- Move `PimEnterprise\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer` to `PimEnterprise\Bundle\SecurityBundle\Pdf\ProductPdfRenderer`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\PublishedProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\InternalApi\PublishedProductNormalizer`

## Removed classes

- Remove `PimEnterprise\Bundle\FilterBundle\PimEnterpriseFilterBundle`
- Remove `PimEnterprise\Bundle\ImportExportBundle\PimEnterpriseImportExportBundle`
- Remove `PimEnterprise\Bundle\FilterBundle\DependencyInjection\PimEnterpriseFilterExtension`
- Remove `PimEnterprise\Bundle\ImportExportBundle\DependencyInjection\PimEnterpriseImportExportExtension`
- Remove `PimEnterprise\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductAndProductModelReader`
- Remove `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductWriter`
- Remove `PimEnterprise\Bundle\EnrichBundle\Form\Type\AvailableAttributesType`
- Remove `PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle`
- Remove `PimEnterprise\Bundle\PdfGeneratorBundle\DependencyInjection\PimEnterprisePdfGeneratorExtension`
- Remove `PimEnterprise\Bundle\UIBundle\DependencyInjection\PimEnterpriseUIExtension`
- Remove `PimEnterprise\Component\Catalog\Updater\AttributeUpdater`
- Remove `PimEnterprise\Bundle\EnrichBundle\Normalizer\AttributeNormalizer`
- Remove `PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer`