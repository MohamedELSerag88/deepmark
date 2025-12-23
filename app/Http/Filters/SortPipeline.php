<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class SortPipeline
{
	private ?string $field;
	private string $direction;

	public function __construct(?string $field = null, ?string $direction = null)
	{
		$this->field = $field ?: null;
		$dir = strtolower((string)$direction);
		$this->direction = in_array($dir, ['asc','desc'], true) ? $dir : 'asc';
	}

	public function apply(Builder $query): Builder
	{
		if (!$this->field) {
			return $query;
		}
		return $query->orderBy($this->field, $this->direction);
	}
}


