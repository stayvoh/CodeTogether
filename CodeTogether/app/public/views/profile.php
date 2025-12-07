<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($user->getUserName()) ?>'s Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="/public/css/core/main.css">
  <link rel="stylesheet" href="/public/css/page/profile.css">
</head>

<body>
  <canvas id="matrix-canvas"></canvas>

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <script src="/public/js/container/ai.js"></script>
  <script src="/public/js/profile.js"></script>

  <main class="page-profile">
    <div class="main container py-5">
      <div class="row justify-content-center">
        <!-- Left column -->
        <aside class="col-lg-3 mb-4">
          <div class="profile-card text-center p-3">
            <?php
            $loggedInID = $_SESSION['usercreds']['userID'] ?? 0;
            $isOwnProfile = ($loggedInID === $user->getUserID());
            $status;

            //check if logged in user is already friends with this profile user
            $isFriend = false;
            foreach ($friends as $friend) {
              if (
                method_exists($friend, 'getFriendID') &&
                $friend->getFriendID() === $user->getUserID()
              ) {
                $isFriend = true;
                break;
              }
            }
            ?>

            <?php
            $profilePic = $data['user']->getProfilePicture() ?? '';
            $absolutePath = __DIR__ . '/../uploads/' . $profilePic;
            $webPath = '/public/uploads/' . $profilePic;



            // Check extension type
            $extension = !empty($profilePic) ? strtolower(pathinfo($profilePic, PATHINFO_EXTENSION)) : ''; ?>

            <?php if (!empty($profilePic) && file_exists($absolutePath)): ?>
              <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?= htmlspecialchars($webPath) ?>" class="profile-avatar mx-auto d-block rounded-circle mb-3"
                  alt="" style="width:120px;height:120px;object-fit:cover;">
              <?php elseif (in_array($extension, ['mp4', 'webm', 'ogg'])): ?>
                <video controls class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                  <source src="<?= htmlspecialchars($webPath) ?>" type="video/<?= $extension ?>">
                  Your browser does not support the video tag.
                </video>
              <?php endif; ?>
            <?php else: ?>
              <!-- Fallback placeholder -->
              <img src="https://placehold.co/120x120/4a5568/ffffff?text=<?= substr($data['user']->getUserName(), 0, 1) ?>"
                alt="Profile Avatar" class="profile-avatar mx-auto d-block rounded-circle mb-3"
                style="width:120px;height:120px;object-fit:cover;">
            <?php endif; ?>
            <?php if ($isOwnProfile): ?>
              <form action="index.php?action=addProfilePicture" method="POST" enctype="multipart/form-data" class="mt-2">
                <input type="file" name="profilePic" accept="image/*" class="form-control form-control-sm mb-2" required>
                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                  <i class="fas fa-upload me-1"></i> Update Picture
                </button>
              </form>
            <?php endif; ?>


            <?php if (isset($_SESSION['upload_error'])): ?>
              <div class="alert alert-danger text-center">
                <?= htmlspecialchars($_SESSION['upload_error']); ?>
              </div>
              <?php unset($_SESSION['upload_error']); ?>
            <?php elseif (isset($_SESSION['upload_success'])): ?>
              <div class="alert alert-success text-center">
                <?= htmlspecialchars($_SESSION['upload_success']); ?>
              </div>
              <?php unset($_SESSION['upload_success']); ?>
            <?php endif; ?>

            <h3 class=""><?= htmlspecialchars($user->getUserName()) ?></h3>
            <p class="text-info mb-2"><?= htmlspecialchars($user->getEmail() ?? 'No email') ?></p>

            <div class="row mt-3 ">
              <div class="col-6">
                <strong>Points</strong><br><?= htmlspecialchars($user->getPoints()) ?>
              </div>
              <div class="col-6">
                <strong>Status</strong><br><?= htmlspecialchars($user->getStatus()) ?>
              </div>
            </div>

            <p class="text-secondary small mt-3">
              Joined: <?= htmlspecialchars($user->getCreatedOn()->format('Y-m-d')) ?>
            </p>

            


            <?php if (!$isOwnProfile): ?>
              <!-- Show only when viewing another user's profile -->
              <?php if ($isFriend): ?>
                <form action="index.php?action=search" method="POST" class="mb-2">
                  <input type="hidden" name="friend_id" value="<?= $user->getUserID(); ?>">
                  <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI']; ?>">
                  <input type="hidden" name="task" value="remove">
                  <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="fas fa-user-minus me-1"></i> Remove Friend
                  </button>
                </form>
              <?php else: ?>
                <form action="index.php?action=search" method="POST" class="mb-2">
                  <input type="hidden" name="friend_id" value="<?= $user->getUserID(); ?>">
                  <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI']; ?>">
                  <input type="hidden" name="task" value="request">
                  <button type="submit" class="btn btn-outline-success btn-sm w-100">
                    <i class="fas fa-user-plus me-1"></i> Add Friend
                  </button>
                </form>
              <?php endif; ?>
            <?php endif; ?>
              

            <button class="btn btn-outline-info btn-sm mt-2 w-100" onclick="window.location='index.php?action=home'">
              <i class="fas fa-home me-1"></i> Back to Home
            </button>
          </div>

          <?php if (!$isOwnProfile): ?>
            <div id="aboutMeCard" class="profile-card mb-4 mt-3 p-3">
              <h4 class="text-info  mb-3">About <?= htmlspecialchars($user->getUserName()) ?></h4>
              <div class=" text-break"
                style="background-color: #4a4468; border: 1px solid #06a342; padding: 10px; border-radius: 8px;">
                <?= htmlspecialchars($user->getAboutMe() ?? 'Nothing here yet!') ?></textarea>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($isOwnProfile): ?>
            <div id="aboutMeCard" class="profile-card mb-4 mt-3 p-3">
                <h4 class="text-info mb-3">About Me</h4>

                <form method="POST" action="index.php?action=profile&user_id=<?= $user->getUserID() ?>">
                  <textarea name="aboutMe" rows="4" class="form-control mb-2"
                      style="background-color:#4a4468;color:#fff;border:1px solid #06a342;resize:none;"><?=
                      trim(htmlspecialchars($user->getAboutMe() ?? 'Nothing here yet!'))
                  ?></textarea>


                  <button type="submit" class="btn btn-sm btn-outline-info rounded-pill w-100">
                      Save
                  </button>
                </form>
            </div>
          <?php endif; ?>

          <?php
            // Figure out current user's music file
            $musicFile   = $user->getProfileMusic();
            $musicFsPath = $musicFile ? (__DIR__ . '/../uploads/' . $musicFile) : null;
            $musicWebPath = $musicFile ? ('/public/uploads/' . rawurlencode((string)$musicFile)) : null;

            // Simple "is this my profile?" check using the same session you already use
            $isOwnProfile = !empty($_SESSION['usercreds']['userID'])
                && (int)$_SESSION['usercreds']['userID'] === (int)$user->getUserID();
            ?>

            <div class="profile-card mb-4 mt-3 p-3">
              <h4 class="text-info mb-3">Profile Song</h4>

              <?php if (!empty($musicFile) && $musicFsPath && file_exists($musicFsPath)): ?>
                <audio controls class="w-100 mb-2">
                  <source src="<?= htmlspecialchars($musicWebPath, ENT_QUOTES, 'UTF-8') ?>" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio>
              <?php else: ?>
                <p class="text-muted mb-2 mb-0">No profile song set yet.</p>
              <?php endif; ?>

              <?php if ($isOwnProfile): ?>
                <form action="index.php?action=addProfileMusic"
                      method="post"
                      enctype="multipart/form-data"
                      class="mt-2">
                  <div class="mb-2">
                    <input type="file"
                          name="profileMusic"
                          class="form-control form-control-sm"
                          accept="audio/*">
                  </div>
                  <button type="submit" class="btn btn-sm btn-outline-info rounded-pill w-100">
                    <?= !empty($musicFile) ? 'Change Profile Song' : 'Upload Profile Song' ?>
                  </button>
                </form>
              <?php endif; ?>
            </div>


          <!-- Friends list -->
          <div class="friends-card mt-4 p-3">
            <h5 class="text-info mb-3"><?= htmlspecialchars($user->getUserName()) ?>'s Friends
              (<?= count($friendsUser) ?>)</h5>
            <?php foreach ($friendsUser as $friendUser): ?>
              <?php
              $statusClass = 'text-danger';
              $statusText = 'Offline';
              $imageColor = 'd9534f';
              if ($friendUser->getStatus() === 'online') {
                $statusClass = 'text-success';
                $statusText = 'Online';
                $imageColor = '5cb85c';
              } elseif ($friendUser->getStatus() === 'away') {
                $statusClass = 'text-warning';
                $statusText = 'Away';
                $imageColor = 'f0ad4e';
              }
              ?>
              <div class="friend-item d-flex align-items-center mb-2" data-friend-id="<?= $friendUser->getUserID(); ?>">

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
                    <img src="<?= htmlspecialchars($webPath) ?>" alt="" class="friend-avatar me-2 rounded-circle">
                  <?php else: ?>
                    <img src="<?= $placeholderUrl ?>" alt="Friend Avatar" class="friend-avatar me-2 rounded-circle">
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
        </aside>

        <!-- Middle column: posts -->
        <section class="col-lg-6 mb-4">
          <?php include __DIR__ . '/../includes/aiWidget.php'; ?>
          <h2 class=" mb-3"><?= htmlspecialchars($user->getUserName()) ?>'s Posts</h2>
          <?php if (empty($userPosts)): ?>
            <p class="text-secondary">No posts yet.</p>
          <?php else: ?>
            <?php foreach ($userPosts as $post): ?>
              <?php include __DIR__ . '/../includes/postVisual.php'; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>
  <script src="/public/js/core/theme.js"></script>
  <script src="/public/js/core/rain.js"></script>
  <script src="/public/js/page/post.js"></script>

  <!--Need this for the bootstrap plus Popper for drop downs and other cool things.-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
</body>

</html>