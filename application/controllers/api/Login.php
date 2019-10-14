<?php
require APPPATH . 'libraries/Rest_lib.php';

class Login extends Rest_lib
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('app', 'jwt', 'authorization'));
        $this->load->model('User_model', 'user');
        $this->load->library('user_agent', 'client');
    }

    public function index_post()
    {
        $input = $this->request->body;
        $email = $input['user_email'];
        $password = $input['user_password'];
        $user = $this->user->getUserByEmail($email);

        if ($user['is_active'] == 1) {
            if (password_verify($password, $user['user_password'])) {
                $this->db->where('user_sid', $user['user_sid']);
                $delete = $this->db->delete('user_tokens');
                if ($delete) {
                    $token = $this->createToken(time());
                    $createToken = $this->db->insert('user_tokens', array(
                        'token_sid' => $this->uuid->sid(),
                        'user_sid' => $user['user_sid'],
                        'auth_token' => $token,
                        'date_created' => get_times_now(),
                        'ip_address' => $this->input->ip_address(),
                        'device_platform' => $this->agent->platform(),
                    ));
                    if ($createToken) {
                        $status = true;
                        $user['token_auth'] = $token;
                        $message = "Login successfully!";
                    } else {
                        $status = false;
                        $user = array(null);
                        $message = "Login failed, please try again!";
                    }
                } else {
                    $status = false;
                    $user = array(null);
                    $message = "Login failed, please try again!";
                }
            } else {
                $status = false;
                $user = array(null);
                $message = "Login failed, your password is not correct!";
            }
        } else {
            $status = false;
            $user = array(null);
            $message = "Login failed, your account has not activated!";
        }

        $this->response($status, $message, $user, Rest_lib::HTTP_OK);

    }

    public function createToken($param)
    {
        return AUTHORIZATION::generateToken($param);
    }

    public function get_me_data_post()
    {
        // Call the verification method and store the return value in the variable
        $this->verify_request();
        $response = array('user' => '');
        $this->response(true, 'Successfully verification all data!', $response, Rest_lib::HTTP_OK);

        // Send the return data as reponse

    }

    public function verify_request()
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
}

