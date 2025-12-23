<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class Pipeline
{
	public static function run(Builder $query, array $pipes): Builder
	{
		foreach ($pipes as $pipe) {
			if (method_exists($pipe, 'apply')) {
				$query = $pipe->apply($query);
			}
		}
		return $query;
	}
}


