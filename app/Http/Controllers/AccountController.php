<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AccountController extends Controller
{
    //This method show register page 
    public function register(){
        return view('account.register');
    }

    //  this method will register user
    public function processRegister(request $request){
        $validator = Validator::make($request->all (),[
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.register')->withInput()->withErrors($validator);
        }


        //Now Register User

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('account.login')->with('success','You Have Registerd successfully.');
    }


    public function login(){
        return view('account.login');
    }

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return redirect()->route('account.login')->withInput()->withErrors($validator);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('account.profile');
        } else{
            return redirect()->route('account.login')->with('error','Either email/password is incorrect.');
        }

    }

    //This method will show user profile page
    public function profile(){
        $user = User::find(Auth::user()->id);
        return view('account.profile',[
            'user' => $user
        ]);
    }

    //This method will update user profile
    public function updateProfile(Request $request){

        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,'.Auth::user()->id.',id',
        ];

        if (!empty($request->image)) {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()) {
            return redirect()->route('account.profile')->withInput()->withErrors($validator);
        }

        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Here we will upload Image
        if (!empty($request->image)) {

            //delete old image here
            File::delete(public_path('uploads/profile/'.$user->image));
            File::delete(public_path('uploads/profile/thumb/'.$user->image));

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();

            $imageName = time().'.'.$ext; //every time different name
            
            $image->move(public_path('uploads/profile'),$imageName);
            
            $user->image = $imageName;
            $user->save();
            
            $manager = new ImageManager(Driver::class);
            $img = $manager->read(public_path('uploads/profile/'.$imageName)); // 800 x 600

            $img->cover(150, 150);
            $img->save(public_path('uploads/profile/thumb/'.$imageName));
        }


        return redirect()->route('account.profile')->with('success','Profile Updated Successfully.');

    }
    



    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }






}