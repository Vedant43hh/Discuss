<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="./">
      <img src="./public/logo.png" />
    </a>

    <?php // WHY: on small screens Bootstrap hides the menu inside "collapse"; this button is what opens it. ?>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="./">Home</a>
        </li>
        <?php
        if (isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <?php // WHY htmlspecialchars: the username is typed by the user at signup, so escape it before showing it. ?>
            <a class="nav-link"
              href="./server/requests.php?logout=true">Logout(<?php echo htmlspecialchars(ucfirst($_SESSION['user']['username'])) ?>)</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?ask=true">Ask A Question</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?u-id=<?php echo (int) $_SESSION['user']['user_id'] ?>">My Questions</a>
          </li>
        <?php } ?>

        <?php
        if (!isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="?login=true">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?signup=true">SignUp</a>
          </li>
        <?php } ?>

        <li class="nav-item">
          <a class="nav-link" href="?latest=true">Latest Questions</a>
        </li>
      </ul>
    </div>
    <form class="d-flex" action="">
      <?php // WHY htmlspecialchars: the search term comes from the URL; without escaping, a " in it could break out of value="..." and inject HTML. ?>
      <input class="form-control me-2" name="search" type="search" placeholder="Search questions"
        value="<?php echo htmlspecialchars($_GET['search'] ?? '') ?>">
      <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
  </div>
</nav>
