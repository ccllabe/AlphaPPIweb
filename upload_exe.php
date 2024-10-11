<?php
  session_start();
  $user_save_dir="./user_data";
  $job_add_dir="./jobs";
  $python_env="/path/to/alphapulldown_env/bin/python"; # Replace with the actual Python environment path where AlphaPulldown is installed
  function processFormData(){
    global $user_save_dir, $job_add_dir;
    list($check, $msg) = formVal();
    if($check){
      $user_id=time()."_".rand(0,999);
      $user_id_path=$user_save_dir."/".$user_id."/";
      $user_input_path=$user_id_path."user_input/";
      $baits_seq_path=$user_input_path."baits.fasta";
      $candidates_seq_path=$user_input_path."candidates.fasta";
      mkdir($user_id_path, 0777);
      chmod($user_id_path, 0777);
      mkdir($user_input_path, 0777);
      chmod($user_input_path, 0777);
      move_uploaded_file($_FILES["baits"]["tmp_name"], $baits_seq_path);
      move_uploaded_file($_FILES["candidates"]["tmp_name"], $candidates_seq_path);
      $msg = fastaProcess($user_input_path);
      if(!isset($msg)){
        $job_path=$job_add_dir."/".$user_id."/";
        mkdir($job_path, 0777);
        chmod($job_path, 0777);
        $_SESSION["user_id"]=$user_id;
        header("Location: ./proj_wait.php?user_id=".$user_id);
        exit();
      }
      rrmdir($user_id_path);
    }
    if(isset($msg)){
      $_SESSION["error"]=$msg;
      header("Location: ./home.php");
      exit();
    }
  }
  function formVal(){
    if(!isset($_POST["action"])){
      return array(false, "Inactive action!!");
    }
    if($_POST["action"]!=="seqs_info_upload"){
      return array(false, "Inactive action!!");
    }
    if($_FILES["baits"]["error"]>0){
      return array(false, "Baits .fasta file transfer failed!!");
    }
    if($_FILES["candidates"]["error"]>0){
      return array(false, "Candidates .fasta file transfer failed!!");
    }
    return array(true, null);
  }
  function fastaProcess($user_input_path){
    global $python_env;
    $command = escapeshellcmd("$python_env fasta_get_info.py $user_input_path");
    $output = shell_exec($command);
    $json_output = json_decode($output, true);
    if (isset($json_output["error"])){
      return $json_output["error"];
    }else{
      return null;
    }
  }
  function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
            rrmdir($dir. DIRECTORY_SEPARATOR .$object);
          else
            unlink($dir. DIRECTORY_SEPARATOR .$object);
        }
      }
      rmdir($dir);
    }
  }
  processFormData();
?>
