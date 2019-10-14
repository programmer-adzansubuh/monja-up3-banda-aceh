<?php


class User_model extends CI_Model
{
    public function getUser()
    {
        return $this->db->get_where('users', array(
            'user_email' => $this->session->userdata('user_email')
        ))->row_array();
    }

    public function getUserByEmail($email)
    {
        return $this->db->get_where('users', array(
            'user_email' => $email
        ))->row_array();
    }
}