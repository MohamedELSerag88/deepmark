<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class RelationPipeline
{
	private array $with;

	public function __construct(array $with = [])
	{
		$this->with = $with;
	}

	public function apply(Builder $query): Builder
	{
		if (empty($this->with)) {
			return $query;
		}
		return $query->with($this->with);
	}
}


