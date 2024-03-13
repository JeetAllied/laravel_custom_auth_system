<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
//use Mail;
use App\Mail\EmailVerificationMail;

class AuthController extends Controller
{
    //
    public function getRegister()
    {
        return view('auth.register');
    }

    public function checkEmailUnique(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user)
        {
            echo 'false';
        }
        else
        {
            echo 'true';
        }
    }

    public function postRegister(Request $request)
    {
        try{
            $rules = [
                'first_name'=> 'required|min:2|max:100',
                'last_name'=> 'required|min:2|max:100',
                'email'=> 'required|unique:users',
                'password'=> 'required|min:6|max:100',
                'confirm_password'=> 'required|same:password',
                'terms'=> 'required',
                'grecaptcha'=> 'required'
            ];
            $messages = [
                'first_name.required'=> 'First name is required.',
                'first_name.min'=> 'First name must be minimum of 2 characters.',
                'first_name.max'=> 'First name must be maximum of 100 characters.',
                'last_name.required'=> 'Last name is required.',
                'last_name.min'=> 'Last name must be minimum of 2 characters.',
                'last_name.max'=> 'Last name must be maximum of 100 characters.',
                'email.required'=> 'Email is required.',
                'email.unique'=> 'Email is Already in use. Please enter another email.',
                'password.required'=> 'Password is required.',
                'password.min'=> 'Password must be minimum of 6 characters.',
                'password.max'=> 'Password must be maximum of 100 characters.',
                'confirm_password' => 'Confirm Password is required.',
                'confirm_password.same' => 'Confirm password must be same as password.',
                'terms.required'=> 'Terms and conditions must be checked.',
                'grecaptcha.required' => 'Google recaptcha must be checked.',
            ];

            $validator = Validator::make($request->all(),$rules, $messages);
            if($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $grecaptcha = $request->grecaptcha;
            $client = new Client();
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
                ['form_params' =>[
                        'secret' =>env('GOOGLE_CAPTCHA_SECRET'),
                        'response' => $grecaptcha
                    ]
                ]
            );
            $body = json_decode((string)$response->getBody());
            if($body->success == true)
            {
                $data = array(
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'password'=> bcrypt($request->password),
                    'email_verification_code'=> Str::random(40),
                );
                $user = User::create($data);

                //send verification email
                Mail::to($request->email)->send(new EmailVerificationMail($user));
                return Redirect::back()->with('success','User registered successfully. Please check your email and verify email.');
            }
            else
            {
                return Redirect::back()->with('error','Invalid recaptcha');
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();

        }
    }

    public function verifyEmail($verificationCode)
    {
        $user = User::where('email_verification_code', $verificationCode)->first();
        if(!$user)
        {
            return Redirect::back()->with('error','Invalid URL.');
        }
        else
        {
            if($user->email_verified_at)
            {
                return Redirect::back()->with('error','Email is already verified.');
            }
            else
            {
                $res = $user->update(['email_verified_at'=>Carbon::now()]);
                return Redirect::back()->with('success','Email is verified successfully.');
            }
        }
    }

    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        try {
            $rules = [
                'email'=>'required|email',
                'password'=> 'required|min:6|max:100',
                'grecaptcha'=>'required',
            ];
            $messages = [
                'email.required'=> 'Email is required.',
                'email.email'=>'Please enter valid email id.',
                'password.required'=> 'Password is required.',
                'password.min'=> 'Password must be minimum of 6 characters.',
                'password.max'=> 'Password must be maximum of 100 characters.',
                'grecaptcha.required' => 'Google recaptcha must be checked.',
            ];

            $validator = Validator::make($request->all(),$rules, $messages);
            if($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }
            $grecaptcha = $request->grecaptcha;
            //google recaptcha checking
            $client = new Client();
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
                ['form_params' =>[
                    'secret' =>env('GOOGLE_CAPTCHA_SECRET'),
                    'response' => $grecaptcha
                ]
                ]
            );
            $body = json_decode((string)$response->getBody());
            if($body->success == true)
            {
                $user = User::where('email',$request->email)->first();
                if(!$user)
                {
                    //dd('in first if...');
                    return Redirect::back()->with('error','Email is not registered.');
                }
                else{
                    if(!$user->email_verified_at)
                    {
                        return Redirect::back()->with('error','Email is not verified.');
                    }
                    else
                    {
                        if(!$user->is_active)
                        {
                            return Redirect::back()->with('User is not active. Please contact the administrator.');
                        }
                        else
                        {
                            $rememberMe = ($request->remember_me) ? true : false;
                            if(auth()->attempt($request->only('email','password'),$rememberMe))
                            {
                                return Redirect::route('dashboard')->with('success','Login Successful.');
                            }
                            else
                            {
                                return Redirect::back()->with('error','Invalid credentials.');
                            }
                        }
                    }
                }
            }
            else
            {
                return Redirect::back()->withErrors('Invalid recaptcha');
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function logout()
    {
        auth()->logout();
        return Redirect::route('getLogin')->with('success','Logout successful.');
    }

    public function getForgetPassword()
    {
        return view('auth.forget_password');
    }

    public function postForgetPassword(Request $request)
    {
        try {
            $rules = [
                'email'=>'required|email',
            ];

            $messages = [
              'email.required'=> 'Email is required.',
              'email.email'=> 'Valid email id is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if($validator->fails())
            {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $user = User::where('email',$request->email)->first();
            if(!$user)
            {
                return redirect()->back()->with('error','User not found.');
            }
            else
            {
                $resetCode = Str::random(200);
                PasswordReset::create([
                    'user_id'=>$user->id,
                    'reset_code'=> $resetCode
                ]);

                Mail::to($user->email)->send(new ForgetPasswordMail($user->first_name, $resetCode));
                return redirect()->back()->with('success','We have sent you password reset link. Please check your mail.');
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function getResetPassword($resetCode)
    {
        $passwordResetData = PasswordReset::where('reset_code', $resetCode)->first();
        if(!$passwordResetData || Carbon::now()->subMinutes(50) > $passwordResetData->created_at)
        {
            return redirect()->route('getForgetPassword')->with('error','Invalid password reset link or link is expired.');
        }
        else
        {
            return view('auth.reset_password',compact('resetCode'));
        }

    }

    public function postResetPassword($resetCode, Request $request)
    {
        $passwordResetData = PasswordReset::where('reset_code', $resetCode)->first();
        if(!$passwordResetData || Carbon::now()->subMinutes(50) > $passwordResetData->created_at)
        {
            return redirect()->route('getForgetPassword')->with('error','Invalid password reset link or link is expired.');
        }
        else
        {
            $rules = [
                'email'=> 'required|email',
                'password'=> 'required|min:6|max:100',
                'confirm_password'=> 'required|same:password',
            ];

            $messages = [
              'email.required'=> 'Email is required.',
              'email.email' => 'Valid email id is required.',
              'password.required'=>'Password is required',
              'password.min'=>'Password must be more than 6 characters.',
              'password.max'=> 'Password must be less than 100 characters.',
              'confirm_password' => 'Confirm password is required.',
               'confirm_password.same'=> 'Confirm password must be same as password.'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if($validator->fails())
            {
                return Redirect::back()->withErrors($validator);
            }

            $user = User::find($passwordResetData->user_id);
            if($user->email != $request->email)
            {
                return redirect()->back()->with('error','Enter correct email.');
            }
            else
            {
                $passwordResetData->delete();
                $user->update([
                    'password'=>bcrypt($request->password)
                ]);

                return redirect()->route('getLogin')->with('success','Password reset successfully.');
            }
        }
    }

    public function ajaxLogin(Request $request)
    {
        $rules = [
            'email'=>'required|email',
            'password'=> 'required|min:6|max:100',
            'grecaptcha'=>'required',
        ];
        $messages = [
            'email.required'=> 'Email is required.',
            'email.email'=>'Please enter valid email id.',
            'password.required'=> 'Password is required.',
            'password.min'=> 'Password must be minimum of 6 characters.',
            'password.max'=> 'Password must be maximum of 100 characters.',
            'grecaptcha.required' => 'Google recaptcha must be checked.',
        ];

        $validator = Validator::make($request->all(),$rules, $messages);
        if($validator->fails()) {
            return response()->json(['error'=> $validator], 422);
        }
        $grecaptcha = $request->grecaptcha;
        //google recaptcha checking
        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            ['form_params' =>[
                'secret' =>env('GOOGLE_CAPTCHA_SECRET'),
                'response' => $grecaptcha
            ]
            ]
        );
        $body = json_decode((string)$response->getBody());
        if($body->success == true)
        {
            $user = User::where('email',$request->email)->first();
            if(!$user)
            {
                return response()->json(['message'=> 'Email is not registered'], 400);
            }
            else{
                if(!$user->email_verified_at)
                {
                    return response()->json(['message'=> 'Email is not verified.'], 400);
                }
                else
                {
                    if(!$user->is_active)
                    {
                        return response()->json(['message'=> 'User is not active. Please contact the administrator.'], 400);
                    }
                    else
                    {
                        $rememberMe = ($request->remember_me) ? true : false;
                        if(auth()->attempt($request->only('email','password'),$rememberMe))
                        {
                            return response()->json(['message'=> 'Login Successful.','redirect_url'=> route('dashboard')], 200);
                        }
                        else
                        {
                            return response()->json(['message'=> 'Invalid credentials.'], 400);
                        }
                    }
                }
            }
        }
        else
        {
            return response()->json(['message'=> 'Invalid recaptcha.'], 400);
        }
    }

    public function ajaxRegister(Request $request)
    {
        $rules = [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|unique:users',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password',
            'terms' => 'required',
            'grecaptcha' => 'required'
        ];
        $messages = [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be minimum of 2 characters.',
            'first_name.max' => 'First name must be maximum of 100 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be minimum of 2 characters.',
            'last_name.max' => 'Last name must be maximum of 100 characters.',
            'email.required' => 'Email is required.',
            'email.unique' => 'Email is Already in use. Please enter another email.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be minimum of 6 characters.',
            'password.max' => 'Password must be maximum of 100 characters.',
            'confirm_password' => 'Confirm Password is required.',
            'confirm_password.same' => 'Confirm password must be same as password.',
            'terms.required' => 'Terms and conditions must be checked.',
            'grecaptcha.required' => 'Google recaptcha must be checked.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['error' => $validator], 422);
            //return Redirect::back()->withInput()->withErrors($validator);
        }

        $grecaptcha = $request->grecaptcha;
        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            ['form_params' => [
                'secret' => env('GOOGLE_CAPTCHA_SECRET'),
                'response' => $grecaptcha
            ]
            ]
        );
        $body = json_decode((string)$response->getBody());
        if ($body->success == true) {
            $data = array(
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'email_verification_code' => Str::random(40),
            );
            $user = User::create($data);

            //send verification email
            Mail::to($request->email)->send(new EmailVerificationMail($user));
            return response()->json(['message' => 'User registered successfully. Please check your email and verify email.', 'redirect_url' => route('dashboard')], 200);
            //return Redirect::back()->with('success','User registered successfully. Please check your email and verify email.');
        }
    }
}
