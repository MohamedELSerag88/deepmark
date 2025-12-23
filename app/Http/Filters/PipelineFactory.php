<?php

namespace App\Http\Filters;

class PipelineFactory
{
	public static function make(): array
	{
		$req = request();
		return [
			new KeySearchPipeline((array)$req->input('search_columns', []), (string)$req->input('search')),
			new KeyRelationSearchPipeline((array)$req->input('search_relations', []), (string)$req->input('search')),
			new RelationPipeline((array)$req->input('with', [])),
			new SortPipeline((string)$req->input('sort'), (string)$req->input('dir')),
			new PaginationPipeline((int)$req->input('page', 1), (int)$req->input('per_page', 0)),
		];
	}
}


