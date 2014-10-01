<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Model;

/**
 * Runnable rule interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RunnableRuleInterface
{
    /**
     * @return RuleInterface
     */
    public function getRule();

    /**
     * @param RuleInterface $rule
     *
     * @return RunnableRuleInterface
     */
    public function setRule(RuleInterface $rule);
}
