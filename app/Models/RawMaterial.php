<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RawMaterial extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'name',
        'code',
        'unit',
        'notes',
    ];

    protected function cost(): Attribute
    {
        return $this->secureNumeric('cost_enc');
    }
}
