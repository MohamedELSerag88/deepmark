<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
	public const KEY_BRAND_NAMES_GENERATE = 'brand_names_generate';

	public const KEY_BRAND_NAMES_SIMILAR = 'brand_names_similar';

	protected $fillable = [
		'key',
		'name',
		'system_template',
		'user_template',
	];
}
