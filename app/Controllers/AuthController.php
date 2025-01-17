<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\CIAuth;  //ye library create kiya hai iss library ko admin ki login detail ko session me store karne 
                           //va session se delete karne keliye bana hai. 
use App\Libraries\Hash;  //ye library create kiya hai ki password ka format hash hai ya nahi.

use App\Models\User;
use App\Models\PasswordResetToken;
use Carbon\Carbon;


class AuthController extends BaseController
{

    protected $helpers = ['url','form', 'CIMail'];

    public function loginForm()
    {
     
        $data = [

            'pageTitle'=>'Login',
            'validation'=>null

        ];   

        return view('backend/pages/auth/login', $data);

    }


    public function loginHandler(){

        $fieldType = filter_var($this->request->getVar('login_id'), FILTER_VALIDATE_EMAIL) ? 'email':'username';

        if($fieldType == 'email'){

            $isValid = $this->validate([

                'login_id'=>[
                    'rules'=>'required|valid_email|is_not_unique[users.email]',
                    'errors'=>[
                        'required'=>'Email is required',
                        'valid_email'=>'Please, check the email field. It does not appears to be valid.',
                        'is_not_unique'=>'Email is not exists in our system.'
                    ]
                ],

                'password'=>[

                    'rules'=>'required|min_length[5]|max_length[45]',
                    'errors'=>[
                        'required'=>'Password is required',
                        'min_length'=>'Password must have atleast 5 charactors in length.',
                        'max_length'=>'Password must not have charactors more than 45 in length.'
                    ]    
                ]
  
            ]);

        }else{

            $isValid = $this->validate([

                'login_id'=>[
                    'rules'=>'required|is_not_unique[users.username]',
                    'errors'=>[
                        'required'=>'Username is required',
                        'is_not_unique'=>'Username is not exists in our system.'
                    ]
                ],

                'password'=>[

                    'rules'=>'required|min_length[5]|max_length[45]',
                    'errors'=>[
                        'required'=>'Password is required',
                        'min_length'=>'Password must have atleast 5 charactors in length.',
                        'max_length'=>'Password must not have charactors more than 45 in length.'
                    ]    
                ]

            ]);

        }

        if(!$isValid){

            return view('backend/pages/auth/login', ['pageTitle'=>'Login', 'validation'=>$this->validator]);

        }else{

            $user = new User();

            $userInfo = $user->where($fieldType, $this->request->getVar('login_id'))->first();
            $check_password = Hash::check($this->request->getVar('password'), $userInfo['password']);

            if( !$check_password){

                return redirect()->route('admin.login.form')->with('fail','wrong password')->withInput();

            }else{

                CIAuth::setCIAuth($userInfo);

                return redirect()->route('admin.home');

            }

        }

    }


    public function forgotForm(){

        $data = array(

            'pageTitle'=>'Forgot password',
            'validation'=>null,
        );

        return view('backend/pages/auth/forgot', $data);

    }


    public function sendPasswordResetLink(){

        $isValid = $this->validate([

            'email'=>[
                'rules'=>'required|valid_email|is_not_unique[users.email]',
                'errors'=>[
                    'required'=>'Email is required',
                    'valid_email'=>'Please check email field. It does not appears to be valid',
                    'is_not_unique'=>'Email not exists in system'
                ]
            ]

        ]);


        if(!$isValid){

            return view('backend/pages/auth/forgot',[

                'pageTitle'=>'Forgot password',
                'validation'=>$this->validator

            ]);

        }else{

            $user = new User();

            $user_info = $user->asObject()->where('email', $this->request->getVar('email'))->first();

            //Generate token
            $token = bin2hex(openssl_random_pseudo_bytes(65));

            //get reset password token
            $password_reset_token = new PasswordResetToken();

            $isOldTokenExists = $password_reset_token->asObject()->where('email', $user_info->email)->first();

            //print_r($isOldTokenExists);
            //exit;

            if($isOldTokenExists){

                //update existing token
                $password_reset_token->where('email', $user_info->email)
                                     ->set(['token'=>$token, 'created_at'=>Carbon::now()])
                                     ->update();

            }else{

                $password_reset_token->insert([

                    'email'=>$user_info->email,
                    'token'=>$token,
                    'created_at'=>Carbon::now()

                ]);

            }

            //Create action link
            //$actionLink = route_to('admin.reset-password', $token);
            $actionLink = base_url(route_to('admin.reset-password', $token));

            $mail_data = array(
                'actionLink'=>$actionLink,
                'user'=>$user_info
            );

            //print_r($mail_data);
            //exit;

            $view = \Config\Services::renderer();

            $mail_body = $view->setVar('mail_data', $mail_data)->render('email-templates/forgot-email-template');

            $mailConfig = array(

                'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                'mail_from_name'=>env('EMAIL_FROM_NAME'),
                'mail_recipient_email'=>$user_info->email,
                'mail_recipient_name'=>$user_info->name,
                'mail_subject'=>'Reset Password',
                'mail_body'=>$mail_body

            );

            //print_r($mailConfig);
            //exit;

            //Send email
            if(sendEmail($mailConfig)){

                return redirect()->route('admin.forgot.form')->with('success','We have e-mailed your password reset link.');

            }else{

                return redirect()->route('admin.forgot.form')->with('fail','Something went wrong');

            }

        }
 

    }


