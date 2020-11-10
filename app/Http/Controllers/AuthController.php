<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
	/**
	 * Function to generate a acces token in the user email and password combination
	 * is correct
	 * 
	 * @param  Request $request
	 * @return Response (JSON)
	 */
    public function requestToken(Request $request)
	{
	    $request->validate([
	        'email' => 'required|email',
	        'password' => 'required',
	        'device_name' => 'required',
	    ]);

	    $user = User::where('email', $request->email)->first();

	    if (! $user || ! Hash::check($request->password, $user->password)) {
	    	$response = [
	    		'status' => 'error',
				'message' => 'The provided credentials are incorrect.'
			];
			return response()->json($response, 200);
	    }

	    $data['access_token'] = $user->createToken($request->device_name)->plainTextToken;

	    $response = [
    		'status' => 'success',
    		'message' => 'Token generated',
    		'data' => $data,
		];
	    return response()->json($response, 200);
	}

	/**
	 * Function if rediect on login route
	 * 
	 * @param  Request $request
	 * @return Response (JSON)
	 */
	public function login(Request $request) 
	{
		$response = [
			'status' => 'error',
            'message' => 'Unauthorised access',
            'data' => ''
        ];
		return response()->json($response, 200);
	}
}
