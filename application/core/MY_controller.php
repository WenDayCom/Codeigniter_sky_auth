<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    protected $header='header_default';
    protected $footer='footer_default';

    protected $nav_data=array();

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('sky_auth'));
        if($this->sky_auth->check() || $this->sky_auth->try_auto_login()){
            $this->nav_data['user_name']=$this->sky_auth->user()->name;
            $this->nav_data['change_password']=$this->sky_auth->user()->uuid;
            return true;
        }

    }

    public function my_view($page, $data=null,$return = false)
    {
        $this->load->view($this->header);
        $this->load->view('partials/nav',$this->nav_data);
        $this->load->view('flashdata');
        $this->load->view($page,$data,$return);
        $this->load->view($this->footer);
    }

}