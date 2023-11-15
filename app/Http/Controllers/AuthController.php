<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Store a new user.
     *
     * @param  Request 
     * @return Response
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|unique:user,username|string|min:6|max:50',
            'password' => 'required|confirmed|min:8|max:255',
            'person_name' => 'required|string|min:10|max:100',
            'sys_role' => 'required|int|gte:1|lte:3',
            'dob' => [
                'required',
                'date',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    try {
                        $age = Carbon::parse($value)->age;

                        if ($age < 18) {
                            $fail('Failed. Due to cash operations, minimum age is 18 years.');
                        }
                    } catch (Exception $e){
                        return response()->json(['result' => 'Failed due to invalid date of birth.'], 400);
                    }                
                },
            ],
        ]);

        try {
            $user = new User;
            $user->active = config('enums.ACCOUNT_STATUS.ACTIVE');
            $user->sys_level = config('enums.USER_LEVEL.COMMOM');
            $user->sys_role = $request->input('sys_role');
            $user->person_name = $request->input('person_name');
            $user->username = $request->input('username');
            $user->password = app('hash')->make($request->input('password'));
            $user->birthdate = $request->input('dob');

            $user->save();

            return response()->json([
                'result' => 'success.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'result' => 'failed.'
            ], 503);
        }
    }
	
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  
     * @return Response 
     */	 
    public function login(Request $request)
    {

        $this->validate($request, [
            'username' => 'required|string|min:6|max:50',
            'password' => 'required|string|min:8|max:255',
        ]);

        $credentials = $request->only(['username', 'password']);

        if (! $token = Auth::attempt($credentials)) {			
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (Auth::user()->active != 1){
            return response()->json(['message' => 'Gone'], 410);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout the user (Invalidate the token).
     * @param  Request  
     * @return Response
     */
    public function logout()
    {
        Auth::logout(); 
        
        Auth::guard('api')->logout(); 

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

}