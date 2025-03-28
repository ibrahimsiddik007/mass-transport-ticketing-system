<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light" id="navbar">
  <a class="navbar-brand" href="index.php" id="navbarTitle">Mass Transport Ticketing System</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php">Home</a>
      </li>
      <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'metro.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="metro.php" id="metro-link">Metro</a>
        <script>
          document.getElementById('metro-link').addEventListener('click', function(event) {
            event.preventDefault();
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
              localStorage.setItem('redirectURL', 'metro.php');
              window.location.href = 'login.php';
            } else {
              window.location.href = 'metro.php';
            }
          });
        </script>
      </li>
      <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'train.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="train.php" id="train-link">Train</a>
        <script>
          document.getElementById('train-link').addEventListener('click', function(event) {
            event.preventDefault();
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
              localStorage.setItem('redirectURL', 'train.php');
              window.location.href = 'login.php';
            } else {
              window.location.href = 'train.php';
            }
          });
        </script>
      </li>
      <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'bus_select_type.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="bus_select_type.php" id="bus-link">Bus</a>
        <script>
          document.getElementById('bus-link').addEventListener('click', function(event) {
            event.preventDefault();
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
              localStorage.setItem('redirectURL', 'bus_select_type.php');
              window.location.href = 'login.php';
            } else {
              window.location.href = 'bus_select_type.php';
            }
          });
        </script>
      </li>
      <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="chat.php" id="chat-link">
          Live Chat <span id="chat-notification" class="badge badge-danger" style="display: none;">0</span>
        </a>
        <script>
          document.getElementById('chat-link').addEventListener('click', function(event) {
            event.preventDefault();
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
              localStorage.setItem('redirectURL', 'chat.php');
              window.location.href = 'login.php';
            } else {
              window.location.href = 'chat.php';
            }
          });
        </script>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
            <img src="<?php echo isset($_SESSION['profile_image']) ? htmlspecialchars($_SESSION['profile_image']) : 'default_profile_image.png'; ?>" alt="profile_image" class="profile-img">
            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php">Register</a>
        </li>
      <?php endif; ?>
      <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
        <li class="nav-item">
          <button class="btn btn-secondary" id="theme-toggle">Change Theme</button>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<style>
  :root {
    --navbar-bg-light: #ffffff;
    --navbar-bg-dark: #2c2c2c;
    --nav-link-light: #000000;
    --nav-link-dark: #e0e0e0;
    --nav-link-hover: #007bff;
  }

  .navbar {
    background-color: var(--navbar-bg-light);
    transition: background-color 0.3s ease-in-out;
  }

  .navbar .nav-link {
    color: var(--nav-link-light);
    position: relative;
    transition: color 0.3s ease-in-out;
    margin-right: 15px;
  }

  .navbar .nav-link:hover,
  .navbar .nav-item.active .nav-link {
    color: var(--nav-link-hover);
  }

  /* Hover underline effect (slider) */
  .navbar .nav-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -5px;
    width: 100%;
    height: 3px;
    background-color: var(--nav-link-hover);
    transform: scaleX(0);
    transition: transform 0.3s ease-in-out;
  }

  .navbar .nav-link:hover::after,
  .navbar .nav-item.active .nav-link::after {
    transform: scaleX(1);
  }

  /* Dark Mode */
  .dark-mode {
    --navbar-bg-light: var(--navbar-bg-dark);
    --nav-link-light: var(--nav-link-dark);
  }

  .dark-mode .navbar {
    background-color: var(--navbar-bg-dark);
  }

  .dark-mode .navbar .nav-link {
    color: var(--nav-link-dark);
  }

  .dark-mode .navbar .nav-link:hover,
  .dark-mode .navbar .nav-item.active .nav-link {
    color: var(--nav-link-hover);
  }

  .profile-img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
  }

  /* Navbar Title */
  #navbarTitle {
    color: var(--nav-link-light);
    transition: color 0.3s ease-in-out;
  }

  .dark-mode #navbarTitle {
    color: var(--nav-link-dark);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme') || 'light';

    if (currentTheme === 'dark') {
      document.body.classList.add('dark-mode');
    }

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
      });
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    function checkNewMessages() {
        fetch('check_new_messages.php')
            .then(response => response.json())
            .then(data => {
                const chatNotification = document.getElementById('chat-notification');
                if (data.new_messages > 0) {
                    chatNotification.textContent = data.new_messages;
                    chatNotification.style.display = 'inline-block';
                } else {
                    chatNotification.style.display = 'none';
                }
            })
            .catch(error => console.error('Error checking new messages:', error));
    }

    // Check for new messages every 10 seconds
    setInterval(checkNewMessages, 5000);

    // Initial check when the page loads
    checkNewMessages();
  });
</script>