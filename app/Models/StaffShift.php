<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'shift_date', 'start_time', 'end_time', 'hours',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class)->withTrashed();
    }
}
