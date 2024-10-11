<?php
  session_start();
  if (isset($_SESSION["error"])){
    $error = $_SESSION["error"];
    unset($_SESSION['error']);
    echo '<script type="text/javascript">alert("'.$error.'");</script>';
  }
?>
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
  <?php $currentPage='home'; include './includes/header.php';?>
  <main>
    <div class="container">
      <div class="row bg-white text-primary-emphasis">
        <div class="col-md mt-5 mb-5">
          <svg class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: 500x500" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>Placeholder</title>
            <rect width="100%" height="100%" fill="#eee"></rect>
            <text x="50%" y="50%" fill="#aaa" dy=".3em">Abstract+Workflow</text>
          </svg>
        </div>
        <div class="col-md mt-5 mb-5">
          <h3 class="fw-bolder mt-1 mb-3">Upload Sequences Data</h3>
          <form method="POST" action="upload_exe.php" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="baits" class="form-label">Baits:</label>
              <input class="form-control" id="baits" name="baits" type="file" accept=".fasta" required>
            </div>
            <div class="mb-3">
              <label for="candidates" class="form-label">Candidates:</label>
              <input class="form-control" id="candidates" name="candidates" type="file" accept=".fasta" required>
            </div>
            <input type="hidden" name="action" value="seqs_info_upload">
            <button type="submit" class="btn btn-dark">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </main>
  <?php include './includes/footer.php';?>
  <script type="text/javascript" src="./assets/plugins/jquery-3.7.1/jquery-3.7.1.slim.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript">
    function baits_restrict(){
      $('#baits').on('change', function () {
        const fileInput = $(this);
        const file = fileInput[0].files[0];
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        if (file.size > 20*1024) {
          alert('Max upload size is 20KB!!');
          fileInput.val('');
        }
        if (fileExtension !== 'fasta'){
          alert('Only Fasta files allowed to be uploaded!!');
          fileInput.val('');
        }
      });
    }
    function candidates_restrict(){
      $('#candidates').on('change', function () {
        const fileInput = $(this);
        const file = fileInput[0].files[0];
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        if (file.size > 20*1024) {
          alert('Max upload size is 20KB!!');
          fileInput.val('');
        }
        if (fileExtension !== 'fasta'){
          alert('Only Fasta files allowed to be uploaded!!');
          fileInput.val('');
        }
      });
    }
    $(document).ready(function(){
      baits_restrict();
      candidates_restrict();
    });
  </script>
</body>
</html>
