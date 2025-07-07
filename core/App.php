<?php
// App.php
class App {
    private $router;
    
    public function __construct() {
        $this->initializeApp();
        require_once 'Router.php';
        $this->router = new Router();
        $this->setupRoutes();
    }
    
    private function initializeApp() {
        // Set error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Set timezone
        date_default_timezone_set('UTC');
        
        // Start session if needed
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Include necessary files
        $this->autoloadClasses();
    }
    
    private function autoloadClasses() {
        // Basic autoloader for your classes
        spl_autoload_register(function ($className) {
            $paths = [
                'controllers/' . $className . '.php',
                'models/' . $className . '.php',
                'core/' . $className . '.php',
                'config/' . $className . '.php',
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    return;
                }
            }
        });
    }

    
    private function setupRoutes() {
        $this->router->setBasePath('/zedfair_backend');

        // Auth Routes
        $this->router->addRoute('POST', '/', 'AuthController', 'login');
        $this->router->addRoute('POST', '/signup', 'AuthController', 'signup');
        $this->router->addRoute('POST', '/logout', 'AuthController', 'logout');

        // Vendor Routes
        $this->router->addRoute('POST', '/vendor/addlogo', 'VendorController', 'uploadLogo');
        $this->router->addRoute('POST', '/vendor/addvendor', 'VendorController', 'addVendor');
        $this->router->addRoute('POST', '/vendor/addbooth', 'VendorController', 'addBooth');
        $this->router->addRoute('GET', '/vendor/category', 'VendorController', 'fetchCategories');
        $this->router->addRoute('GET', '/vendor/myprofile', 'VendorController', 'myProfile');
        $this->router->addRoute('POST', '/vendor/addevents', 'VendorController', 'addEvent');
        $this->router->addRoute('GET', '/vendor/myevents', 'VendorController', 'fetchEvents');
        $this->router->addRoute('POST', '/vendor/delevent', 'VendorController', 'delEvent');
        $this->router->addRoute('GET', '/vendor/myattendees', 'VendorController', 'registeredAttendees');

        // Explorer Routes
        $this->router->addRoute('GET', '/explorer/search', 'UserController', 'generalSearch');
        $this->router->addRoute('GET', '/explorer/fetchusers', 'UserController', 'fetchAllUsers');
        $this->router->addRoute('GET', '/explorer/fetchevents', 'UserController', 'upcomingEvents');
        $this->router->addRoute('GET', '/explorer/highlights', 'UserController', 'featuredHighlights');
        $this->router->addRoute('GET', '/explorer/allbooths', 'UserController', 'exploreBooths');
        $this->router->addRoute('POST', '/explorer/filterbooths', 'UserController', 'filteredSearch');
        $this->router->addRoute('POST', '/explorer/addschedule', 'UserController', 'addToSchedule');
        $this->router->addRoute('POST', '/explorer/delSchedule', 'UserController', 'deleteSchedule');
        $this->router->addRoute('GET', '/explorer/myschedule', 'UserController', 'mySchedule');
        $this->router->addRoute('GET', '/explorer/myevents', 'UserController', 'loadMyEvents');
        $this->router->addRoute('GET', '/explorer/addfavourite', 'UserController', 'addFavourites');
    }
    
    public function run() {
        try {
            $this->router->handleRequest();
        } catch (Exception $e) {
            // Handle errors gracefully
            if (ini_get('display_errors')) {
                echo "Application Error: " . $e->getMessage();
            } else {
                echo "An error occurred. Please try again later.";
            }
        }
    }
    
    // Helper method to include your database config
    public function loadConfig($configFile) {
        $configPath = "app/config/{$configFile}.php";
        if (file_exists($configPath)) {
            require_once $configPath;
        }
    }
}
?>