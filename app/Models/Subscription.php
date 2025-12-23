<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'plan_id',
		'status', // pending, active, canceled
		'started_at',
		'ends_at',
		'stripe_session_id',
		'stripe_subscription_id',
	];

	protected function casts(): array
	{
		return [
			'started_at' => 'datetime',
			'ends_at' => 'datetime',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function plan()
	{
		return $this->belongsTo(Plan::class);
	}
}


