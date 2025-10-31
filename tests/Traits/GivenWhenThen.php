<?php

namespace Tests\Traits;

/**
 * Trait for implementing Given/When/Then BDD-style test scenarios
 */
trait GivenWhenThen
{
    /**
     * Define the "Given" context for the test scenario
     * 
     * @param string $context Description of the initial state
     * @param callable $callback Setup actions for the given context
     * @return mixed
     */
    protected function given(string $context, callable $callback = null): mixed
    {
        $this->addToAssertionCount(1);
        
        if ($callback) {
            return $callback();
        }
        
        return null;
    }

    /**
     * Define the "When" action for the test scenario
     * 
     * @param string $action Description of the action being performed
     * @param callable $callback The action to perform
     * @return mixed
     */
    protected function when(string $action, callable $callback): mixed
    {
        $this->addToAssertionCount(1);
        
        return $callback();
    }

    /**
     * Define the "Then" assertion for the test scenario
     * 
     * @param string $expectation Description of the expected outcome
     * @param callable $callback Assertions to verify the outcome
     * @return void
     */
    protected function then(string $expectation, callable $callback): void
    {
        $callback();
    }

    /**
     * Define an "And" clause to chain additional context, actions, or assertions
     * 
     * @param string $description Description of the additional clause
     * @param callable $callback The callback to execute
     * @return mixed
     */
    protected function and(string $description, callable $callback): mixed
    {
        $this->addToAssertionCount(1);
        
        return $callback();
    }
}

