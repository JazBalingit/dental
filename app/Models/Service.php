<?php
// Place in: app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'tbl_services';
    protected $primaryKey = 'ServiceID';

    protected $fillable = [
        'ServiceName',
        'Description',
        'Price',
        'IsArchived',
    ];

    protected $casts = [
        'IsArchived' => 'boolean',
        'Price' => 'decimal:2',
    ];
}