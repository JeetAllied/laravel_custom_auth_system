<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function dashboard()
    {
        return view('profile.dashboard');
    }

    public function editProfile()
    {
        $user = auth()->user();
        $data['user'] = $user;
        return view('profile.edit_profile', $data);
    }

    public function updateProfile(Request $request)
    {
        try {
            $rules = [
                'first_name'=> 'required|min:2|max:100',
                'last_name'=> 'required|min:2|max:100',
            ];

            $messages = [
                'first_name.required'=> 'First name is required.',
                'first_name.min' => 'First name must be more than 2 characters.',
                'first_name.max' => 'First name must be less than 100 characters.',
                'last_name.required'=> 'Last name is required.',
                'last_name.min' => 'Last name must be more than 2 characters.',
                'last_name.max' => 'Last name must be less than 100 characters.',
            ];

            $validator = Validator::make($request->all(),$rules, $messages);
            if($validator->fails())
            {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $user = auth()->user();
            $user->update([
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
            ]);

            return redirect()->route('editProfile')->with('success','Profile Updated Successfully.');
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function changePassword()
    {
        return view('profile.change_password');
    }

    public function updatePassword(Request $request)
    {
        try {
            $rules = [
              'old_password'=>'required|min:6|max:100',
              'new_password'=>'required|min:6|max:100',
              'confirm_password'=> 'required|same:new_password',
            ];

            $messages = [
                'old_password.required'=> 'Old password is required.',
                'old_password.min' => 'Old password must be more than 6 characters.',
                'old_password.max'=> 'Old password must be less than 100 characters.',
                'new_password.required'=> 'New password is required.',
                'new_password.min' => 'New password must be more than 6 characters.',
                'new_password.max'=> 'New password must be less than 100 characters.',
                'confirm_password.required'=> 'Confirm password is required.',
                'confirm_password.same' => 'Confirm password must be same as new password.',
            ];

            $validator = Validator::make($request->all(),$rules, $messages);
            if($validator->fails())
            {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            $currentUser = auth()->user();
            //check old password matches or not
            if(Hash::check($request->old_password, $currentUser->password))
            {
                $currentUser->update([
                   'password'=>bcrypt($request->new_password)
                ]);

                return redirect()->back()->with('success','Password Updated Successfully.');
            }
            else
            {
                return redirect()->back()->with('error','Old password doesn\'t match.');
            }

        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
