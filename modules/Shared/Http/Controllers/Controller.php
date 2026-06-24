<?php

namespace Modules\Shared\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller for the application's module controllers.
 *
 * Extends Laravel's base Controller to provide authorization, validation,
 * and other framework features. This is the shared extension point for
 * cross-cutting controller behavior that every module's controllers should inherit.
 */
abstract class Controller extends BaseController
{
    /**
     * Clean and validate UTF-8 encoding in data structures
     * Recursively processes arrays and objects to ensure valid UTF-8
     */
    protected function cleanUtf8($data)
    {
        if (is_string($data)) {
            // Check if string is valid UTF-8, if not try to fix it
            if (! mb_check_encoding($data, 'UTF-8')) {
                // Try UTF-8 validation/cleaning
                return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            }

            return $data;
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$this->cleanUtf8($key)] = $this->cleanUtf8($value);
            }

            return $cleaned;
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                // For Eloquent models and collections
                return $this->cleanUtf8($data->toArray());
            }

            $cleaned = new \stdClass;
            foreach ($data as $key => $value) {
                $cleaned->$key = $this->cleanUtf8($value);
            }

            return $cleaned;
        }

        return $data;
    }
}
