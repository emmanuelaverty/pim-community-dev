<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Context\ActivityManager;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\DatagridViewTypes;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectContext extends PimContext
{
    /**
     * @Then /^the project "([^"]*)" for channel "([^"]*)" and locale "([^"]*)" has the following properties:$/
     */
    public function projectHasProperties($label, $channelCode, $localeCode, TableNode $properties)
    {
        $project = $this->findProjectByLabelChannelLocale($label, $channelCode, $localeCode);

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties->getRows() as $property) {
            list($propertyName, $expectedValue) = $property;
            switch ($propertyName) {
                case 'Due date':
                    $actualValue = $accessor->getValue($project, strtolower(str_replace(' ', '_', $propertyName)));
                    $actualValue = $actualValue->format('Y-m-d');
                    break;
                case 'Channel':
                    $actualValue = $project->getChannel()->getCode();
                    break;
                case 'Locale':
                    $actualValue = $project->getLocale()->getCode();
                    break;
                case 'Owner':
                    $actualValue = $project->getOwner()->getUsername();
                    break;
                default:
                    $actualValue = $accessor->getValue($project, $propertyName);
                    break;
            }

            if ($expectedValue !== $actualValue) {
                throw new \DomainException(
                    sprintf(
                        'Given value does not match the expected value, "%s" expected, "%s" given, property: "%s"',
                        $expectedValue,
                        $actualValue,
                        $propertyName
                    )
                );
            }
        }
    }

    /**
     * @Given /^the project "([^"]*)" for channel "([^"]*)" and locale "([^"]*)" has a project datagrid view$/
     */
    public function projectHasDatagridView($label, $channelCode, $localeCode)
    {
        $project = $this->findProjectByLabelChannelLocale($label, $channelCode, $localeCode);
        $datagridView = $project->getDatagridView();
        $type = $datagridView->getType();

        assertNotNull($datagridView, 'The project %s does not have a datagrid view');
        assertEquals(
            DatagridViewTypes::PROJECT_VIEW,
            $type,
            sprintf(
                'the project datagrid view has the right type, %s given, %s expected %s',
                $label,
                $type,
                DatagridViewTypes::PROJECT_VIEW
            )
        );
    }

    /**
     * @Given /^the following projects:$/
     */
    public function theFollowingProjects(TableNode $table)
    {
        $factory = $this->getService('pimee_activity_manager.factory.project');

        $projects = [];
        foreach ($table->getHash() as $field => $data) {
            if (!isset($data['datagrid_view'])) {
                $data['datagrid_view']['columns'] = 'sku,enable';
                $data['datagrid_view']['filters'] = '/filters';
            }

            $data['product_filters'] = json_decode($data['product_filters'], true);

            $projects[] = $factory->create($data);
        }
        $this->getService('pimee_activity_manager.saver.project')->saveAll($projects);

        foreach ($projects as $project) {
            $this->generateProject($project->getCode());
        }
    }

    /**
     * Launch the project calculation job for the given project
     *
     * @param string $projectCode
     *
     * @throws \Exception
     */
    private function generateProject($projectCode)
    {
        $application = new Application();
        $application->add(new BatchCommand());
        $batchJobCommand = $application->find('akeneo:batch:job');
        $batchJobCommand->setContainer($this->getMainContext()->getContainer());
        $command = new CommandTester($batchJobCommand);

        $jobInstance = $this->getService('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier('project_calculation');
        $exitCode = $command->execute(
            [
                'command'    => $batchJobCommand->getName(),
                'code'       => $jobInstance->getCode(),
                '--config'   => json_encode(['project_code' => $projectCode]),
                '--no-log'   => true,
                '-v'         => true
            ]
        );

        if (0 !== $exitCode) {
            throw new \Exception(
                sprintf(
                    'An error happened during the "project_calculation" job of "%s" project: "%s"',
                    $projectCode,
                    $command->getDisplay()
                )
            );
        }
    }

    /**
     * @param string $label
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws \UnexpectedValueException
     * @return ProjectInterface
     *
     */
    private function findProjectByLabelChannelLocale($label, $channelCode, $localeCode)
    {
        $channel = $this->getService('pim_catalog.repository.channel')
            ->findOneByIdentifier($channelCode);

        $locale = $this->getService('pim_catalog.repository.locale')
            ->findOneByIdentifier($localeCode);

        $project = $this->getService('pimee_activity_manager.repository.project')
            ->findOneBy([
                'label'   => $label,
                'channel' => $channel,
                'locale'  => $locale,
            ]);

        if (null === $project) {
            throw new \UnexpectedValueException(
                sprintf(
                    'The project "%s" does not exist for channel "%s" and locale "%s"',
                    $label,
                    $channelCode,
                    $localeCode
                )
            );
        }

        return $project;
    }
}
