<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth
{
	public $CI_OBJECT;
	public $HEADER_TOKEN;
	public $VERIFICATION_RESPONSE;
    /**
     * @var false
     */
    private bool $LOGGED_IN;

    public function __construct()
	{
		$this->CI_OBJECT =& get_instance();
		$this->HEADER_TOKEN = "";
		$this->LOGGED_IN = false;
	}

	public function doAuthentication($type = "AUTHENTICATION")
	{
		$this->HEADER_TOKEN = $this->CI_OBJECT->input->request_headers()['HTTP_AUTHORIZATION'] ?? "";

        if (!empty($this->HEADER_TOKEN)) {

            // Check if the header starts with "Bearer"
            if (preg_match('/Bearer\s(\S+)/', $this->HEADER_TOKEN, $matches)) {

                $this->HEADER_TOKEN = $matches[1]; // Extract the token value

                $this->CI_OBJECT->db->where('TOKEN', $this->HEADER_TOKEN);
                $loginRecord = $this->CI_OBJECT->db->get('user_login');

                if ($loginRecord->num_rows() > 0) {
                    $this->LOGGED_IN = true;
                }

            }else{
                return array(
                    'http_response_code' => 401,
                    'status' => 0,
                    'message' => 'Invalid Bearer Token'
                );
            }

        }else{
            return array(
                'http_response_code' => 401,
                'status' => 0,
                'message' => 'Missing authorization token'
            );
        }


		if ($this->LOGGED_IN) {

			switch($type){

				case "AUTHORIZATION" :

					$this->CI_OBJECT->db->where('id', $loginRecord->row()->user_id);
					$this->CI_OBJECT->db->where('status', 1);
					if($this->CI_OBJECT->db->get('user')->num_rows() > 0){

						return array(
							'http_response_code' => 200,
							'status' => 1,
							'message' => 'Authenticated user token',
							'user_id' => $loginRecord->row()->user_id
						);

					}

					return array(
						'http_response_code' => 403,
						'status' => 0,
						'message' => 'Unauthorized user'
					);

					break;

				default :

					return array(
						'http_response_code' => 200,
						'status' => 1,
						'message' => 'Authenticated user token',
						'user_id' => $loginRecord->row()->user_id,
						'dateTime' => $loginRecord->row()->dateTime
					);
			}


		}

		return array(
			'http_response_code' => 401,
			'status' => 0,
			'message' => 'Unauthenticated user token'
		);

	}


	public function getUserData($value, $sourceType = "id")
	{
		if ($sourceType === "id") {
			$this->CI_OBJECT->db->where('id', $value);
		} else {
			$this->CI_OBJECT->db->where($sourceType, $value);
		}

		return $this->CI_OBJECT->db->get('user')->result()[0];

	}


	//Start Sign-up process
	public function processSignup($input)
	{
        //validate if user already exists
        $this->CI_OBJECT->db->where('username', filter($input['username']));
        $this->CI_OBJECT->db->where('status', 1);
        if(!($this->CI_OBJECT->db->get('user')->num_rows() > 0)) {

            //begin processing of user registration
            $userData = array(
                'name' => filter($input['name']),
                'username' => filter($input['username']),
                'password' => password_hash(filter($input['password']), PASSWORD_BCRYPT)
            );

            if ($this->CI_OBJECT->db->insert('user', $userData)) {

                return [
                    'http_response_code' => 200, 'status' => 1,
                    'message' => 'User registered successfully'
                ];

            }
        }else{

            return [
                'http_response_code' => 409, 'status' => 0,
                'message' => 'Username Taken'
            ];

        }

		return [
			'http_response_code' => 500, 'status' => 0,
			'message' => 'An error occurred while processing you request'
		];
	}
	//End Sign-up Process

	//Start checking username and password
	public function verifyUser($username, $password)
	{
		$this->CI_OBJECT->db->where('username', $username);

		if ($this->CI_OBJECT->db->get('user')->num_rows() > 0) {

			$userData = $this->getUserData($username, 'username');

			$password_hash = $userData->password;

			if (password_verify($password, $password_hash)) {

				$this->VERIFICATION_RESPONSE = array(
					'http_response_code' => 200,
					'status' => 1,
					'user_id' => (int)$userData->id,
					'message' => 'username & password is valid',
					'user_status' => (int)$userData->status
				);

			} else {

				$this->VERIFICATION_RESPONSE = array(
					'http_response_code' => 400,
					'status' => 0,
					'message' => 'The password is invalid'
				);

			}

		} else {

			$this->VERIFICATION_RESPONSE = array(
				'http_response_code' => 401,
				'status' => 0,
				'message' => 'The user is invalid'
			);

		}

		return false;
	}

	//End checking username and password

	public function processSignIn($request = "authenticateUser", array $data = [])
	{
		switch ($request) {

			case "authenticateUser":
				$username = filter($data['username']);
				$password = filter($data['password']);

				$this->verifyUser($username, $password);

				return $this->VERIFICATION_RESPONSE;

			case "login":

				$userID = $data['user_id'];
				$token    = random_string('alnum', 128);
				$loggedInTime = date("Y-m-d H:i:s");

				$loginData = array(
					'user_id' => $userID,
					'token'	=> $token,
					'dateTime' => $loggedInTime
				);

                if ($this->CI_OBJECT->db->insert('user_login', $loginData)) {

                    return array(
                        'http_response_code' => 200,
                        'status' => 1,
                        'token'  => $token,
                        'message' => 'Login successful'
                    );
                }

				return array(
					'http_response_code' => 500,
					'status' => 0,
					'message' => 'An error occurred while processing you request'
				);

			default:

				return [
					'http_response_code' => 400,
					'status' => 0,
					'message' => 'No request found'
				];
		}

	}

	public function processSignOut($USER_ID){


		$this->CI_OBJECT->db->where('user_id', $USER_ID);
		if ($this->CI_OBJECT->db->delete('user_login')) {

			return array(
				'http_response_code' => 200,
				'status' => 1,
				'message' => 'User signed out successfully'
			);

		}

		return false;
	}

}
