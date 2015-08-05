<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Context\WebUser as BaseWebUser;

/**
 * Overrided context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseWebUser extends BaseWebUser
{
    /**
     * Override parent
     *
     * {@inheritdoc}
     */
    public function iChooseTheOperation($operation)
    {
        $this->getNavigationContext()->currentPage = $this
            ->getPage('Batch Operation')
            ->addStep('Publish products', 'Batch Publish')
            ->addStep('Unpublish products', 'Batch Unpublish')
            ->chooseOperation($operation)
            ->next();

        $this->wait();
    }

    /**
     * @Given /^I should not see a single form input$/
     */
    public function iShouldNotSeeASingleFormInput()
    {
        new Step\Given('I should not see an "input" element');
    }

    /**
     * @param string $fieldName
     * @param string $expected
     *
     * @Then /^the view mode field (.*) should contain "([^"]*)"$/
     */
    public function theProductViewModeFieldValueShouldBe($fieldName, $expected = '')
    {
        $field = $this->getCurrentPage()->findField($fieldName);
        $actual = trim($field->getHtml());

        if ($expected != $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product view mode field "%s" to contain "%s", but got "%s".',
                    $fieldName,
                    $expected,
                    $actual
                )
            );
        }
    }

    /**
     * @Then /^I should see the smart attribute tooltip$/
     */
    public function iShouldSeeTheTooltip()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = 'return $(\'.icon-code-fork[data-async-content]\').length > 0';
            $found = $this->getSession()->evaluateScript($script);
            if ($found) {
                return;
            }
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see smart attribute tooltip'
                )
            );
        }
    }

    /**
     * @param string $date
     *
     * @When /^I change the end of use at to "([^"]+)"$/
     */
    public function iChangeTheEndOfUseAtTo($date)
    {
        $this->getCurrentPage()->changeTheEndOfUseAtTo($date);
    }

    /**
     * @params string       $field
     * $params string|array $tags
     *
     * @Given /^I add the following tags? in the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iAddTheFollowingTagsInTheSelect2($field, $tags)
    {
        if (is_string($tags)) {
            $tags = $this->convertCommaSeparatedToArray($tags);
        }

        $select2 = $this->getCurrentPage()->findField($field);
        $search  = $this->getCurrentPage()->find('css', '.select2-results');
        foreach ($tags as $tag) {
            $select2->click();
            // Impossible to use NodeElement::setValue() since the Selenium2 implementation emulates the change event
            // by hitting the TAB key, which results in closing select2 choices
            $this->getSession()->executeScript(
                sprintf('$(\'.select2-search-field .select2-input\').val(\'%s\').trigger(\'paste\');', $tag)
            );

            $item = $this->getMainContext()->spin(function () use ($search, $tag) {
                return $search->find(
                    'css',
                    sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', $tag)
                );
            }, 5);
            $item->click();
        }
    }

    /**
     * @params string       $field
     * $params string|array $tags
     *
     * @Given /^I set the following tags? in the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iSetTheFollowingTagsInTheSelect2($field, $tags)
    {
        if (is_string($tags)) {
            $tags = $this->convertCommaSeparatedToArray($tags);
        }

        $choices = $this->getSelect2Choices($field);
        $this->removeTags($choices);

        $this->iAddTheFollowingTagsInTheSelect2($field, $tags);
    }

    /**
     * @params string       $field
     * @params string|array $tags
     *
     * @Given /^I remove the following tags? from the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iRemoveTheFollowingTagsFromTheSelect2($field, $tags)
    {
        $tags = $this->convertCommaSeparatedToArray($tags);
        $choices = $this->getSelect2Choices($field, $tags);
        $this->removeTags($choices);
    }

    /**
     * @param TableNode $table
     *
     * @Then /^the grid locale switcher should contain the following items:$/
     */
    public function theGridLocaleSwitcherShouldContainTheFollowingItems(TableNode $table, $page = 'index')
    {
        return parent::theLocaleSwitcherShouldContainTheFollowingItems($table, $page);
    }

    /**
     * @params string        $field
     * @params string[]|null $tags
     *
     * @return NodeElement[]|array NodeElement[] if something is found or empty array if nothing is found
     */
    protected function getSelect2Choices($field, $tags = null)
    {
        $select2Label   = $this->getCurrentPage()->find('css', sprintf('label:contains("%s")', $field));
        $currentChoices = $select2Label->getParent()->findAll('css', '.select2-search-choice');

        if (null !== $tags) {
            $choices = [];
            foreach ($tags as $tag) {
                foreach ($currentChoices as $choice) {
                    if ($choice->getText() === $tag) {
                        $choices[] = $choice;
                        break;
                    }
                }
            }

            return $choices;
        }

        return $currentChoices;
    }

    /**
     * Remove tags from their NodeElement. You can use getSelect2Choices() to found their NodeElement
     *
     * @param NodeElement[] $tagElements
     */
    protected function removeTags(array $tagElements)
    {
        foreach ($tagElements as $tag) {
            $removeLink = $tag->find('css', '.select2-search-choice-close');
            $removeLink->click();

            $this->getMainContext()->spin(function () use ($removeLink) {
                try {
                    $removeLink->getText();
                } catch (\Exception $e) {
                    return true;
                }

                return false;
            });
        }
    }

    /**
     * @param string $vars Vars separated by ',' or ', '
     *
     * @return string[]
     */
    protected function convertCommaSeparatedToArray($vars)
    {
        $exploded = explode(',', $vars);

        return array_map(function($var) {
            return trim($var);
        }, $exploded);
    }
}
