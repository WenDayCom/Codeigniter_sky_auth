<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('form_validation','security','sky_auth'));
        $this->lang->load('sky_auth');
        $this->config->load('sky_auth', TRUE);
    }

    public function register()
    {

        if($this->config->item('allow_registration','sky_auth') === false){
            $data['message'] = $this->lang->line('sky_auth_message_registration_disabled');
            $this->my_view('auth/message',$data);
            return false;
        }

        if($this->form_validation->run('register') === false){
            $this->my_view('auth/register');
            return false;
        }

        $user = array(
            'name' => $this->input->post('inputName'),
            'email' => $this->input->post('inputEmail'),
            'password' => $this->input->post('inputPassword'),
        );

        $this->sky_auth->register($user);

        if($this->sky_auth->error() != ''){
            $data['_message'] = $this->sky_auth->error();
            $this->show_message('auth/register',$data);
            return false;
        }

        // Registration Complete without errors.
        if($this->config->item('email_activation','sky_auth') === true){
            $message = $this->lang->line('sky_auth_message_registration_completed_1');
        } else {
            $message = $this->lang->line('sky_auth_message_registration_completed_2');
        }

        $this->session->set_flashdata('alert',$message);
        redirect('/');
    }


    public function login()
    {
        if($this->sky_auth->check()){
            redirect('/');
        }

        if($this->sky_auth->max_login($this->input->post('inputEmail'))){
            $data['_message'] = "失败的次数太多, 24小时内无法再登录此帐号.";
            $this->show_message('auth/login',$data);
            return false;
        }

        if ($this->form_validation->run('login') == FALSE) {
            $this->my_view('auth/login');
            return false;
            // To check credentials against database.
        }

        $credentials = array(
            'email' => $this->input->post('inputEmail'),
            'password' => $this->input->post('inputPassword'),
            'ip_address' => $this->input->ip_address()
        );

        $remember = (bool) $this->input->post('rememberMe');

        $this->sky_auth->login($credentials, $remember);

        if($this->sky_auth->error() != false){
            $data['_message'] = $this->sky_auth->error();
            $this->show_message('auth/login',$data);
            return false;
        }

        // logged in successfully
        $message = $this->lang->line('sky_auth_message_logged_in');
        $this->session->set_flashdata('alert', $message);
        redirect('/');

    }
    
    public function logout()
    {
        if(! $this->sky_auth->check()){
            redirect('/');
            return false;
        }

        $this->sky_auth->logout();
        $message = $this->lang->line('sky_auth_message_logged_out');
        $this->session->set_flashdata('alert',$message);
        redirect('index');
    }

    
    public function forgot_password()
    {
        if($this->sky_auth->check()){
            redirect('index');
        }

        if($this->form_validation->run('email') == false){
            $this->my_view('auth/forgot_password');
            return false;
        }

        $this->sky_auth->forgot_password($this->input->post('inputEmail'));

        if($this->sky_auth->error() != false){
            $data['_message'] = $this->sky_auth->error();
        } else {
            $data['_message'] = $this->lang->line('sky_auth_message_reset_password_sent');
        }

        $this->show_message('auth/forgot_password', $data['_message']);
    }

    public function change_password()
    {
        $data = array(
            'uuid' => $this->uri->segment(2),
        );

        if($this->form_validation->run('password') == false){
            $this->my_view('auth/change_password', $data);
            return false;
        }

        $currentPassword = $this->input->post('currentPassword');
        $newPassword = $this->input->post('inputPassword');

        if($this->sky_auth->check() == false){
            redirect('auth/login');
            return false;
        }

        if($this->sky_auth->user()->uuid !== $data['uuid']){
            redirect('/');
            return false;
        }

        $this->sky_auth->change_password($data['uuid'], $currentPassword, $newPassword);

        if($this->sky_auth->error() != false){
            $_message = $this->sky_auth->error();
            $this->show_message('auth/change_password', $_message, $data);
            return false;
        }

        $message = $this->lang->line('sky_auth_message_new_password_activated');
        $this->session->set_flashdata('alert', $message);
        redirect('auth/login');
    }

    public function reset_password($uuid, $token)
    {
        $data = array(
            'uuid' => $this->uri->segment(2),
            'token' => $this->uri->segment(3),
        );

        if($this->form_validation->run('password') == false){
            $this->my_view('auth/reset_password', $data);
            return false;
        }

        $password = $this->input->post('inputPassword');
        $this->sky_auth->reset_password($data['uuid'], $data['token'], $password);

        if($this->sky_auth->error() != false){
            $_message = $this->sky_auth->error();
            $this->show_message('auth/reset_password', $_message, $data);
            return false;
        }

        $message = $this->lang->line('sky_auth_message_new_password_activated');
        $this->session->set_flashdata('alert', $message);
        redirect('auth/login');

    }

    public function activate_user($uuid, $token)
    {
        //to do
    }

    public function email_check($inputEmail)
    {
        if ($this->sky_auth->email_check($inputEmail) == false){
            $error = $this->lang->line('sky_auth_email_in_use');
            $this->form_validation->set_message('email_check', $error);
            return FALSE;
        }
        return TRUE;
    }

    public function show_message($view,$message, $data=null)
    {
        $data['_message'] = $message;
        $this->my_view($view,$data);
    }


}