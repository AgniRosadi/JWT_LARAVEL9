<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        
        //cek token
        $token  = Auth::attempt($credentials);
        if(!$token)
        {
            return response()->json([
                'status' => 'error',
                "message" => 'Unauthorized',
                'error_code' => 401
            ]);
        }
        
        //cek user
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ]);
    }


    //Register
    public function register(Request $request)
    {
        $users = DB::table('users')->get();
        $tes = [];
        $email = $request->email;
        foreach ($users as $user)
        {
            $tes = $user->email;
        }
        $rules =([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        $response = response()->json([
            'status' => 'error',
            'message' => 'Error, Data can not be empty',
            'error_code' => 400
        ]);
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return $response;
        }
        
        if($email == $tes){
            return response()->json([
                'status' => 'error',
                'message' => 'Error, email already exists',
                'error_code' => 401
            ]);
        }
        
        //query add user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        $token = Auth::login($user);
        // var_dump($token);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}