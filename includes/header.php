<style>
  .navbar {
    z-index: 100000
  }
</style>
<header>
  <!-- Fixed navbar -->
  <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Web site Name</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav me-auto mb-2 mb-md-0">
          <li class="nav-item">
            <?php
              if(isset($currentPage) && $currentPage === 'home'){
                echo '<a class="nav-link active" aria-current="page" href="#">Home</a>';
              }else{
                echo '<a class="nav-link" href="./home.php">Home</a>';
              }
            ?>
          </li>
          <li class="nav-item">
            <?php
              if(isset($user_id) && $user_id === '1714227943_599'){
                echo '<a class="nav-link active" aria-current="page" href="#">Sample 1</a>';
              }else{
                echo '<a class="nav-link" href="./proj_show.php?user_id=1714227943_599">Sample 1</a>';
              }
            ?>
          </li>
          <li class="nav-item">
            <?php
              if(isset($user_id) && $user_id === '1714227991_431'){
                echo '<a class="nav-link active" aria-current="page" href="#">Sample 2</a>';
              }else{
                echo '<a class="nav-link" href="./proj_show.php?user_id=1714227991_431">Sample 2</a>';
              }
            ?>
          </li>
          <li class="nav-item">
            <?php
              if(isset($user_id) && $user_id === '1714640160_886'){
                echo '<a class="nav-link active" aria-current="page" href="#">Sample 3</a>';
              }else{
                echo '<a class="nav-link" href="./proj_show.php?user_id=1714640160_886">Sample 3</a>';
              }
            ?>
          </li>
          <li class="nav-item">
            <?php
              if(isset($user_id) && $user_id === '1716257442_97'){
                echo '<a class="nav-link active" aria-current="page" href="#">Sample 4</a>';
              }else{
                echo '<a class="nav-link" href="./proj_show.php?user_id=1716257442_97">Sample 4</a>';
              }
            ?>
          </li>
          <li class="nav-item">
            <?php
              if(isset($currentPage) && $currentPage === 'help'){
                echo '<a class="nav-link active" aria-current="page" href="#">Help</a>';
              }else{
                echo '<a class="nav-link" href="./help.php">Help</a>';
              }
            ?>
          </li>
        </ul>
        <form class="d-flex" action="proj_show.php" method="get">
          <input class="form-control me-2"  name="user_id" type="text" placeholder="Job ID" aria-label="Search">
          <button class="btn btn-outline-success">Search</button>
        </form>
      </div>
    </div>
  </nav>
</header>
