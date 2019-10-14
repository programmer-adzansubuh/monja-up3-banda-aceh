<?php
require APPPATH . 'libraries/Rest_lib.php';
class Emp extends Rest_lib {
	public function __construct() {
       parent::__construct();
       $this->load->database();
    }
    public function index_get($id = 0){
        if(!empty($id)){
            $data = $this->db->get_where("users", array('id' => $id))->row_array();
        }else{
            $data = $this->db->get("users")->result();
        }
        $this->response($data, Rest_lib::HTTP_OK);
	}
    public function index_post(){
        $input = $this->input->post();
        $this->db->insert('employee',$input);

        $this->response(['Employee created successfully.'], Rest_lib::HTTP_OK);
    }
    public function index_put($id){
        $input = $this->put();
        $this->db->update('employee', $input, array('id'=>$id));

        $this->response(['Employee updated successfully.'], Rest_lib::HTTP_OK);
    }
    public function index_delete($id){
        $this->db->delete('employee', array('id'=>$id));

        $this->response(['Employee deleted successfully.'], Rest_lib::HTTP_OK);
    }
}