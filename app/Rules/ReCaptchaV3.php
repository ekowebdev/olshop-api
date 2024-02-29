<?php

namespace App\Rules;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\Rule;

class ReCaptchaV3 implements Rule
{
    public $action, $minScore;

    public function __construct(string $action, float $minScore)
    {
        $this->action = $action;
        $this->minScore = $minScore;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Send a POST request to the google siteverify service to validate the reCAPTCHA token
        $siteVerify = Http::asForm()
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
            ]);

        // If the request to Google fails, return false
        if ($siteVerify->failed()) {
            return false;
        }

        // If Google successfully processes the request
        if ($siteVerify->successful()) {
            $body = $siteVerify->json();

            // If Google verifies the reCAPTCHA token successfully
            if ($body['success'] === true) {
                // If a specific action is provided and it doesn't match the one from Google's response
                if (!is_null($this->action) && $this->action != $body['action']) {
                    return false;
                }

                // If a minimum score is provided and the score from Google's response is lower
                if (!is_null($this->minScore) && $this->minScore > $body['score']) {
                    return false;
                }

                // If everything checks out, return true
                return true;
            }
        }

        // Default to false if verification fails
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.g_recaptcha_failed');
    }
}