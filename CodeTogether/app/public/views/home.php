<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Home Page</title>
    <!--bootsrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!--navigation icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJc5nI6Jj4QkI7U1vKjK+L0n4A0w4Z+T5E5R5B5B5Y5S5T5W5V5U5T5Q5V5W5X5Y5Z5"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/public/css/core/main.css">
    <link rel="stylesheet" href="/public/css/page/home.css">
</head>

<body>
    <canvas id="matrix-canvas"></canvas>
    <!--NavBar-->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <!--end of navigation-->
    <main class="page-home">
        <div class="container py-5">
            <div class="row justify-content-center">
                <!--left column; on lg screen it is 3/12 width-->
                <div class="col-lg-3 mb-4 order-lg-1 order-1">
                    <!--profile summary card-->
                    <div class="profile-card text-center mb-4">
                        <?php
                        $profilePic = $data['user']->getProfilePicture() ?? '';
                        $absolutePath = __DIR__ . '/../uploads/' . $profilePic;
                        $webPath = '/public/uploads/' . $profilePic;
                        $fileExists = !empty($profilePic) && file_exists($absolutePath);




                        // Check extension type
                        $extension = !empty($profilePic) ? strtolower(pathinfo($profilePic, PATHINFO_EXTENSION)) : ''; ?>

                        <?php if ($fileExists): ?>
                            <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= htmlspecialchars($webPath) ?>"
                                    class="profile-avatar mx-auto d-block rounded-circle mb-3" alt=""
                                    style="width:120px;height:120px;object-fit:cover;">
                            <?php elseif (in_array($extension, ['mp4', 'webm', 'ogg'])): ?>
                                <video controls class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                                    <source src="<?= htmlspecialchars($webPath) ?>" type="video/<?= $extension ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Fallback placeholder -->
                            <img src="https://placehold.co/120x120/4a5568/ffffff?text=<?= substr($data['user']->getUserName(), 0, 1) ?>"
                                alt="Profile Avatar" class="profile-avatar mx-auto d-block rounded-circle mb-3">
                        <?php endif; ?>

                        <h2 class="mb-0 "><?= htmlspecialchars($data['user']->getUserName()) ?></h2>
                        <p class="mb-2 "><?= htmlspecialchars($data['user']->getEmail()) ?></p>

                        <div class="row mt-3">
                            <div class="col-4 stat-item">
                                <h3 class="fw-bold mb-0 "><?= htmlspecialchars($data['user']->getPoints()) ?>
                                </h3>
                                <small class="">Game Points</small>
                            </div>
                            <div class="col-4 stat-item">
                                <h3 class="fw-bold mb-0 "><?= htmlspecialchars(count($data['friends'])) ?>
                                </h3>
                                <small class="">Friends</small>
                            </div>
                            <div class="col-4 stat-item">
                                <h3 class="fw-bold mb-0 "><?= htmlspecialchars(count($data['userPosts'])) ?>
                                </h3>
                                <small class="">Posts</small>
                            </div>
                        </div>

                        <a href="index.php?action=addPost" class="btn btn-success w-100 mt-3 rounded-pill">
                            <i class="fas fa-plus me-2"></i> Add New Post
                        </a>
                    </div>

                    <!--end of profile card-->

                    <!-- the about me info card-->
                    <div id="aboutMeCard" class="profile-card mb-4 mt-3 p-3">
                        <h4 class="text-info  mb-3">About <?= htmlspecialchars($user->getUserName()) ?></h4>
                        <div class=" text-break"
                            style="background-color:#4a4468;color:#fff;border:1px solid #06a342;resize:none; padding: 10px; border-radius: 8px;">
                            <?= htmlspecialchars($user->getAboutMe() ?? 'Nothing here yet!') ?></textarea>
                        </div>
                    </div>

                    <!--end of the about me box-->

                <?php
                    // Profile song display on home page
                    $musicFile   = $data['user']->getProfileMusic();
                    $musicFsPath = $musicFile ? (__DIR__ . '/../uploads/' . $musicFile) : null;
                    $musicWebPath = $musicFile ? ('/public/uploads/' . rawurlencode((string)$musicFile)) : null;
                    ?>

                    <div class="profile-card mb-4 mt-3 p-3">
                        <h4 class="text-info mb-3">Profile Song</h4>

                        <?php if (!empty($musicFile) && $musicFsPath && file_exists($musicFsPath)): ?>
                            <audio controls class="w-100 mb-2">
                                <source src="<?= htmlspecialchars($musicWebPath, ENT_QUOTES, 'UTF-8') ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        <?php else: ?>
                            <p class="text-muted mb-0">No profile song set yet.</p>
                        <?php endif; ?>

                        <a href="index.php?action=profile&user_id=<?= $data['user']->getUserID(); ?>"
                        class="btn btn-sm btn-outline-info rounded-pill w-100 mt-2">
                            Edit Profile Song
                        </a>
                    </div>


                </div>
                <!--end of left column-->

                <!-- middle column: posts will be 6/12 on large screens-->
                <div class="col-lg-6 mb-4 order-lg-2 order-3">
                    <?php include __DIR__ . '/../includes/aiWidget.php'; ?><!--This is the include for the ai stuff!-->
                    <h3 class="mb-4 ">Latest Posts</h3>
                    <!--php integration for posts somewhat complete. I added some fake posts and got them to display, but it could use some work this need comments to show up properly, and the ability to display image/video-->
                    <?php foreach ($data['userAndFriendPosts'] as $post): ?>
                        <?php include __DIR__ . '/../includes/postVisual.php'; ?>
                    <?php endforeach; ?>
                </div>
                <!-- End Posts Feed (middle column) -->

                <!--right side of page for friends list  will be 3/12 width on large screens-->
                <div class="col-lg-3 mb-4 order-lg-3 order-2">
                    <?php include __DIR__ . '/../includes/leaderboardWidget.php'; ?><!--Leaderboard!-->
                    <div class="friends-card">
                        <h4 class="text-info mb-3 ">Online Friends (<?php echo count($data['friends']); ?>)
                        </h4>

                        <?php foreach ($data['friendsUser'] as $friendUser):
                            $statusClass = 'text-danger';
                            $imageColor = 'd9534f';
                            $statusText = 'Offline';
                            if ($friendUser->getStatus() === 'online') {
                                $statusClass = 'text-success';
                                $statusText = 'Online';
                                $imageColor = '5cb85c';
                            } elseif ($friendUser->getStatus() === 'away') {
                                $statusClass = 'text-warning';
                                $imageColor = 'f0ad4e';
                                $statusText = 'Away';
                            }
                            ?>
                            <!-- href="index.php?action=accountSettings" -->
                            <!-- href="public/profile_page/profile.php?user_id=<?= $friendUser->getUserID(); ?>" -->
                            <div class="friend-item d-flex align-items-center mb-2"
                                data-friend-id="<?= $friendUser->getUserID(); ?>">

                                <a href="index.php?action=profile&user_id=<?= $friendUser->getUserID(); ?>"
                                    class="d-flex align-items-center text-decoration-none flex-grow-1 nav-fade">

                                    <?php
                                    $profilePic = $friendUser->getProfilePicture() ?? '';
                                    $absolutePath = __DIR__ . '/../uploads/' . $profilePic;
                                    $webPath = '/public/uploads/' . $profilePic;
                                    $fileExists = !empty($profilePic) && file_exists($absolutePath);

                                    // Fallback color + placeholder text (initial)
                                    $initial = substr($friendUser->getUserName(), 0, 1);
                                    $placeholderUrl = "https://placehold.co/40x40/{$imageColor}/ffffff?text={$initial}";
                                    ?>

                                    <?php if ($fileExists): ?>
                                        <img src="<?= htmlspecialchars($webPath) ?>" alt=""
                                            class="friend-avatar me-2 rounded-circle">
                                    <?php else: ?>
                                        <img src="<?= $placeholderUrl ?>" alt="Friend Avatar"
                                            class="friend-avatar me-2 rounded-circle">
                                    <?php endif; ?>

                                    <div>
                                        <div class="fw-bold ">
                                            <?= htmlspecialchars($friendUser->getUserName()); ?>
                                        </div>
                                        <small class="<?= $statusClass; ?>"><?= $statusText; ?></small>
                                    </div>
                                </a>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- end of main container-->
    </main>


    <script src="/public/js/container/ai.js"></script>
    <script src="/public/js/page/post.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        xintegrity="sha384-I7E8VVD/ismYTF4yFOWMaa4G8Hh8MfWfQ9SFJdFjO3/B5Gowu/Q7X9+l+O/Y5z4z0J"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        xintegrity="sha384-0pUGZvbkm6XF6gxjEnlwpMCEoV3f73SjJ+J8C6W6D2Kx5lM7B8K2FfR7R7E7Q"
        crossorigin="anonymous"></script>
    <script src="/public/js/core/rain.js"></script>
    <script src="/public/js/core/theme.js"></script>
    <script src="/public/js/page/home.js"></script>
    <script src="/public/js/core/status.js"></script>

</body>

</html>