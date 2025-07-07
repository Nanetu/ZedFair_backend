<?php

class VendorController extends Controller {
    private $userModel;
    private $vendorModel;
    private $locationModel;
    private $boothModel;
    private $categoryModel;
    private $scheduleModel;
    private $eventModel;
    private $setupModel;

    public function __construct(){
        $this->userModel = $this->loadModel("User");
        $this->vendorModel = $this->loadModel("Vendor");
        $this->locationModel = $this->loadModel("Location");
        $this->boothModel = $this->loadModel("Booth");
        $this->categoryModel = $this->loadModel("Category");
        $this->scheduleModel = $this->loadModel("Schedule");
        $this->eventModel = $this->loadModel("Event");
        $this->setupModel = $this->loadModel("Vendor_Setup");
    }

    public function uploadLogo(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Upload error: ' . $_FILES['logo']['error']]);
            return;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/MVP/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['logo']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid("logo_", true) . "." . $extension;
        $targetPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $publicPath = '/MVP/uploads/' . $uniqueName;
            echo json_encode(['success' => true, 'logoUrl' => $publicPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
    }


    public function addVendor(){

        if($_SERVER['REQUEST_METHOD'] !== 'POST' ){
            http_response_code(405);
            echo json_encode(['error'=>'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? null;
        $username = $input['businessName'] ?? null;
        $password = $input['password'] ?? null;
        $category = $input['category'] ?? null;
        $role = "vendor";

        $fields = [
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'category' => $category
        ];

        foreach ($fields as $key => $value) {
            if (!$value) {
                echo json_encode([
                    'status'=>'error',
                    'message'=>$key.'field is empty.'
                ]);
                return;
            }
        }


        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->userModel->addUser($username, $email, $hash, $role);

            $user_id = $this->userModel->getUser($email);

            $this->setupModel->initVendorSetup($user_id['user_id'], $category);


            echo json_encode([
                    'status'=>'success',
                    'message'=>'User entered sucessfully.'
                ]);

            $_SESSION['category'] = $category;
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

    public function addBooth(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST' ){
            http_response_code(405);
            echo json_encode(['error'=>'Method not allowed']);
            return;
        }

        $email = $_SESSION['email'];

        $input = json_decode(file_get_contents('php://input'), true);
        
        $business_name = $input['name'] ?? null;
        $business_type = $input['type'] ?? null;
        $description = $input['desc'] ?? null;
        $logo = $input['logo'] ?? null;

        $fields = [
            'business_name' => $business_name,
            'business_type' => $business_type,
            'description' => $description,
            'logo' => $logo
        ];

        foreach ($fields as $key => $value) {
            if (!$value) {
                echo json_encode([
                    'status'=>'error',
                    'message'=>$key.'field is empty.'
                ]);
                return;
            }
        }

         try{
            $user_id = $this->userModel->getUser($email);
            $user_id = $user_id['user_id'];

            $category_id = $this->setupModel->getSetupCategory($user_id);
            $category = $this->categoryModel->getCategory($category_id);

            $existing = $this->vendorModel->getVendor($user_id);
            if ($existing && $existing['booth_number']) {
                $this->vendorModel->updateProfile($user_id, $business_name, $business_type, $description, $logo);
	            echo json_encode(['success' => true, 'message' => 'Profile updated']);
                return;
            }


            // Randomly select a location first
            $maxRow = $this->locationModel->countLocations();
            $max = (int) $maxRow['total'];
            $match = true;
            $attempts = 0;
            $maxAttempts = 1000;  // Avoid infinite loop
            while ($match && $attempts < $maxAttempts) {
                $attempts++;

                $randomNum = rand(1, $max);
                $location = $this->locationModel->getLocation($randomNum);

                if (!$location) continue; // avoid errors if getLocation returns null

                $typeMatch = ($business_type == 'Company' && $location['location_type'] == 'building') ||
                            ($business_type == 'Independent' && $location['location_type'] == 'tent');

                if ($location['is_assigned'] != 1 && $typeMatch) {
                    $match = false;
                    $this->locationModel->setAssigned($randomNum);
                }
            }

            if ($attempts >= $maxAttempts) {
                http_response_code(400);
                echo json_encode(['error' => 'No suitable location found for business type.']);
                return;
            }


            // Generate booth number
            $firstletter = substr($category['category_name'], 0, 1);
            //$boothNum;
            $equals = true;
            while($equals == true){
                $num = rand(1, 999);
                $number = str_pad($num, 3, '0', STR_PAD_LEFT);
                $boothNum = $firstletter.$number;   // Should give something like T103
                // search if booth(boothNum returns a result
                $result = $this->boothModel->getBoothById($boothNum);
                if(!$result){
                    $equals = false;
                    $this->boothModel->addBooth($boothNum, $user_id, $randomNum);
                }   
            }

            $this->vendorModel->addVendor($user_id, $business_name, $business_type, $category_id, $boothNum, $description, $logo);
            echo json_encode([
                'success' => true,
                'location' => $randomNum,
                'booth' => $boothNum
            ]);

        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }
    }

    public function myProfile(){
        try{
            $user = $_SESSION['id'];

            $profile = $this->vendorModel->getVendor($user);
            $location = $this->boothModel->getBoothById($profile['booth_number']);

            echo json_encode([
                'status'=>'success',
                'booths'=>$profile,
                'location'=>$location['location_id']
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateVendorProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $email = $_SESSION['email'];
        $input = json_decode(file_get_contents('php://input'), true);

        $business_name = $input['name'] ?? null;
        $description = $input['desc'] ?? null;
        $logo = $input['logo'] ?? null;

        if (!$business_name || !$description) {
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            $user = $this->userModel->getUser($email);
            $user_id = $user['user_id'];

            $this->vendorModel->updateProfile($user_id, $business_name, $description, $logo);
            echo json_encode([
                'success' => true]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    public function fetchEvents(){
        $this->setJsonHeaders();
        $user = $_GET['userId'];


        $vendor = $this->vendorModel->getVendor($user);

        try{
            $events = $this->eventModel->getAllEventsByVendor($vendor['vendor_id']);
            echo json_encode([
                'status'=>'success',
                'events'=>$events
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function addEvent(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST' ){
            http_response_code(405);
            echo json_encode(['error'=>'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $user = $input['userId'];
        $time_start = $input['start'];
        $time_end = $input['end'];
        $title = $input['title'];

        $vendor_id = $this->vendorModel->getVendor($user);
        $vendor_id= $vendor_id['vendor_id'];

        try{
            $this->eventModel->addEvent($vendor_id, $title, $time_start, $time_end);
            echo json_encode([
                'status'=>'success',
                'message'=>'Event added'
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delEvent(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST' ){
            http_response_code(405);
            echo json_encode(['error'=>'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $title = $input['title'];

        try{
            $this->eventModel->deleteEvent($title);
            echo json_encode([
                'status'=>'success',
                'message'=>'Event deleted'
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function fetchCategories(){
        try{
            $category = $this->categoryModel->getAllCategories();
            //$category = $category['category_name'];

            echo json_encode([
                'status'=>'success',
                'categories'=>$category
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function registeredAttendees(){
        $this->setJsonHeaders();

        $data = json_decode(file_get_contents("php://input"), true);
        $event = $data['event'] ?? null;

        try{
            $events = $this->scheduleModel->getAllUsersForEvent($event);
            echo json_encode([
                'status'=>'sucess',
                'events'=>$events
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }
}

?>