<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class View extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pk', 'username', 'date',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function boot()
    {
        View::creating(function ($model) {
            $model->date = Carbon::now();
        });
    }
}
