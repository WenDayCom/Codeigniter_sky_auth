<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Use this function in a form, it can output  csrf token.
 */

if ( ! function_exists('csrf_token')) {
    function csrf_token()
    {
        $CI = &get_instance();
        $hidden[$CI->security->get_csrf_token_name()] = $CI->security->get_csrf_hash();
        $form_token = sprintf("<div style=\"display:none\">%s</div>", form_hidden($hidden));
        echo $form_token;
    }
}