    public function resetPassword($token){

        //echo $token;

        $passwordResetPassword = new PasswordResetToken();

        $check_token_have = $passwordResetPassword->asObject()->where('token', $token)->first();

        if(!$check_token_have){ 

            //if token not exist
            return redirect()->route()->with('fail','Invalid token, Request another reset password link');

        }else{

            //check if token not expired (Not older than 15 minutes)
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s', $check_token_have->created_at)->diffInMinutes(Carbon::now());

            if($diffMins > 15){

                //check if token not valid (Older than 15 minutes)
                return redirect()->route('admin.forgot.form')->with('fail','Token is not valid now. Request another reset password link');

            }else{

                return view('backend/pages/auth/reset', [
                    'pageTitle'=>'Reset Password', 
                    'validation'=>null, 
                    'token'=>$token
                ]);


            }

        }

    }


    public function resetPasswordHandler($token){

        $isValid = $this->validate([

            'new_password'=>[

                'rules'=>'required|min_length[5]|max_length[20]|is_password_strong[new_password]',
                'errors'=>[

                    'required'=>'Enter new password',
                    'min_length'=>'New password must have atleast minimum of 5 charactors.',
                    'max_length'=>'New password must have maximum of 20 charactors',
                    'is_password_strong'=>'New password must contains atleast 1 uppercase, 1 lowercase, 1 number and 1 special charactor.'
                ]

            ],

            'confirm_new_password'=>[

                'rules'=>'required|matches[new_password]',
                'errors'=>[
                    'required'=>'Confirm new password',
                    'matches'=>'Password not matches'

                ]

            ]

        ]);


        if(!$isValid){

            return view('backend/pages/auth/reset',[

                'pageTitle'=>'Reset Password',
                'validation'=>null,
                'token'=>$token

            ]);

        }else{

            //get token details
            $passwordResetPassword = new PasswordResetToken();
            $get_token = $passwordResetPassword->asObject()->where('token', $token)->first();

            //get user (admin) details
            $user = new User();
            $user_info = $user->asObject()->where('email', $get_token->email)->first();

            if(!$get_token){

                return redirect()->back()->with('fail','Invalid token!')->withInput();

            }else{

                //update admin password in DB
                $user->where('email', $user_info->email)
                     ->set(['password'=>Hash::make($this->request->getVar('new_password'))])
                     ->update();

                //send notification to user (admin) email address
                $mail_data = array(
                
                    'user'=>$user_info,
                    'new_password'=>$this->request->getVar('new_password')

                );

                $view = \Config\Services::renderer();
                $mail_body = $view->setVar('mail_data', $mail_data)->render('email-templates/password-changed-email-template');

                $mailConfig = array(

                    'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                    'mail_from_name'=>env('EMAIL_FROM_NAME'),
                    'mail_recipient_email'=>$user_info->email,
                    'mail_recipient_name'=>$user_info->name,
                    'mail_subject'=>'Password Changed',
                    'mail_body'=>$mail_body

                ); 

                if(sendEmail($mailConfig)){

                    //delete token
                    $passwordResetPassword->where('email', $user_info->email)->delete();

                    //redirect and display message on login page
                    return redirect()->route('admin.login.form')->with('success', 'Done!, Your password has been changed, Use new password to login into system.');

                }else{

                    return redirect()->back()->with('fail','Something went wrong')->withInput();

                }    

            }

        }


    }


}
