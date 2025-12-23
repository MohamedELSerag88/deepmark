<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'brand_chat_id',
		'meeting_at',
		'notes',
		'status',
	];

	protected function casts(): array
	{
		return [
			'meeting_at' => 'datetime',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function brandChat()
	{
		return $this->belongsTo(BrandChat::class, 'brand_chat_id');
	}
}


