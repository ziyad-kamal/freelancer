<?php

namespace App\Repositories;

use App\Http\Requests\UserRequest;
use App\Interfaces\Repository\AuthRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
	public function login(UserRequest $request):RedirectResponse
	{
		$credentials = $request->only('email', 'password');

		if (auth()->attempt($credentials, $request->filled('remember_me'))) {
			return to_route('home');
		} else {
			return to_route('get.login')->with(['error' => 'incorrect password or email']);
		}
	}

	public function storeUser(UserRequest $request):int
	{
		$data = $request->safe()->except('password') + ['password' => Hash::make($request->password), 'created_at' => now()];

		return DB::table('users')->insertGetId($data);
	}
}
