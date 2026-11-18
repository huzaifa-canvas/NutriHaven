<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberHealthDetail extends Model
{
    protected $table = 'member_health_details';

    protected $fillable = ['member_id', 'group', 'category', 'answers', 'is_active'];

    protected $casts = [
        'answers' => 'array',
        'is_active' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Members::class, 'member_id', 'member_id');
    }
}
