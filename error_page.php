<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web</title>
  <link href="./assets/plugins/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      padding-top: 3.5rem;
    }
  </style>
</head>
<body>
  <?php include './includes/header.php';?>
  <main>
    <div class="d-flex align-items-center justify-content-center vh-100">
      <div class="text-center">
        <h1 class="display-1 fw-bold">404</h1>
        <p class="fs-3"> <span class="text-danger">Opps!</span> Page not found.</p>
        <p class="lead">
          The page you’re looking for doesn’t exist.
        </p>
        <a href="home.php" class="btn btn-primary">Go Home</a>
      </div>
    </div>
  </main>
  <?php include './includes/footer.php';?>
  <script type="text/javascript" src="./assets/plugins/jquery-3.7.1/jquery-3.7.1.slim.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
