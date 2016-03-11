<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$config = array(
    'register' => array(
        array(
            'field' => 'inputName',
            'label' => 'lang:name',
            'rules' => 'required|min_length[4]|max_lengh[12]'
        ),
        array(
            'field' => 'inputEmail',
            'label' => 'lang:email',
            'rules' => 'required|valid_email|is_unique[sky_auth_users.email]'
//            'rules' => 'required|valid_email|callback_email_check'
        ),
        array(
            'field' => 'inputPassword',
            'label' => 'lang:password',
            'rules' => 'required|min_length[8]|max_length[50]'
        ),
        array(
            'field' => 'inputPassword2',
            'label' => 'lang:password2',
            'rules' => 'required|min_length[8]|max_length[50]|matches[inputPassword]'
        )
    ),
    'login' => array(
        array(
            'field' => 'inputEmail',
            'label' => 'lang:email',
            'rules' => 'required|valid_email'
        ),
        array(
            'field' => 'inputPassword',
            'label' => 'lang:password',
            'rules' => 'required|min_length[8]|max_length[50]'
        ),
    ),
    'email' => array(
        array(
            'field' => 'inputEmail',
            'label' => 'lang:email',
            'rules' => 'required|valid_email'
        ),
    ),
    'password' => array(
        array(
            'field' => 'inputPassword',
            'label' => 'lang:password',
            'rules' => 'required|min_length[8]|max_length[50]'
        ),
        array(
            'field' => 'inputPassword2',
            'label' => 'lang:password2',
            'rules' => 'required|min_length[8]|max_length[50]|matches[inputPassword]'
        )
    )
);