<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//This file is based on the Tank Auth. I cut some things off to fit "Conventions over Configurations",
//except for functional stuffs.
//Last Edit: 2016-02-27 14:30:11

/*
|--------------------------------------------------------------------------
| Website details
|
| These details are used in emails sent by authentication library.
|--------------------------------------------------------------------------
*/
$config['website_name'] = 'sky_auth.loc';
$config['webmaster_email'] = '858667651@qq.com';
$config['webmaster_name'] = 'No-Reply';

/*
|--------------------------------------------------------------------------
| Registration settings
|
| 'allow_registration' = Registration is enabled or not
| 'captcha_registration' = Registration uses CAPTCHA
| 'email_activation' = Requires user to activate their account using email after registration.
| 'email_activation_expire' = Time before users who don't activate their account getting deleted from database. Default is 48 hours (60*60*24*2).
| 'email_account_details' = Email with account details is sent after registration (only when 'email_activation' is FALSE).
| 'use_username' = Username is required or not.

|--------------------------------------------------------------------------
*/
$config['allow_registration'] = true;
$config['captcha_registration'] = TRUE;
$config['email_activation'] = true;
$config['email_activation_expire'] = 60*60*24*2;
$config['email_account_details'] = TRUE;


/*
|--------------------------------------------------------------------------
| Forgot password settings
|
| 'forgot_password_expire' = Time before forgot password key become invalid. Default is 15 minutes (60*15).
|--------------------------------------------------------------------------
*/
$config['forgot_password_expire'] = 60*15;

/* End of file sky_auth.php */
/* Location: ./application/config/sky_auth.php */