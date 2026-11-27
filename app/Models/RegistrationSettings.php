<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RegistrationSettings extends Model
{
    public $timestamps = false;
    protected $attributes = ['group_limit' => Unit::MAX_GROUP_MEMBERS, 'letter_contact' => 'Pak Nanang'];
    public static function current(): self
    {
        return self::find(1) ?? new self;
    }
}
