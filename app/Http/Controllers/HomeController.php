<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Newsletter\Facades\Newsletter;

class HomeController extends Controller
{
    //
    public function home()
    {
        return view('home');
    }

    public function subscribe(Request $request)
    {
        try {
            $rules = [
              'subscriber_email'=> 'required|email',
            ];

            $messages = [
              'subscriber_email.required' => 'Email is required.',
              'subscriber_email.email' => 'Please enter valid email id.',
            ];

            $validator = Validator::make($request->all(),$rules,$messages);
            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator);
            }

            if(Newsletter::isSubscribed($request->subscriber_email))
            {
                return redirect()->back()->with('error','Email is already subscribed.');
            }
            else
            {
                Newsletter::subscribe($request->subscriber_email);
                return redirect()->back()->with('success','Email is subscribed successfully.');
            }
        }
        catch(\Exception $e)
        {
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
}
