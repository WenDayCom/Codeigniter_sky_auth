<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sky_auth_model extends CI_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('security'));
    }

    public function findByEmail($email)
    {
        $this->db->select('id, uuid, name, email, is_banned');
        $this->db->where('email', $email);
        $query = $this->db->get('sky_auth_users');

        if($query->num_rows() == 0){
            return false;
        }

        return $query->row();
    }

    public function findByUuid($uuid)
    {
        $this->db->select('id, uuid, name, email, is_banned,reset_password_token');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('sky_auth_users');

        if($query->num_rows() == 0){
            return false;
        }

        return $query->row();
    }

    public function verifyPassword($uuid, $password)
    {
        $this->db->select('password');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('sky_auth_users');

        if($query->num_rows() ==0 ){
            return false;
        }

        $user = $query->row();

        if(password_verify($password,$user->password)){
            return true;
        }
        return false;
    }

    public function forgot_password($id, $data)
    {
        $data['reset_password_token'] = hash('sha256', $data['reset_password_token']);

        $this->db->where('id',$id);
        $this->db->update('sky_auth_users',$data);
        if($this->db->affected_rows() > 0){
            return true;
        }
        return false;
    }

    public function reset_password($uuid, $password)
    {
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $data['reset_password_token'] = null;
        $this->db->where('uuid', $uuid);
        $this->db->update('sky_auth_users', $data);

    }

    public function register($user)
    {
        if($this->config->item('email_activation','sky_auth') === false){
            $user['is_active'] = '1';
        }

        $user['uuid'] = randomUuid();

        $this->db->insert('sky_auth_users',$user);
        return $this->db->insert_id();
    }

    public function login($email, $password)
    {
        $this->db->select('id,uuid,email,name,password,is_banned');
        $this->db->where('email', $email);
        $query = $this->db->get('sky_auth_users');

        if($query->num_rows() != 1) return false;

        $user = $query->row();

        if(! password_verify($password,$user->password)) return false;

        return $user;

    }

    public function remember_me($uuid, $token)
    {
        $data['remember_token'] = hash('sha256',$token);
        $this->db->where('uuid',$uuid);
        $this->db->update('sky_auth_users',$data);
        setcookie('rm',$uuid.$token,time()+3600*24*7,'/');
    }

    public function autologin($uuid)
    {

        $this->db->select('id, uuid, name, email, is_banned,remember_token');
        $this->db->where('uuid',$uuid);
        $query = $this->db->get('sky_auth_users');

        if($query->num_rows() !== 1){

            return false;
        }

        $user = $query->row();
        return $user;

    }

    public function get_attempts($email)
    {
        $this->db->select('1', FALSE);
        $this->db->where('email', $email);
        $query = $this->db->get('sky_auth_throttles');

       return $query->num_rows();

    }


    public function increase_attempts($email, $ip_address)
    {
        $attemps['email'] = $email;
        $attemps['ip_address'] = $ip_address;
        $this->db->insert('sky_auth_throttles', $attemps);

    }

    public function clear_attempts($email)
    {
        $this->db->where(array('email' => $email));

        $this->db->or_where('UNIX_TIMESTAMP(create_at) <', time() - 86400);

        $this->db->delete('sky_auth_throttles');

    }

    public function email_check($email)
    {
        $this->db->select('1', FALSE);
        $this->db->where('email', $email);
        $query = $this->db->get('sky_auth_users');

        if ($query->num_rows() == 1) {
            return false;
        }

        return true;
    }

    public function banned_check($flag)
    {
        if(filter_var($flag, FILTER_VALIDATE_EMAIL)) {
            $this->db->select('is_banned');
            $this->db->where('email',$flag);
            $query = $this->db->get('sky_auth_users');
            $user = $query->row();
            if($user->is_banned === '1') return true;
        }

        // We can add ip address check here
        // e.g. if this->db->is_ip_address_banned($flag)->num_rows() == 1; return true;

        return false;
    }

    public function updatePassword($uuid, $password)
    {
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);

        $this->db->where('uuid',$uuid);
        $this->db->update('sky_auth_users',$data);
        if($this->db->affected_rows() > 0){
            return true;
        }
        return false;
    }

    public function getUserById($id)
    {
        $query = $this->db->select('id,uuid,name,email')
            ->where('id',$id)
            ->get('sky_auth_users');

        if($query->num_rows() === 1){
            return $query->row();
        }
    }
}