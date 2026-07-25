<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandChat extends Model
{
	use HasFactory;

	protected $fillable = [
		'parent_id',
		'user_id',
		'topic',
		'project_name',
		'selected_brand_name',
		'language',
		'answers',
		'response',
		'raw_response',
		'device_token',
		'branding_email_sent',
	];

	protected function casts(): array
	{
		return [
			'answers' => 'array',
			'response' => 'array',
			'branding_email_sent' => 'boolean',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function messages()
	{
		return $this->hasMany(BrandChatMessage::class);
	}

	public function nameSuggestions()
	{
		return $this->hasMany(BrandNameSuggestion::class)->orderBy('suggestion_index');
	}
}


