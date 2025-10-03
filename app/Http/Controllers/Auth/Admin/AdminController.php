<?php

namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function Login(){
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        // Validate user input
        // $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required|min:6',
        // ]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        try {
            // Attempt authentication
            if (!Auth::attempt($request->only('email', 'password'))) {
                return redirect()->route('admin.login')->with('error', 'Invalid email or password');
            }

            // Get authenticated user
            $user = Auth::user();
            if($user->user_type == 'employee'){
                return redirect()->route('import');
            }

            // Generate token
            // $token = $user->createToken('app')->accessToken;

             // Retrieve the intended URL from the session, or default to the admin dashboard
             //  dd(session('redirect_to'));
             $redirectTo = session()->pull('redirect_to', route('admin.dashboard')); 
             return redirect($redirectTo);

            // return redirect()->route('admin.dashboard');

        } catch (Exception $exception) {
            Log::error("Login error: " . $exception->getMessage());

            return redirect()->route('admin.login')
                ->withErrors($exception);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // if (!$user) {
        //     return redirect()->route('admin.login')->with('error', 'Unauthorized. No active session found.');
        // }

        // Logout from only the current device (Passport)
        // $user->tokens()->delete(); // This revokes all issued tokens
        Auth::logout(); // Logs out the user
    
        $request->session()->invalidate(); // Invalidates the session
        $request->session()->regenerateToken(); // Regenerates CSRF token

        return redirect()->route('admin.login')->with('success', 'You have successfully logged out!.');
    }

    public function dashboard(){
        // $total_users = User::whereNot('user_type', 'admin')->count();
        // $total_DGs = User::whereIn('user_type', ['DG'])->count();
        // $total_Drs = User::whereIn('user_type', ['Dr'])->count();
        // $total_Employees = User::whereIn('user_type', ['employee'])->count();
        // $total_Pending = User::whereIn('user_type', ['pending'])->count();

        // return response()->json($users);
        return view('admin.dashboard');
    }

}
