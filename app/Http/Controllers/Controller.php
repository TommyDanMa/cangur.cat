<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller that all application controllers extend.
 *
 * Traits used:
 * - AuthorizesRequests: provides $this->authorize() and related helpers for Policies (authorization).
 * - ValidatesRequests: provides $this->validate() and Request validation shortcuts.
 *
 * In Laravel, controllers are typically thin and leverage middleware, policies, and validation.
 * In Flask, you might rely more on decorators and manual validation/permission checks in each view function.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
