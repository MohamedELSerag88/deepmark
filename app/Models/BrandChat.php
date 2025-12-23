<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandChat extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'topic',
		'language',
		'answers',
		'response',
		'raw_response',
	];

	protected function casts(): array
	{
		return [
			'answers' => 'array',
			'response' => 'array',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


