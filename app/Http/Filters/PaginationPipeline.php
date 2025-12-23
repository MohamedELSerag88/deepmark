<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class PaginationPipeline
{
	private int $page;
	private int $perPage;

	public function __construct(int $page = 1, int $perPage = 0)
	{
		$this->page = max(1, $page);
		$this->perPage = max(0, $perPage);
	}

	public function apply(Builder $query): Builder
	{
		if ($this->perPage <= 0) {
			return $query;
		}
		$offset = ($this->page - 1) * $this->perPage;
		return $query->skip($offset)->take($this->perPage);
	}
}


