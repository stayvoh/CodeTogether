<?php
declare(strict_types=1);
include_once __DIR__ . "/../dao/UserDAO.php";

class AddProfileMusicController extends Controller
{
    private UserDAO $userDao;

    public function performAction(): void
    {
        if (!isset($_SESSION['usercreds']['userID'])) {
            header('Location: index.php?action=login');
            exit;
        }

        // No file field at all?
        if (!isset($_FILES['profileMusic'])) {
            $_SESSION['upload_error'] = 'Please choose an audio file before uploading.';
            header('Location: index.php?action=profile');
            exit;
        }

        $file = $_FILES['profileMusic'];

        // Handle PHP upload errors explicitly
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $_SESSION['upload_error'] = 'File is too large';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $_SESSION['upload_error'] = 'Please choose an audio file before uploading.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $_SESSION['upload_error'] = 'Upload was interrupted. Please try again.';
                    break;
                default:
                    $_SESSION['upload_error'] = 'There was a problem with the upload. Please try again.';
                    break;
            }
            header('Location: index.php?action=profile');
            exit;
        }

        // At this point PHP accepted the file, now we enforce our own limits
        $uploadDir = __DIR__ . '/../public/uploads/';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
            $_SESSION['upload_error'] = 'Upload directory does not exist and could not be created.';
            header('Location: index.php?action=profile');
            exit;
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Allowed audio types
        $allowedExtensions = ['mp3', 'wav', 'ogg'];
        if (!in_array($extension, $allowedExtensions, true)) {
            $_SESSION['upload_error'] = "Invalid file type: .$extension — allowed types are MP3, WAV, OGG.";
            header('Location: index.php?action=profile');
            exit;
        }

        $maxBytes = 10 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            $_SESSION['upload_error'] = 'File is too large. Max size is 10 MB.';
            header('Location: index.php?action=profile');
            exit;
        }

        $newFileName = uniqid('music_', true) . '.' . $extension;
        $filePath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['upload_error'] = 'Failed to move the uploaded file. Please try again.';
            header('Location: index.php?action=profile');
            exit;
        }

        $this->userDao = new UserDAO();
        $userID = (int)$_SESSION['usercreds']['userID'];

        if (!$this->userDao->updateProfileMusic($userID, $newFileName)) {
            $_SESSION['upload_error'] = 'Database update failed.';
            header('Location: index.php?action=profile');
            exit;
        }

        $_SESSION['upload_success'] = 'Profile song updated successfully!';
        header('Location: index.php?action=profile');
        exit;
    }

    public function renderView(string $view, array $data = []): void
    {
        parent::renderView($view, $data);
    }
}
