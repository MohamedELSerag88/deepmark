<?php

namespace App\Services\Question;

use App\Models\Question;
use Illuminate\Support\Collection;

class QuestionService
{
	public function list(): Collection
	{
		return Question::query()->latest()->get();
	}
}
