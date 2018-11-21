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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command\InitFranklinUserCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InitFranklinUserSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => 'initUser',
        ];
    }

    /**
     * @param InstallerEvent $event
     *
     * @throws \Exception
     */
    public function initUser(InstallerEvent $event): void
    {
        $commandExecutor = $event->getCommandExecutor();
        $commandExecutor->runCommand(
            InitFranklinUserCommand::getDefaultName(),
            [
                '--quiet' => true,
            ]
        );
    }
}