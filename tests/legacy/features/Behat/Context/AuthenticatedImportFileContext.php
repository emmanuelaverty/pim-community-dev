<?php

namespace PimEnterprise\Behat\Context;

use Akeneo\Tool\Bundle\BatchQueueBundle\Command\JobQueueConsumerCommand;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * This context aims to contain all methods to the launch of a job via the queue and be authentified.
 *
 * When we launch a job, we keep in this context the job instance and execution so that we can
 * check what happens in a next step (and yes, that means this class is completely stateful).
 * For instance, we want to check that 1 item has been skipped.
 */
final class AuthenticatedImportFileContext extends PimContext implements SnippetAcceptingContext
{
    use SpinCapableTrait;

    /** @var JobInstance */
    private $jobInstance;

    /** @var JobExecution */
    private $jobExecution;

    /**
     * @When :author imports :entities via the job :jobName
     */
    public function authorImportsResourceViaTheJobName(string $author, string $entities, string $jobName)
    {
        $jobOptions = new TableNode([['filePath', self::$placeholderValues['%file to import%']]]);

        $this->import($jobName, $jobOptions, $author);
    }

    /**
     * @When :author imports :filePath file for :entities via the job :jobName
     */
    public function authorImportsFileForResourceViaTheJobName(string $author, string $filePath, string $entities, string $jobName)
    {
        $jobOptions = new TableNode([['filePath', self::$placeholderValues['%fixtures%'] . $filePath]]);

        $this->import($jobName, $jobOptions, $author);
    }

    private function import(string $jobName, TableNode $jobOptions, string $author): void
    {
        $this->jobInstance = $this->mainContext->getSubcontext('job')->theFollowingJobConfiguration($jobName, $jobOptions);

        $user = $this->getFixturesContext()->getUser($author);

        $launcher = $this->mainContext->getContainer()->get('pim_connector.launcher.authenticated_job_launcher');
        $launcher->launch($this->jobInstance, $user);

        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $arrayInput = ['command' => JobQueueConsumerCommand::COMMAND_NAME, '--run-once' => true];

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $this->jobExecution = $this->waitForJobToFinish($this->jobInstance);
    }

    private function waitForJobToFinish(JobInstance $jobInstance): JobExecution
    {
        $jobInstance->getJobExecutions()->setInitialized(false);
        $this->getFixturesContext()->refresh($jobInstance);
        $jobExecution = $jobInstance->getJobExecutions()->last();

        $this->spin(function () use ($jobExecution) {
            $this->getFixturesContext()->refresh($jobExecution);

            return $jobExecution && !$jobExecution->isRunning();
        }, sprintf('The job execution of "%s" was too long', $jobInstance->getJobName()));

        return $jobExecution;
    }
}