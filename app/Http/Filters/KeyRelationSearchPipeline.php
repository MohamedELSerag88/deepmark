<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class KeyRelationSearchPipeline
{
	/**
	 * relations format: [['relation' => 'user', 'columns' => ['name','email']], ...]
	 */
	private array $relations;
	private ?string $term;

	public function __construct(array $relations = [], ?string $term = null)
	{
		$this->relations = $relations;
		$this->term = $term;
	}

	public function apply(Builder $query): Builder
	{
		if (!$this->term || empty($this->relations)) {
			return $query;
		}
		$term = '%' . str_replace('%', '\%', $this->term) . '%';
		foreach ($this->relations as $def) {
			$relation = $def['relation'] ?? null;
			$columns = (array)($def['columns'] ?? []);
			if (!$relation || empty($columns)) {
				continue;
			}
			$query->orWhereHas($relation, function (Builder $q) use ($columns, $term) {
				foreach ($columns as $col) {
					$q->orWhere($col, 'LIKE', $term);
				}
			});
		}
		return $query;
	}
}


