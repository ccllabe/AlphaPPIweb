<?php
  $user_save_dir="./user_data";
  if(!isset($_GET["user_id"])){
    header("Location: ./error_page.php");
    exit();
  }elseif(!is_dir($user_save_dir."/".$_GET["user_id"])){
    header("Location: ./error_page.php");
    exit();
  }elseif(!is_file($user_save_dir."/".$_GET["user_id"]."/state.json")){
    header("Location: ./proj_wait.php?user_id=".$_GET["user_id"]);
    exit();
  }else{
    $state_content = file_get_contents($user_save_dir."/".$_GET["user_id"]."/state.json");
    $state_content = json_decode($state_content);
    if(is_null($state_content->{'end'})){
      header("Location: ./proj_wait.php?user_id=".$_GET["user_id"]);
      exit();
    }
  }
  $user_id = $_GET["user_id"];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web</title>
  <link href="./assets/plugins/dataTables-2.0.0/datatables.min.css" rel="stylesheet">
  <link href="./assets/plugins/dataTables-2.0.0/Responsive-3.0.0/css/responsive.dataTables.min.css" rel="stylesheet">
  <link href="./assets/plugins/dataTables-2.0.0/Buttons-3.0.0/css/buttons.dataTables.min.css" rel="stylesheet">
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
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <h1 class="fw-bolder mt-1 mb-3">Project ID: <?php echo $user_id;?></h1>
        </div>
      </div>
      <div class="row">
        <div class="col-12" align="center">
          <div id="structure_show"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <button id="seqs_download" class="btn btn-primary">Sequences Download</button>
          <button id="csv_download" class="btn btn-primary">CSV Download</button>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div id="csv_show"></div>
        </div>
      </div>
    </div>
  </main>
  <?php include './includes/footer.php';?>
  <script type="text/javascript" src="./assets/plugins/jsmol-16.1.47/JSmol.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/datatables.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/Responsive-3.0.0/js/dataTables.responsive.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/Responsive-3.0.0/js/responsive.dataTables.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/Buttons-3.0.0/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/Buttons-3.0.0/js/buttons.dataTables.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/Buttons-3.0.0/js/buttons.colVis.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript">
    const user_id = '<?php echo $user_id;?>';
    function init_jsmol(){
      Jmol._isAsync = false;
      let info_1 = {
        height: 600,
        width: 1400,
        color : "0xFFFFFF",
        use: "HTML5",
        j2sPath: "./assets/plugins/jsmol-16.1.47/j2s",
        serverURL: "./assets/plugins/jsmol-16.1.47/php/jsmol.php",
        zIndexBase: 0
      };
      $("#structure_show").html(Jmol.getAppletHtml("jmol_1", info_1));
    }
    function init_load(){
      $.ajax({
        url: "./user_data/"+user_id+"/eval_vis_output/predictions_with_good_interpae.csv",
        dataType: "text",
        success:function(data){
          const csv_data = data.split(/\r?\n|\r/);
          if(csv_data.length>2){
            const colors = get_color(csv_data.length-1);
            csv_to_jsmol_load(csv_data, colors);
            csv_to_table(csv_data, colors);
            ch_model_act();
          }
        },
        error:function(xhr){
          console.log(xhr);
        }
      });
    }
    function get_color(n) {
      const colors = [];
      for (let i = 0; i < n; i++) {
        const hue = i / n;
        const saturation = 0.9;
        const lightness = 0.6;
        colors.push(HSVtoRGB(hue, saturation, lightness));
      }
      return colors;
    }
    function HSVtoRGB(h, s, v) {
      let r, g, b, i, f, p, q, t;
      i = Math.floor(h * 6);
      f = h * 6 - i;
      p = v * (1 - s);
      q = v * (1 - f * s);
      t = v * (1 - (1 - f) * s);
      switch (i % 6) {
        case 0: r = v, g = t, b = p; break;
        case 1: r = q, g = v, b = p; break;
        case 2: r = p, g = v, b = t; break;
        case 3: r = p, g = q, b = v; break;
        case 4: r = t, g = p, b = v; break;
        case 5: r = v, g = p, b = q; break;
      }
      return '#' + ((1 << 24) + (Math.round(r * 255) << 16) + (Math.round(g * 255) << 8) + Math.round(b * 255)).toString(16).slice(1);
    }
    function csv_to_jsmol_load(csv_data, colors){
      let row_data;
      let pdb_files = [];
      for(let i=1;i<csv_data.length-1;i++){
        row_data = csv_data[i].split(",");
        pdb_files[i-1] = "./user_data/"+user_id+"/eval_vis_output/align_pdb/"+row_data[0]+".pdb";
      }
      Jmol.script(jmol_1, 'load files "'+pdb_files.join('" "')+'";');
      Jmol.script(jmol_1, 'select protein;cartoon only;');
      for(let i=1;i<csv_data.length-1;i++){
        Jmol.script(jmol_1, 'color {model='+i+'.1} "'+colors[i]+'";');
        bait_chain = csv_data[i].split(",")[2].split("_")[1];
        Jmol.script(jmol_1, 'select {model='+i+'.1 and chain="'+bait_chain+'"}; color "'+colors[0]+'";');
      }
      Jmol.script(jmol_1, 'frame all;');
    }
    function csv_to_table(csv_data, colors){
      let row_data;
      let table_data;
      if(csv_data.length>2){
        row_data = csv_data[0].split(",");
        table_data = "<table id='predictions_with_good_interpae' class='display'><thead><tr>";
        table_data += "<th>show</th>";
        for(let j=0;j<row_data.length;j++){
          table_data += "<th>"+row_data[j]+"</th>";
        }
        table_data += "</tr></thead><tbody>";
        for(let i=1;i<csv_data.length-1;i++){
          table_data += "<tr>";
          table_data += "<td>\
          <input type='checkbox' name='ch_model[]' value='"+i+".1' checked>\
          <div style='display:inline-block;width:10px;height:10px;background-color:"+colors[i]+"'></div>\
          </td>";
          row_data = csv_data[i].split(",");
          for(let j=0;j<row_data.length;j++){
            table_data += "<td>"+row_data[j]+"</td>";
          }
          table_data += "</tr>";
        }
        table_data += "</tbody></table>";
        $('#csv_show').html(table_data);
        let table = $('#predictions_with_good_interpae').DataTable({
          'responsive':true,
          'autoWidth':true,
          'colReorder':true,
          'layout':{
            'topStart':{
              'buttons':['colvis']
            }
          },
          'order':[[19, "desc"],[20, "desc"],[21, "desc"]],
          'columnDefs':[
            {
              'targets': [1],
              'render': function (data, type, row) {
                let safedataname = encodeURIComponent(data);
                return '<a href="seq_show.php?user_id='+user_id+'&seq_n='+safedataname+'" class="btn btn-primary" target="_block">'+data+'</a>';
              }
            },
            {
              'visible': false, 'target': [2,3,15,16]
            },
            {
              'targets': [19,20,21],
              'render': function (data, type, row) {
                  if (type === 'display' || type === 'filter') {
                      return parseFloat(data).toFixed(3);
                  }
                  return data;
              }
            }
          ]
        });
        //change table order
        let order = table.colReorder.order();
        for(let i = 0; i < 3; i++){
          let col = order.pop();
          order.splice(2, 0, col);
        }
        table.colReorder.order(order);
      }
    }
    function ch_model_act(){
      $('input[name="ch_model[]"]').on('change', function(){
        let show_models = $('input[name="ch_model[]"]:checked').map(function(){
          return $(this).val();
        }).get();
        Jmol.script(jmol_1, 'hide all;');
        Jmol.script(jmol_1, 'display {model=['+show_models.join(", ")+']};');
      });
    }
    function csv_download_onclick(){
      $('#csv_download').click(function(){
        let link = document.createElement("a");
        link.setAttribute("href", "./user_data/"+user_id+"/eval_vis_output/predictions_with_good_interpae.csv");
        link.setAttribute("download", "table_data.csv");
        document.body.appendChild(link);
        link.click();
      });
    }
    function seqs_download_onclick(){
      $('#seqs_download').click(function(){
        $.ajax({
          type: 'POST',
          url: "./seq_zip.php",
          data: {user_id:user_id},
          dataType: "json",
          success:function(data){
            if(data.zip_exist){
              let link = document.createElement("a");
              link.setAttribute("href", "./user_data/"+user_id+"/eval_vis_output/align_pdb.zip");
              link.setAttribute("download", "align_pdb.zip");
              document.body.appendChild(link);
              link.click();
            }
          },
          error:function(xhr){
            console.log(xhr);
          }
        });
      });
    }
    $(document).ready(function(){
      init_jsmol();
      init_load();
      csv_download_onclick();
      seqs_download_onclick();
    });
  </script>
</body>
</html>
