<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\InvitationResource;
use App\Services\Invite\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteController extends Controller
{
	public function __construct(
		private readonly InviteService $inviteService,
	) {
		parent::__construct();
	}

	public function index(): JsonResponse
	{
		$list = $this->inviteService->listForUser(auth('api')->id());

		return $this->statusOk([
			'data' => [
				'items' => InvitationResource::collection($list),
			],
		]);
	}

	public function store(Request $request): JsonResponse
	{
		$created = $this->inviteService->store(
			(array) $request->input('emails', []),
			(string) $request->input('message', ''),
			auth('api')->id()
		);

		return $this->statusOk([
			'data' => [
				'items' => InvitationResource::collection(collect($created)),
			],
			'message' => 'Invitations sent',
		]);
	}
}
