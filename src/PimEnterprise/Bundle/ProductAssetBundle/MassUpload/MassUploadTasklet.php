<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

/**
 * Launch the asset upload processor to create/update assets from uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadTasklet implements TaskletInterface
{
    public const TASKLET_NAME = 'asset_mass_upload';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadProcessor */
    protected $processor;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param MassUploadProcessor $processor
     * @param string              $tmpStorageDir
     */
    public function __construct(
        MassUploadProcessor $processor,
        string $tmpStorageDir
    ) {
        $this->processor = $processor;
        $this->tmpStorageDir = $tmpStorageDir;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $jobExecution = $this->stepExecution->getJobExecution();

        $username = $jobExecution->getUser();
        $uploadContext = new UploadContext($this->tmpStorageDir, $username);

        $processedItems = $this->processor->process($uploadContext);

        $this->incrementSummaryInfo($processedItems, $this->stepExecution);
    }

    /**
     * @param ProcessedItemList $processedItems
     * @param StepExecution     $stepExecution
     */
    protected function incrementSummaryInfo(ProcessedItemList $processedItems, StepExecution $stepExecution): void
    {
        foreach ($processedItems as $item) {
            $file = $item->getItem();

            if (!$file instanceof \SplFileInfo) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "\SplFileInfo", "%s" provided.',
                        ClassUtils::getClass($file)
                    )
                );
            }

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $stepExecution->incrementSummaryInfo('error');
                    $stepExecution->addError($item->getException()->getMessage());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $stepExecution->incrementSummaryInfo('variations_not_generated');
                    $stepExecution->addWarning(
                        $item->getReason(),
                        [],
                        new DataInvalidItem(['filename' => $file->getFilename()])
                    );
                    break;
                default:
                    $stepExecution->incrementSummaryInfo($item->getReason());
                    break;
            }
        }
    }
}
