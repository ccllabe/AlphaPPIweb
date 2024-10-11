<?php
  $user_save_dir="./user_data";
  function zip_user_seqs(){
    global $user_save_dir;
    if(!isset($_POST["user_id"])){
      echo json_encode(array('er_msg'=>"No user ID provided!!"));
    }else{
      $seqs_fd_path = $user_save_dir."/".$_POST["user_id"]."/eval_vis_output/align_pdb/";
      $seqs_zip_path = $user_save_dir."/".$_POST["user_id"]."/eval_vis_output/align_pdb.zip";
      if(!is_dir($seqs_fd_path)){
        echo json_encode(array('er_msg'=>"The user ID currently does not have permission to obtain the compressed file!!"));
      }elseif(is_file($seqs_zip_path)){
        echo json_encode(array('zip_exist'=>true));
      }else{
        $seqs_zip = new ZipArchive();
        $seqs_fd_path = realpath($seqs_fd_path);
        $seqs_zip->open($seqs_zip_path, ZipArchive::CREATE);
        addFilesToZip($seqs_fd_path, $seqs_zip);
        $seqs_zip->close();
        echo json_encode(array('zip_exist'=>true));
      }
    }
  }
  function addFilesToZip($folderPath, $zip){
    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($folderPath),
      RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file){
      if(!$file->isDir()){
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($folderPath)+1);
        $zip -> addFile($filePath, $relativePath);
      }
    }
  }
  zip_user_seqs();
?>
