<?php
  $user_save_dir="./user_data";
  if(!isset($_GET["user_id"])){
    header("Location: ./error_page.php");
    exit();
  }elseif(!is_dir($user_save_dir."/".$_GET["user_id"])){
    header("Location: ./error_page.php");
    exit();
  }
  $user_id = $_GET["user_id"];
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
  <?php include './includes/header.php';?>
  <main>
    <div class="container">
      <div class="row">
        <div class="col mt-5 mb-5">
          <h1 class="fw-bolder mt-1 mb-3">Project Wait</h1>
          <hr class="featurette-divider">
          <div id="sequence_show"></div>
          <div id="exe_step_show"></div>
        </div>
      </div>
    </div>
  </main>
  <?php include './includes/footer.php';?>
  <script type="text/javascript" src="./assets/plugins/jquery-3.7.1/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript">
    const user_id = '<?php echo $user_id;?>';
    let sequence_interval;
    let step_interval;
    function get_job_sequence(){
      $.ajax({
        type: 'POST',
        url: './get_job_sequence.php',
        data: {user_id: user_id},
        dataType: 'json',
        success: function(data){
          if('er_msg' in data){
            window.location.href = "./error_page.php";
          }
          show_sequence(data);
          if(data.is_run === true){
            clearInterval(sequence_interval);
            init_step();
            step_interval = setInterval(get_job_step ,60000);
            get_job_step();
          }
        },
        error:function(xhr){
          console.log(xhr);
        }
      });
    }
    function show_sequence(data){
      if(data["sequence"]<1 && data["is_run"]===false){
        data["sequence"] = 1;
      }
      show_text = "<h3> Wait Job: "+data["sequence"]+"</h3>";
      $('#sequence_show').html(show_text);
    }
    function init_step(){
      show_text = '<h3>Progress</h3>\
      <div class="progress">\
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">\
        0%\
        </div>\
      </div>';
      $('#exe_step_show').html(show_text);
    }
    function get_job_step(){
      $.ajax({
        url: "./user_data/"+user_id+"/state.json?"+new Date().getTime(),
        dataType: "json",
        success:function(data){
          if(data.end != null){
            clearInterval(step_interval);
            window.location.href = "./proj_show.php?user_id="+user_id;
          }
          show_step(data);
        },
        error:function(xhr){
          console.log(xhr);
        }
      });
    }
    function show_step(data){
      let progress = 10;
      if (data.step_2) progress += 40;
      if (data.step_3) progress += 20;
      if (data.step_4) progress += 25;
      $(".progress-bar").css("width", progress + "%");
      $(".progress-bar").text(progress + "%");
    }
    $(document).ready(function(){
      sequence_interval = setInterval(get_job_sequence ,300000);
      get_job_sequence();
    });
  </script>
</body>
</html>
