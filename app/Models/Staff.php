<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = ['user_id', 'staff_code', 'full_name', 'phone', 'cccd', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
