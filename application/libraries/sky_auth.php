<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class  Sky_auth {

    protected $user;
    protected $errors;
    protected $loggedOut = false;
    
    public function __construct()
    {
        $this->load->helper(array('cookie'));
        $this->load->model(array('sky_auth_model'));
        $this->load->library(array('session'));
    }
    /**
     * We can direct access CI resources from this custom class via the magic __get function.
     *
     * @access public
     * @param $var
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function check()
    {
        if($this->loggedOut){
            return false;
        }

        if($this->user != false){
            return true;
        }

        $id = $this->session->userdata('user_id');

        if($id != false){
            $this->user = $this->sky_auth_model->getUserById($id);
            return true;
        }

        return false;
    }

    public function user()
    {
        if($this->check()){
            return $this->user;
        }
        return false;
    }

    public function set_user($user)
    {
        $this->user = $user;
    }

    public function reset_password($uuid, $token, $password)
    {
        $user=$this->sky_auth_model->findByUuid($uuid);

        if($user == false){
            $this->set_error($this->lang->line('sky_auth_message_reset_failed'));
            return false;
        }

        if($user->reset_password_token == false){
            $this->set_error('Operation Terminated!');
            return false;
        }

        if(! hash_equals($user->reset_password_token,hash('sha256', $token))){
            $this->set_error($this->lang->line('sky_auth_message_reset_failed'));
            return false;
        }

        $this->sky_auth_model->reset_password($uuid, $password);

    }

    public function change_password($uuid, $oldPassword, $newPassword)
    {
        if(! $this->sky_auth_model->verifyPassword($uuid, $oldPassword)){
            $this->set_error($this->lang->line('sky_auth_incorrect_password'));
            return false;
        }

        $this->sky_auth_model->updatePassword($uuid, $newPassword);
        $this->logout();
    }


    public function try_auto_login()
    {
        if(isset($_COOKIE['rm'])){
            $uuid = substr($_COOKIE['rm'],0,36);
            $token = substr($_COOKIE['rm'],36);
            return $this->sky_auth->autologin($uuid, $token);
        }
    }

    public function autologin($uuid, $token)
    {
        $user = $this->sky_auth_model->autologin($uuid, $token);

        if($user === false ){
            return false;
        }

        if($user->is_banned === '1'){
            return false;
        }

        $hash = hash('sha256',$token);

        if(! hash_equals($user->remember_token,$hash)){
            return false;
        }

        $this->user = $user;

        $session_data = array(
            'user_id' => $user->id,
            'user_name' => $user->name
        );

        $this->create_session($session_data);


        $token = $this->generate_token();
        $this->sky_auth_model->remember_me($user->uuid, $token);
        return true;
    }
    
    public function logout()
    {
        //delete auto login cookies
        if(isset($_COOKIE['rm'])){
            setcookie('rm', '', 1, '/');
        }
        $this->session->set_userdata(array('user_id' => '', 'user_name' => ''));
        return $this->session->sess_destroy();
    }
    
    /**
     * @param $user
     * @return bool
     */
    public function register($user)
    {
        $user['password'] = password_hash($user['password'],PASSWORD_DEFAULT);
        $user['activate_token'] = hash('md5',$this->generate_token());

        // Try to register, if successful, it will return user id, otherwise return false.
        $user['id'] = $this->sky_auth_model->register($user);

        if(! $user['id'] ){
            // Fail to create db row.
            return false;
        }

        $session_data = array(
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'is_active' => '0'
        );

        $this->create_session($session_data);

        // If new user need to be activated ...
        if($this->config->item('email_activation','sky_auth') === true) {

            $activate_data = array(
                'website_name' => $this->config->item('website_name', 'sky_auth'),
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'activate_token' => $user['activate_token']
            );

            // Send an activation email.
            $this->send_activate_email($activate_data);
        }
        // Registration complete.
        return true;
    }

    public function login($credentials, $remember)
    {

        $this->user = $this->sky_auth_model->login($credentials['email'],$credentials['password']);
        if( $this->user === false){
            $this->set_error($this->lang->line('sky_auth_incorrect_email_or_password'));
            $this->sky_auth_model->increase_attempts($credentials['email'], $credentials['ip_address']);
            return false;
        }

        if($this->is_user_banned($this->user->email)){
            $this->set_error($this->lang->line('sky_auth_message_user_banned'));
            return false;
        }

        // doesn't support yet.
//        if($this->is_ip_address_banned($credentials['ip_address'])){
//            $this->set_error($this->lang->line('sky_auth_message_ip_banned'));
//            return false;
//        }





        // user credentials verified, write session.
        $session_data = array(
            'user_id' => $this->user->id,
            'user_name' => $this->user->name
        );

        $this->create_session($session_data);

        if($remember) {
            $token = $this->generate_token();
            $this->sky_auth_model->remember_me($this->user->uuid, $token);
        }

        $this->sky_auth_model->clear_attempts($this->user->email);
        return true;

    }

    public function is_user_banned($email)
    {
        return $this->sky_auth_model->banned_check($email);
    }

    public function is_ip_address_banned($ip_address)
    {
        return $this->sky_auth_model->banned_check($ip_address);
    }

    public function email_check($email)
    {
        return $this->sky_auth_model->email_check($email);
    }

    public function forgot_password($email)
    {
        $user = $this->sky_auth_model->findByEmail($email);

        if($user == false){
            $this->set_error($this->lang->line('sky_auth_email_not_registered'));
            return false;
        }

        if($user->is_banned == '1'){
            $this->set_error($this->lang->line('sky_auth_message_user_banned'));
            return false;
        }

        $data = array(
            'reset_password_at' => date('Y-m-d G:i:s'),
            'reset_password_token' => $this->generate_token()
        );

        $this->sky_auth_model->forgot_password($user->id,$data);

        $info = array(
            'username' => $user->name,
            'user_id' => $user->uuid,
            'site_name' => $this->config->item('site_name','sky_auth')
        );
        $data =  array_merge($data,$info);

        if($this->send_email($this->lang->line('reset_password'),'reset_password',$email,$data)){
            return true;
        }
        return false;


    }


    public function max_login($email)
    {
        return $this->sky_auth_model->get_attempts($email) >= 3;
    }

    /**
     * @param $data
     * @return bool
     */
    public function send_activate_email($data)
    {
        $subject = sprintf($this->lang->line('sky_auth_subject_activate'),$this->config->item('website_name','sky_auth'));
        // send_mail('subject','view','to','data');
        if($this->send_email($subject,'activate_user',$data['user_email'],$data)){
            $this->session->set_flashdata('alert', 'The activation link has been sent.');
            return true;
        }
    }

    public function send_email($subject, $view, $to, $data)
    {
        $email_info=array(
            'website_name' => $this->config->item('website_name','sky_auth'),
            'webmaster_email' => $this->config->item('webmaster_email','sky_auth'),
        );
        $this->load->library('email');
        $this->email->from($email_info['webmaster_email'],$email_info['website_name']);
        $this->email->reply_to($email_info['webmaster_email'],$email_info['website_name']);
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($this->load->view('auth/email/'.$view.'_html',$data,true));
        $this->email->set_alt_message($this->load->view('auth/email/'.$view.'_txt', $data, true));
        return $this->email->send();
    }

    public function error()
    {
        return $this->errors;
    }

    public function set_error($errors)
    {
        $this->errors=$errors;
    }

    public function generate_token($length = 50)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    public function create_session($data)
    {
        $this->session->sess_create();
        $this->session->set_userdata($data);
    }
}