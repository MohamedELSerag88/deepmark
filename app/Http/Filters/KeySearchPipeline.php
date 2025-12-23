<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class KeySearchPipeline
{
	private array $columns;
	private ?string $term;

	public function __construct(array $columns = [], ?string $term = null)
	{
		$this->columns = $columns;
		$this->term = $term;
	}

	public function apply(Builder $query): Builder
	{
		if (!$this->term || empty($this->columns)) {
			return $query;
		}
		$term = '%' . str_replace('%', '\%', $this->term) . '%';
		return $query->where(function (Builder $q) use ($term) {
			foreach ($this->columns as $col) {
				$q->orWhere($col, 'LIKE', $term);
			}
		});
	}
}


