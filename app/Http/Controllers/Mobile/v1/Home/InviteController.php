<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
	public function index(): JsonResponse
	{
		$list = Invitation::where('user_id', auth()->id())
			->latest('id')
			->limit(100)
			->get(['id','email','status','accepted_at','created_at']);

		return $this->response->statusOk(['data' => ['items' => $list]]);
	}

	public function store(Request $request): JsonResponse
	{
		$emails = (array)$request->input('emails', []);
		$message = (string)$request->input('message', '');

		$created = [];
		foreach ($emails as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				continue;
			}
			$token = Str::random(40);
			$invite = Invitation::create([
				'user_id' => auth()->id(),
				'email' => $email,
				'token' => $token,
				'status' => 'pending',
			]);
			$link = rtrim(config('app.url'), '/') . '/signup?invite=' . $token;
			$body = "You've been invited to Deepmarks.\n" . ($message ? $message . "\n\n" : "") . "Join using this link: {$link}";

			Mail::raw($body, function ($m) use ($email) {
				$m->to($email)->subject('Invitation to Deepmarks');
			});
			$created[] = ['id' => $invite->id, 'email' => $email];
		}

		return $this->response->statusOk(['data' => ['items' => $created], 'message' => 'Invitations sent']);
	}
}


