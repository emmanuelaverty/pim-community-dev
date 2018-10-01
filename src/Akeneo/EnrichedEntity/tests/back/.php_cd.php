<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\ReferenceEntity',
        'Akeneo\Tool\Component',
        'Webmozart\Assert\Assert'
    ])->in('Akeneo\ReferenceEntity\Domain'),
    $builder->only([
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
    ])->in('Akeneo\ReferenceEntity\Application'),
    $builder->only([
        'Akeneo\ReferenceEntity\Application',
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Akeneo\Pim\ReferenceEntity\Component',
        'Doctrine\DBAL',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'PDO',
        'Akeneo\Platform\Bundle\InstallerBundle',
        'Ramsey\Uuid\Uuid',
        'Symfony',
    ])->in('Akeneo\ReferenceEntity\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
