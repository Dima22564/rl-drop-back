<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;

class isAdmin
{
  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    $roles = User::getAuthUserRoles();
    if (!in_array(Role::ADMIN_ROLE, $roles)) {
      return response()->json([
        'error' => new \Exception('Forbidden'),
        'success' => false], 403);
    }
    return $next($request);
  }
}
