<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
	use HasFactory;

	protected $fillable = [
		'question_en',
		'question_ar',
		'question_type',
		'answers',
			'description_en',
			'description_ar',
			'video_url',
			'video_path',
			'image_url',
			'example_answer',
			'resources',
	];

	protected function casts(): array
	{
		return [
			'answers' => 'array',
			'resources' => 'array',
		];
	}
}


