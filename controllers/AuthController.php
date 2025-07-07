<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct(){
        $this->userModel = $this->loadModel("User");
    }

    public function signup(){

        header('Content-Type: application/json');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode([
                'status'=> 'failure',
                'message'=>'Method not allowed'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? null;
        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;
        $role = "explorer";

        $fields = [
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        foreach ($fields as $key => $value) {
            if (!$value) {
                echo json_encode([
                    'status'=>'error',
                    'message'=>$key.' field is empty.'
                ]);
                return;
            }
        }


        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $this->userModel->addUser($username, $email, $hash, $role);
            
            echo json_encode([
                    'status'=>'success',
                    'message'=>'User entered sucessfully.'
            ]);
        } catch(Exception $e){
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                echo json_encode([
                    'status' => 'error',
                    'error' => 'Duplicate entry: email already exists.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]);
            }
        } 
    }

    public function login(){

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405);
            echo json_encode(['error'=>'Method not allowed']);
            return;
        }

        $max_attempts = 3;
        $lockout_time = 900;
        $ip = $_SERVER['REMOTE_ADDR'];
        $current_time = time();

        if(!isset($_SESSION['login_attempts'])){
            $_SESSION['login_attempts'] = [];
        }

        $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($attempt) use ($current_time, $lockout_time){
            return ($current_time - $attempt['time']) < $lockout_time;
        });

        $ip_attempts = array_filter($_SESSION['login_attempts'], function($attempt) use($ip){
            return $attempt['ip'] === $ip;
        });

        if (count($ip_attempts) >= $max_attempts){
            $oldest_attempt = min(array_column($ip_attempts, 'time'));
            $time_remaining = $lockout_time - ($current_time-$oldest_attempt);

            http_response_code(429);
            echo json_encode([
                'error'=>'Too many login attempts. Try again in '.ceil($time_remaining/60).' minutes.'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if(!$email || !$password){
            http_response_code(400);
            echo json_encode(['error'=>'Cannot validate empty data']);
            return;
        }

        try {
            $user = $this->userModel->getUser($email);

            if(!$user){
                $_SESSION['login_attempts'][] = [
                    'ip' => $ip,
                    'time' => $current_time,
                    'email' => $email
                ];
                http_response_code(401);
                echo json_encode(['error'=>'Invalid credentials']);
                return;
            }

            if(password_verify($password, $user['password'])){
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['id'] = $user['user_id'];

                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['user_id'],
                        'email' => $email,
                        'name' => $user['username'],
                        'role' => $user['role']
                    ]
                ]);

                $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($attempt) use ($ip) {
                    return $attempt['ip'] !== $ip;
                });
            } else{
                http_response_code(401);
                echo json_encode(['error'=>'Invalid Credentials']);
                return;
            }

        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error'=> $e->getMessage()]);
        }
    }

    public function logout(){
        try {
            session_unset();
            session_destroy();
            echo json_encode(['status'=>'success']);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error'=> $e->getMessage()]);
        }
    }
}

?>