<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Task extends CI_Controller
{
	private array $AUTH_RESPONSE;
	private int $AUTH_USER;
	private string $REQUEST_METHOD;

	public function __construct()
	{
		parent::__construct();

		$this->load->library('auth');
		$this->REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];

		$this->AUTH_RESPONSE = $this->auth->doAuthentication("AUTHORIZATION");

		if((int)$this->AUTH_RESPONSE['status'] !== 0) {

			$this->AUTH_USER = $this->AUTH_RESPONSE['user_id'];

		}else{
            $response = $this->AUTH_RESPONSE;
            $this->core->outputResponse($response, $response['http_response_code']);
        }

	}

    public function index()
    {
        $this->task();
    }

	public function task()
	{

		switch($this->REQUEST_METHOD){
			case "GET":

                $response = $this->fetch();

				break;

            case "POST":

                $response = $this->add();

                break;

			case "PUT":

                $response = $this->update();

				break;

            case "DELETE":

                $response = $this->delete();

                break;
			default :

				$response = [
					'http_response_code' => 400, 'status' => 0,
					'message' => 'Invalid request method'
				];

		}

		$this->core->outputResponse($response, $response['http_response_code']);
	}

	private function fetch()
	{
		$response = ['http_response_code' => 200, 'status' => 0, 'message' => 'Data not found'];

		$this->db->where('user_id', $this->AUTH_USER);
		if($this->db->get('task')->num_rows() > 0){

			$this->db->where('user_id', $this->AUTH_USER);

			$response = [
				'http_response_code' => 200, 'status' => 1,
				'message' => 'Tasks fetched successfully',
				'userData' => $this->db->get('task')->result()
			];
		}

		return $response;
	}

    private function add()
    {
        $response = ['http_response_code' => 500, 'status' => 0, 'message' => 'Error occurred while requesting'];

        $input_data = json_decode(file_get_contents("php://input"), true);

        $this->form_validation->set_data($input_data);
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() === FALSE) {

            $response = ['http_response_code' => 403 , 'status' => 0, 'message' => strip_tags(validation_errors())];

        } else {

            $data = [
                'user_id' => $this->AUTH_USER,
                'title' => filter($input_data['title']),
                'description' => filter($input_data['description'] ?? ""),
                'status' => filter($input_data['status'])
            ];

            if($this->db->insert('task', $data)){

                $response = [
                    'http_response_code' => 200, 'status' => 1,
                    'message' => 'Task added successfully'
                ];

            }

        }

        return $response;
    }

	private function update()
	{
		$response = ['http_response_code' => 500, 'status' => 0, 'message' => 'Error occurred while requesting'];

		$input_data = json_decode(file_get_contents("php://input"), true);

		$this->form_validation->set_data($input_data);
        $this->form_validation->set_rules('id', 'ID', 'required');

		if ($this->form_validation->run() === FALSE) {

			$response = ['http_response_code' => 403 , 'status' => 0, 'message' => strip_tags(validation_errors())];

		} else {

            $data = [
                'title' => filter($input_data['title'] ?? ""),
                'description' => filter($input_data['description'] ?? ""),
                'status' => filter($input_data['status'] ?? "")
            ];

            if($this->db->update('task', $data, ['user_id' => $this->AUTH_USER, 'id' => filter($input_data['id'])])){

                $response = [
                    'http_response_code' => 200, 'status' => 1,
                    'message' => 'Task updated successfully'
                ];

            }

		}

		return $response;
	}

	private function delete()
	{
        $response = ['http_response_code' => 500, 'status' => 0, 'message' => 'Error occurred while requesting'];

        $input_data = json_decode(file_get_contents("php://input"), true);

        $this->form_validation->set_data($input_data);
        $this->form_validation->set_rules('id', 'ID', 'required');

        if ($this->form_validation->run() === FALSE) {

            $response = ['http_response_code' => 403 , 'status' => 0, 'message' => strip_tags(validation_errors())];

        } else {

            if($this->db->delete('task', ['user_id' => $this->AUTH_USER, 'id' => filter($input_data['id'])])){

                $response = [
                    'http_response_code' => 200, 'status' => 1,
                    'message' => 'Task deleted successfully'
                ];

            }

        }

        return $response;
	}

}
