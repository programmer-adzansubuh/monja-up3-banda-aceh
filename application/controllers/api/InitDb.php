<?php
require APPPATH . 'libraries/Rest_lib.php';
require APPPATH . 'libraries/Json2Sql.php';

class InitDb extends Rest_lib
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get($id = 0)
    {
        $records = $this->db->get('users');

        $filename = 'test.sqlite';

        header('Content-type: application/sqlite');
        header('Content-Disposition: attachment; filename=' . $filename);
        header("Content-Transfer-Encoding: UTF-8");

        $file = fopen('php://output', 'a');
        if ($records->num_rows() > 0) {
            foreach ($records->result_array() as $key => $row) {
                if ($key == 0) fputcsv($file, array_keys((array)$row)); // write column headings, added extra brace
                foreach ($row as $line) {
                    $line = (array)$line;
                    fputcsv($file, $line);
                }
            }
        }

        fclose($file);
    }

    public function index_post()
    {
        $input = $this->request->body;
        $user_sid = $input['user_sid'];
        $assets_path = 'assets\\db\\';
        $db_name = $user_sid . '_' . $this->helper->getTimesNow('Ymdhms') . '_monja_sqlite.db';
        $db_path = FCPATH . $assets_path . $db_name;

        if ($user_sid) {
            $test = new JSON2SQL($db_path, 'users');
            $test->debugMode(false);
            // Unit 1
            $test->dropTable()->createTable('[
                  {"user_sid" : "varchar(128)"},
                  {"user_uid" : "varchar(128)"},
                  {"user_first_name" : "varchar(128)"},
                  {"user_last_name" : "varchar(128)"},
                  {"user_position" : "varchar(128)"},
                  {"user_region" : "varchar(128)"},
                  {"user_type" : "integer(64)"},
                  {"user_password" : "varchar(128)"},
                  {"user_status" : "boolean"},
                  {"date_created" : "datetime"},
                  {"date_modified" : "datetime"}
                ]'
            );
            $data = json_encode(
                $this->db->get('users', array('user_sid', $user_sid))->result_array()
            );
            $test->add($data);

            // table test
            $test = new JSON2SQL($db_path, 'test');
            $test->dropTable()->createTable('[
                  {"id" : "integer(128)"},
                  {"no_akun" : "integer(128)"},
                  {"barang_id" : "integer(128)"}
                ]'
            );

            $response['db_name'] = $db_name;
            $response['db_version'] = '1.0';
            $response['db_path'] = $db_path;
            $response['db_url'] = base_url() . 'assets/db/' . $db_name;
            $this->response(true,'Successfully fetched!', $response,  Rest_lib::HTTP_OK);
        } else {
            $this->response(false,'No data found!', null,  Rest_lib::HTTP_OK);
        }
    }
}
