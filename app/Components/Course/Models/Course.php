<?php

namespace App\Components\Course\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Course
 * @package App\Components\Course\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property array $permissions
 * @property string|null $active
 */
class Course extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * the validation rules
     *
     * @var array
     */
    public static $rules = [];
}
