<?php

function is_logged_in()
{
    $ci = get_instance();

    if (!$ci->session->userdata('email')) {
        redirect('auth');
    } else {
        $role_id = $ci->session->userdata('role_id');
        $menu = $ci->uri->segment(1);

        $queryMenu = $ci->db->get_where('user_menu', ['menu' => $menu])->row_array();
        $menu_id = $queryMenu['id'];

        $userAccess = $ci->db->get_where('user_access_menu', ['role_id' => $role_id, 'menu_id' => $menu_id]);

        if ($userAccess->num_rows() < 1) {
            redirect('auth/blocked');
        }
    }
}

function check_access($role_id, $menu_id)
{
    $ci = get_instance();

    $ci->db->where('role_id', $role_id);
    $ci->db->where('menu_id', $menu_id);
    $result = $ci->db->get('user_access_menu');

    if ($result->num_rows() > 0) {
        return "checked='checked'";
    }
    return "";
}

function uid_generator($prefix = false)
{
    if ($prefix) {
        return strtoupper($prefix . '-' . time());
    }
    return strtoupper('UID-' . time());
}

function get_times_now($format = NULL)
{
    if ($format) {
        return date($format, time());
    } else {
        return date('Y-m-d h:m:s', time());
    }
}

function verify_request()
{
    // Get all the headers
    $headers = $this->input->request_headers();

    // Extract the token
    $token = $headers['Authorization'];

    // Use try-catch
    // JWT library throws exception if the token is not valid
    try {
        // Validate the token
        // Successfull validation will return the decoded user data else returns false
        $data = AUTHORIZATION::validateToken($token);
        if ($data == null) {
            $this->response(false, 'Unauthorized Access!', $data, Rest_lib::HTTP_UNAUTHORIZED);
            exit();
        }
        else if ($data === false) {
            $this->response(false, 'Unauthorized Access!', $data, Rest_lib::HTTP_UNAUTHORIZED);
            exit();
        }
        else {
            return $data;
        }
    } catch (Exception $e) {
        // Token is invalid
        // Send the unathorized access message
        header("Content-type:application/json");
        header(http_response_code(401));
        echo '{"status":false, "message":"Unauthorized Access!"}';
        exit();
    }
}
