<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    const Job_seeker='seeker';
    const Job_poster='employer';

    public function createSeeker(){
        return view('user.seeker-register');
    }

   public function createEmployer(){
    return view('user.employer-register');

   }

    public function storeSeeker(){

       request()->validate([
        'name'=>['required','string','max:255'],
        'email'=>['required','string','email','max:255','unique:users'],
        'password'=>['required']
       ]);
         

       $user= User::create([
         'name'=>request('name'),
         'email'=>request('email'),
         'password'=> bcrypt(request('password')),
         'user_type'=>self::Job_seeker
        ]);

        Auth::login($user);

        $user->sendEmailVerificationNotification();
        return response()->json('success');


        //return redirect()->route('verification.notice')->with('successMessage','Your account was created');
    }

    public function storeEmployer(){
        
        request()->validate([
         'name'=>['required','string','max:255'],
         'email'=>['required','string','email','max:255','unique:users'],
         'password'=>['required']
        ]);
          
 
        $user= User::create([
          'name'=>request('name'),
          'email'=>request('email'),
          'password'=> bcrypt(request('password')),
          'user_type'=>self::Job_poster,
          'user_trial' => now()->addWeek()
         ]);

         Auth::login($user);


         $user->sendEmailVerificationNotification();

         return response()->json('success');

        // return redirect()->route('verification.notice')->with('successMessage','Your account was created');

 
     }


    public function login()  {
        return view('user.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentails = $request->only('email', 'password');

        if(Auth::attempt($credentails)) {
            if(!auth()->user()->email_verified_at)
            {
                return redirect()->to('/verify');
            }
            if(auth()->user()->user_type == 'employer') {
                return redirect()->to('dashboard');
            }else {
                return redirect()->to('/');
            }
        }

        return 'Wrong email or password';
    }


    public function logout()  {
        auth()->logout();
        return redirect()->route('login');
    }



    public function profile()
    {
        return view('profile.index');
    }

    // public function update(Request $request)
    // {
    //     if($request->hasFile('profile_pic')) {
    //         $imagepath = $request->file('profile_pic')->store('profile', 'public');   

    //         User::find(auth()->user()->id)->update(['profile_pic' => $imagepath]);
    //     }

    //     User::find(auth()->user()->id)->update($request->except('profile_pic'));

    //     return back()->with('success','Your profile has been updated');
    // }

    public function update(Request $request)
{
    try {

        $request->validate([
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example: max 2MB file size
        ]);

        if($request->hasFile('profile_pic')) {
            $imagepath = $request->file('profile_pic')->store('profile', 'public');   
            User::find(auth()->user()->id)->update(['profile_pic' => $imagepath]);
        }

        User::find(auth()->user()->id)->update($request->except('profile_pic'));

        return back()->with('success','Your profile Pic has been updated');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}


    public function seekerProfile()
    {
        return view('seeker.profile');
    } 

    // public function changePassword(Request $request)
    // {
    //     $request->validate([
    //         'current_password' => 'required',
    //         'password' => 'required|min:8|confirmed'
    //     ]);

    //     $user = auth()->user();
    //     if(!Hash::check($request->current_password, $user->password)) {
    //         return back()->with('error','Current password is incorrect');
    //     }

    //     $user->password = Hash::make($request->password);
    //     $user->save();

    //     return back()->with('success','Your password has been updated successfully');
    // }

    public function changePassword(Request $request)
{
    try {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = auth()->user();
        if(!Hash::check($request->current_password, $user->password)) {
            return back()->with('error','Current password is incorrect');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success','Your password has been updated successfully');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}



    // public function uploadResume(Request $request)
    // {
    //     $this->validate($request,[
    //         'resume' => 'required|mimes:pdf,doc,docx',
    //     ]);

    //     if($request->hasFile('resume')) {
    //         $resume = $request->file('resume')->store('resume', 'public');   
    //         User::find(auth()->user()->id)->update(['resume' => $resume]);

    //         return back()->with('success','Your resume has been updated successfully');

    //     }
    //     return back()->with('error','Your resume has not been updated');


    // }

    public function uploadResume(Request $request)
{
    try {
        $this->validate($request,[
            'resume' => 'required|mimes:pdf,doc,docx',
        ]);

        if($request->hasFile('resume')) {
            $resume = $request->file('resume')->store('resume', 'public');   
            User::find(auth()->user()->id)->update(['resume' => $resume]);

            return back()->with('success','Your resume has been updated successfully');
        } else {
            throw new \Exception('No file uploaded.');
        }
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

public function jobApplied()
{
    $users =  User::with('listings')->where('id',auth()->user()->id)->get();

    return view('seeker.job-applied',compact('users'));
}


}
