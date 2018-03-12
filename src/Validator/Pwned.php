<?php

// @TODO:
// * Readme
// * Auto-registration

namespace Square1\Laravel\PwnedCheck\Validator;

use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Validation\Rule;
use Square1\Laravel\PwnedCheck\PwnedCheck;
use Square1\Pwned\Exception\GeneralException;
use Square1\Pwned\Exception\ConnectionFailedException;

class Pwned implements Rule
{
    /**
     * Minimum occurrences of password in HIBP data for it to be considered compromised
     *
     * @var integer
     */
    private $minimum = 1;


    /**
     * Number of chars used for password range search
     *
     * @var integer
     */
    private $range_size = 5;


    /**
     * Validate that a given value passes tests for the associated attribute
     *
     * @param string $attribute  Type of value being validated
     * @param string $value      Value to validate
     * @param array  $parameters Optional additional parameters (minimum frequency)
     *
     * @return boolean
     */
    public function validate($attribute, $value, $parameters)
    {
        // User may have passed minimum occurrence setting as param to rule
        $this->minimum = array_get($parameters, 0, 1);

        // Call api
        return $this->passes($attribute, $value);
    }


    /**
     * Does the given value pass the validation test required by the given attribute type?
     *
     * @param string $attribute Type of value being validated
     * @param string $value     Value to validate
     *
     * @return boolean
     */
    public function passes($attribute, $value)
    {
        try {
            $pwned = new PwnedCheck();
            return !$pwned->hasBeenPwned($value, $this->minimum);
        } catch (ConnectionFailedException $e) {
            return $this->handleRemoteServiceFailure($e);
        } catch (GeneralException $e) {
            return $this->handleRemoteServiceFailure($e);
        } catch (Exception $e) {
            return $this->handleRemoteServiceFailure($e);
        }

        return false;
    }


    /**
     * Default message
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('validation.pwned');
    }


    /**
     * Handle response when remote service fails
     * User can choose to run this as advisory, or hard block on exceptional failure
     *
     * @param Exception $exception
     *
     * @return bool
     */
    public function handleRemoteServiceFailure($exception)
    {
        $this->log($e->getMessage());

        // Has the user chosen to accept this failure as a blocking failure, or ignore it?
        if (config('pwned-check.fail_on_timeout')) {
            return false;
        }

        return true;
    }


    /**
     * Log messages to standard laravel log
     * User may have disabled this, so consult config first
     *
     * @param string $message Message to log
     *
     * @return void
     */
    public function log($message)
    {
        if (!config('pwned-check.log_failures')) {
            return;
        }

        logger()->debug($message);
    }
}
