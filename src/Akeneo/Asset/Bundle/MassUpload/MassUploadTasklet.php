<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\MassUpload;

use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\Upload\MassUploadProcessor;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * Launch the asset upload processor to create/update assets from uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadTasklet implements TaskletInterface
{
    const TASKLET_NAME = 'asset_mass_upload';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadProcessor */
    protected $massUploadProcessor;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param MassUploadProcessor $massUploadProcessor
     * @param string              $tmpStorageDir
     */
    public function __construct(
        MassUploadProcessor $massUploadProcessor,
        $tmpStorageDir
    ) {
        $this->massUploadProcessor = $massUploadProcessor;
        $this->tmpStorageDir = $tmpStorageDir;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobExecution = $this->stepExecution->getJobExecution();

        $username = $jobExecution->getUser();
        $uploadContext = new UploadContext($this->tmpStorageDir, $username);

        $processedList = $this->massUploadProcessor->applyMassUpload($uploadContext);

        foreach ($processedList as $item) {
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
                    $this->stepExecution->incrementSummaryInfo('error');
                    $this->stepExecution->addError($item->getException()->getMessage());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $this->stepExecution->incrementSummaryInfo('variations_not_generated');
                    $this->stepExecution->addWarning(
                        $item->getReason(),
                        [],
                        new DataInvalidItem(['filename' => $file->getFilename()])
                    );
                    break;
                default:
                    $this->stepExecution->incrementSummaryInfo($item->getReason());
                    break;
            }
        }
    }
}