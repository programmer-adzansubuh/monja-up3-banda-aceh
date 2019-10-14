<?php
require APPPATH . 'libraries/Rest_lib.php';

class User extends Rest_lib
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('app', 'jwt', 'authorization'));
    }

    public function index_get($id = 0)
    {
        if (!empty($id)) {
            $data = $this->db->get_where("users", array('user_sid' => $id))->row_array();
        } else {
            $data = $this->db->get("users")->result();
        }
        if ($data) {
            $this->response(true, 'Successfully fetched!', $data, Rest_lib::HTTP_OK);
        } else {
            $this->response(true, 'No data found!', null, Rest_lib::HTTP_OK);
        }
    }

    public function index_post()
    {
        $input = $this->request->body;
        $input['user_sid'] = $this->uuid->sid();
        $input['user_uid'] = uid_generator('MNJ');
        $input['date_created'] = get_times_now();
        $password = $input['user_password'];
        $input['user_password'] = password_hash($password, PASSWORD_DEFAULT);
        $this->db->insert('users', $input);
        $this->response(true, 'Successfully inserted!', null, Rest_lib::HTTP_OK);
    }

    public function index_put($id)
    {
        $input = $this->put();
        $this->db->update('users', $input, array('user_sid' => $id));
        $this->response(true, 'Successfully updated!', null, Rest_lib::HTTP_OK);
    }

    public function index_delete($id)
    {
        if ($id !== 'delete_all') {
            $this->db->delete('users', array('user_sid' => $id));
            $this->response(true, 'Successfully deleted!', null, Rest_lib::HTTP_OK);
        } else {
            $this->db->query('DELETE FROM users');
            $this->response(true, 'Successfully deleted all data!', null, Rest_lib::HTTP_OK);
        }
    }

    public function hello_get()
    {
        // Create a token
        $token = AUTHORIZATION::generateToken('Haerul');
        // Set HTTP status code
        $status = parent::HTTP_OK;
        // Prepare the response
        $response = array('status' => $status, 'token' => $token);
        // REST_Controller provide this method to send responses
        $this->response(true, 'Successfully verification all data!', $response, Rest_lib::HTTP_OK);
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

