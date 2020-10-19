<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */

  public function run()
  {
    $roles = [
      'user',
      'admin',
      'messenger'
    ];
    foreach ($roles as $role) {
      \Illuminate\Support\Facades\DB::table('roles')
        ->insert(['role' => $role]);
    }
  }
}
