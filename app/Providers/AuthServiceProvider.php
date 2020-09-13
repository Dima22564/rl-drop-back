<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array
   */
  protected $policies = [
    // 'App\Model' => 'App\Policies\ModelPolicy',
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();

    Gate::define('user-update', function ($user, $payload) {
      return $user->id == $payload;
    });

    Gate::define('check-balance', function ($user, $payload) {
      return $user->balance >= $payload;
    });

    Gate::define('open-chest-opened', function ($user, $payload) {
      return $payload === 1;
    });
  }
}
