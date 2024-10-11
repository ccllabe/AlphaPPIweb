<?php
  $user_save_dir="./user_data";
  $job_add_dir="./jobs";
  function get_job_sequence(){
    global $user_save_dir, $job_add_dir;
    list($check, $msg) = postVal();
    if($check){
      $user_id = $_POST["user_id"];
      $job_quene = scandir($job_add_dir);
      $job_quene = array_diff($job_quene, array('..', '.', 'running.json'));
      sort($job_quene);
      if(in_array($user_id, $job_quene)){
        $sequence = array_keys($job_quene, $user_id)[0];
      }else{
        $msg = "Not in the waiting execution queue!!";
      }
      $state_log_path = $user_save_dir."/".$_POST["user_id"]."/state.json";
      if(is_file($state_log_path)){
        $is_run = true;
      }else{
        $is_run = false;
      }
    }
    if(!isset($msg)){
      echo json_encode(array('is_run'=>$is_run, 'sequence'=>$sequence));
    }elseif($msg === "Not in the waiting execution queue!!"){
      echo json_encode(array('is_run'=>true, 'sequence'=>0));
    }else{
      echo json_encode(array('er_msg'=>$msg));
    }
  }
  function postVal(){
    global $user_save_dir, $job_add_dir;
    if(!isset($_POST["user_id"])){
      return array(false, "Inactive action!!");
    }
    if(!is_dir($job_add_dir."/".$_POST["user_id"])){
      return array(false, "Not in the waiting execution queue!!");
    }
    if(!is_dir($user_save_dir."/".$_POST["user_id"])){
      return array(false, "Job doesn't exist!!");
    }
    return array(true, null);
  }
  get_job_sequence();
?>
