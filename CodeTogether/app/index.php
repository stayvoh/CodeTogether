<?php
declare(strict_types=1);
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
session_start();


include_once __DIR__ . "/config/Controller.php";
include_once __DIR__ . "/config/Router.php";
include_once __DIR__ . "/controllers/LoginController.php";
include_once __DIR__ . "/controllers/CreateAccountController.php";
include_once __DIR__ . "/controllers/HomeController.php";
include_once __DIR__ . "/controllers/AccountSettingsController.php";
include_once __DIR__ . "/controllers/MessagesController.php";
include_once __DIR__ . "/controllers/NotFoundController.php";
include_once __DIR__ . "/controllers/LandingController.php";
include_once __DIR__ . "/controllers/LogoutController.php";
include_once __DIR__ . "/controllers/SearchController.php";
include_once __DIR__ . "/controllers/GameController.php";
include_once __DIR__ . "/controllers/ProfileController.php";
include_once __DIR__ . "/controllers/AddPostController.php";
include_once __DIR__ . "/controllers/DeletePostController.php";
include_once __DIR__ . "/controllers/EditPostController.php";
include_once __DIR__ . "/controllers/LikePostController.php";
include_once __DIR__ . "/controllers/PrivacyPolicyController.php";
include_once __DIR__ . "/controllers/TermsController.php";
include_once __DIR__ . "/controllers/AddProfilePictureController.php";
include_once __DIR__ . "/controllers/AddProfileMusicController.php";
include_once __DIR__ . "/controllers/NoPermissionController.php";
include_once __DIR__ . "/controllers/MessagesController.php";
include_once __DIR__ . "/controllers/ViewPostController.php";
include_once __DIR__ . "/controllers/FlashCardsController.php";
include_once __DIR__ . "/controllers/AddPointsController.php";
include_once __DIR__ . "/controllers/UpdateStatusController.php";





# Create router instance
$router = new Router();
$router->showErrors(1);

# Register controllers
$router->addController('login', new LoginController());
$router->addController('logout', new LogoutController());
$router->addController('createAccount', new CreateAccountController());
$router->addController('home', new HomeController());
$router->addController('accountSettings', new AccountSettingsController());
$router->addController('messages', new MessagesController());
$router->addController('404', new NotFoundController());
$router->addController('landing', new LandingController());
$router->addController('search', new SearchController());
$router->addController('game', new GameController());
$router->addController('profile', new ProfileController());
$router->addController('addPost', new AddPostController());
$router->addController('addProfileMusic', new AddProfileMusicController());
$router->addController('deletePost', new DeletePostController());
$router->addController('editPost', new EditPostController());
$router->addController('likePost', new LikePostController());
$router->addController('privacyPolicy',new PrivacyPolicyController());
$router->addController('terms',new TermsController());
$router->addController('addProfilePicture',new AddProfilePictureController());
$router->addController('noPermission', new NoPermissionController());
$messagesController  = new MessagesController();
$router->addController('messages', $messagesController);
$router->addController('getMessages', $messagesController);
$router->addController('sendMessage', $messagesController);
$router->addController('viewPost', new ViewPostController());
$router->addController('cards', new FlashCardsController());
$router->addController('addPoints', new AddPointsController());
$router->addController('status', new UpdateStatusController());



# Register default controller (used when no action is specified)
$router->addController('default', new LandingController());

# Run router
$router->run();
?>