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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Creates an enriched entity
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    /** @var CreateEnrichedEntityHandler */
    private $createEnrichedEntityHandler;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        CreateEnrichedEntityHandler $createEnrichedEntityHandler,
        ValidatorInterface $validator
    ) {
        $this->createEnrichedEntityHandler = $createEnrichedEntityHandler;
        $this->validator = $validator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $command = $this->getCreateCommand($request);
        $errors = $this->validateCommand($command);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => json_encode($errors)], Response::HTTP_BAD_REQUEST);
        }

        ($this->createEnrichedEntityHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getCreateCommand(Request $request): CreateEnrichedEntityCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new CreateEnrichedEntityCommand();
        $command->identifier = $normalizedCommand['identifier'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? [];

        return $command;
    }

    private function validateCommand(CreateEnrichedEntityCommand $command): array
    {
        $errors = [];
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        return $errors;
    }
}
