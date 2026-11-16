<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\Application;
use App\Models\InternshipGroup;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

$u = User::where('email', 'calon.magang@unesa.ac.id')->first();
if ($u) {
    $u->password = Hash::make('password');
    $u->save();
    Application::where('user_id', $u->id)->delete();
    InternshipGroup::where('leader_user_id', $u->id)->delete();
    echo 'RESET SUCCESS: '.$u->email."\n";
} else {
    echo "USER NOT FOUND\n";
}
