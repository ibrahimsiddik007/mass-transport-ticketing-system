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
    --navbar-bg-dark: #1a1a1a;
    --nav-link-light: #000000;
    --nav-link-dark: #ffffff;
    --nav-link-hover: #4a00e0;
    --nav-link-active: #8e2de2;
    --navbar-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    --toggler-color: #000000;
  }

  .navbar {
    background-color: var(--navbar-bg-light);
    transition: all 0.3s ease-in-out;
    box-shadow: var(--navbar-shadow);
    padding: 1rem 2rem;
  }

  .navbar .nav-link {
    color: var(--nav-link-light);
    position: relative;
    transition: all 0.3s ease-in-out;
    margin-right: 15px;
    font-weight: 500;
    padding: 0.5rem 1rem;
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
    background: linear-gradient(90deg, var(--nav-link-hover), var(--nav-link-active));
    transform: scaleX(0);
    transition: transform 0.3s ease-in-out;
    border-radius: 2px;
  }

  .navbar .nav-link:hover::after,
  .navbar .nav-item.active .nav-link::after {
    transform: scaleX(1);
  }

  /* Dark Mode */
  body.dark-mode {
    --navbar-bg-light: var(--navbar-bg-dark);
    --nav-link-light: var(--nav-link-dark);
    --navbar-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    --toggler-color: #ffffff;
  }

  body.dark-mode .navbar {
    background-color: var(--navbar-bg-dark);
  }

  body.dark-mode .navbar .nav-link {
    color: var(--nav-link-dark);
  }

  body.dark-mode .navbar .nav-link:hover,
  body.dark-mode .navbar .nav-item.active .nav-link {
    color: var(--nav-link-hover);
  }

  body.dark-mode .navbar-toggler {
    color: var(--toggler-color);
    border-color: var(--toggler-color);
  }

  body.dark-mode .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
  }

  .profile-img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid var(--nav-link-hover);
    transition: all 0.3s ease-in-out;
  }

  body.dark-mode .profile-img {
    border-color: var(--nav-link-hover);
  }

  /* Navbar Title */
  #navbarTitle {
    color: var(--nav-link-light);
    font-weight: 600;
    font-size: 1.5rem;
    transition: all 0.3s ease-in-out;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
  }

  body.dark-mode #navbarTitle {
    color: var(--nav-link-dark);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
  }

  /* Theme Toggle Button */
  #theme-toggle {
    background: linear-gradient(135deg, var(--nav-link-hover), var(--nav-link-active));
    border: none;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    transition: all 0.3s ease-in-out;
  }

  #theme-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  }

  /* Badge Notification */
  .badge-danger {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    border-radius: 10px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
  }

  /* Active state for nav items */
  .nav-item.active .nav-link {
    color: var(--nav-link-hover);
    font-weight: 600;
  }

  body.dark-mode .nav-item.active .nav-link {
    color: var(--nav-link-hover);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    const body = document.body;

    if (currentTheme === 'dark') {
      body.classList.add('dark-mode');
    }

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        const theme = body.classList.contains('dark-mode') ? 'dark' : 'light';
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