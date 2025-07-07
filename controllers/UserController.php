<?php

class UserController extends Controller {
    private $userModel;
    private $vendorModel;
    private $boothModel;
    private $productModel;
    private $eventModel;
    private $categoryModel;
    private $scheduleModel;
    private $favouriteModel;

    public function __construct(){
        $this->userModel = $this->loadModel("User");
        $this->vendorModel = $this->loadModel("Vendor");
        $this->boothModel = $this->loadModel("Booth");
        $this->productModel = $this->loadModel("Product");
        $this->eventModel = $this->loadModel("Event");
        $this->categoryModel = $this->loadModel("Category");
        $this->scheduleModel = $this->loadModel("Schedule");
        $this->favouriteModel = $this->loadModel("Favourite");
    }

    public function generalSearch(){
        $this->setJsonHeaders();

        $data = json_decode(file_get_contents("php://input"), true);
        $entry = $data['entry'] ?? null;

        if(!$entry){
            echo json_encode([
                'status'=>'error',
                'message'=>'Cannot enter an empty query'
            ]);
            return;
        }
        $entry = trim($entry, ' ');
        try{
            $products = $this->productModel->getAllProducts($entry);
            $exhibitions = $this->vendorModel->getAllVendorsByEntry($entry);

            echo json_encode([
                'status'=>'sucess',
                'products'=>$products
            ]);
            echo json_encode([
                'status'=>'sucess',
                'exhibitions'=>$exhibitions
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function fetchAllUsers(){
        $this->setJsonHeaders();

        try{
            $vendors = $this->userModel->getAllVendors();
            $users = $this->userModel->getAllExhibitors();
            echo json_encode([
                'status'=>'sucess',
                'exhibitors'=>$users['total'],
                'vendors'=>$vendors['total']
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function upcomingEvents(){
        $this->setJsonHeaders();

        try{
            $events = $this->eventModel->upComing();
            echo json_encode([
                'status'=>'success',
                'events'=>$events
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function featuredHighlights(){
        $this->setJsonHeaders();

        try{
            $highlights = $this->productModel->getRandomProducts();
            echo json_encode([
                'status'=>'sucess',
                'highlights'=>$highlights
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function exploreBooths(){
        $this->setJsonHeaders();

        try{
            $booths = $this->boothModel->getAllBooths();

            $results = [];

            foreach ($booths as $booth) {
                $user_id = $booth['user_id'];

                $vendor = $this->vendorModel->getVendor($user_id);
                if (!$vendor) continue;

                $id = $vendor['vendor_id'] ?? '';
                $title = $vendor['business_name'] ?? '';
                $logo = $vendor['logo'] ?? '';
                $category_id = $vendor['category_id'] ?? null;
                $category = $this->categoryModel->getCategory($category_id);
                $categoryName = $category['category_name'] ?? 'Unknown';

                $user = $this->userModel->getUserById($user_id);
                $type = $user['role'] ?? 'unknown';

                $results[] = [
                    'id' => $id,
                    'title' => $title,
                    'category' => $categoryName,
                    'type' => $type,
                    'logo' => $logo
                ];
            }

            echo json_encode([
                'status' => 'success',
                'booths' => $results
            ]);

        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function filteredSearch(){

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode([
                'status'=> 'failure',
                'message'=>'Method not allowed'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $entry = $input['entry'] ?? null;
        $category = $input['category'] ?? null;
        $sortby = $input['sort_by'] ?? null;
        $orderby = $input['order_by'] ?? null;

        $category_id = $this->categoryModel->getCategory($category);


        if(!$sortby) $sortby = "business_name";
        if(!$orderby) $orderby = "ASC";

        try{
            if(!$entry){
                $vendors = $this->vendorModel->getVendorsByCategory($category_id, $sortby, $orderby);

            } else {
                $entry = trim($entry, ' ');
                $vendors = $this->vendorModel->getAllVendorsBySortedEntry($entry, $sortby, $orderby);
            }
            if (!$vendors){
                echo json_encode([
                    'status'=>'failure',
                    'message'=>'No matches else'
                ]);
                return;
            } else {
                $id = $vendors['vendor_id'];
                $name = $vendors['business_name'];
                $logo = $vendors['logo'];
                $category_id = $vendors['category_id'];
                $category = $this->categoryModel->getCategory($category_id);
                $categoryName = $category['category_name'] ?? 'Unknown';
                $user_id = $vendors['user_id'];
                $user = $this->userModel->getUserById($user_id);
                $type = $user['role'] ?? 'unknown';

                echo json_encode([
                    'status'=>'success',
                    'id' => $id,
                    'title' => $name,
                    'category' => $categoryName,
                    'type' => $type,
                    'logo' => $logo
                ]);
            }
            
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }    
    }

    public function addToSchedule(){
        $this->setJsonHeaders();

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode([
                'status'=> 'failure',
                'message'=>'Method not allowed'
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $user = $data['user'] ?? null;
        $event = $data['event'] ?? null;

        try{
            $this->scheduleModel->addSchedule($user, $event);
            echo json_encode([
                'status'=>'success',
                'message'=>'Event Added'
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }

    public function deleteSchedule(){
        $this->setJsonHeaders();

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode([
                'status'=> 'failure',
                'message'=>'Method not allowed'
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        $event = $data['event'] ?? null;

        try{
            $this->scheduleModel->deleteSchedule($event);
            echo json_encode([
                'status'=>'success',
                'message'=>'Event deleted'
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }

    public function mySchedule(){
        $this->setJsonHeaders();

        $user_id = $_SESSION['id'];

        try{
            $events = $this->scheduleModel->getScheduleByUser($user_id);
            echo json_encode([
                'status'=>'success',
                'events'=>$events
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }

    public function addFavourites(){
        $this->setJsonHeaders();

        $data = json_decode(file_get_contents("php://input"), true);

        $user = $_GET['userId'];
        $vendor = $_GET['title'];

        $vendor_id = $this->vendorModel->getVendorByName($vendor);
        $vendor_id = $vendor_id['vendor_id'];

        try {
            $this->favouriteModel->addFavourite($user, $vendor_id);
            echo json_encode([
                'status'=>'success',
                'message'=>'Added to Favourite'
            ]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error'=>$e->getMessage()]);
        }
    }

    public function loadMyEvents() {
        $this->setJsonHeaders();

        $user = $_SESSION['id'];
        $vendors = $this->favouriteModel->getFavouriteVendors($user);
        $results = [];

        foreach ($vendors as $vendor) {
            if (!$vendor) continue;

            $id = $vendor['vendor_id'];
            $vendor = $this->vendorModel->getVendorById($id);

            $title = $vendor['business_name'] ?? '';
            $logo = $vendor['logo'] ?? '';

            $events = $this->eventModel->getAllEventsByVendor($id);

            $formattedEvents = [];
            foreach ($events as $ev) {
                $formattedEvents[] = [
                    'event_id'=>$ev['event_id'],
                    'title' => $ev['title'],
                    'time' => date("g:i A", strtotime($ev['time_start'])),
                ];
            }

            $results[] = [
                'id' => $id,
                'title' => $title,
                'logo' => $logo,
                'events' => $formattedEvents,
            ];
        }
        

        echo json_encode([
            'status' => 'success',
            'events' => $results
        ]);
    }
}
?>