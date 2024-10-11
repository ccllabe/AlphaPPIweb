<?php
  $user_id = $_GET["user_id"];
  $seq_n = urldecode($_GET["seq_n"]);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web</title>
  <link href="./assets/plugins/dataTables-2.0.0/datatables.min.css" rel="stylesheet">
  <link href="./assets/plugins/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="./assets/plugins/pdbe-molstar-3.1.2/pdbe-molstar-3.1.2.css" rel="stylesheet">
  <style>
    body {
      padding-top: 3.5rem;
    }
  </style>
</head>
<body>
  <?php include './includes/header.php';?>
  <main style="background-color:rgb(31,34,43);">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-10 text-white">
          <h1 class="fw-bolder mt-1 mb-3">User ID: <?php echo $user_id;?></h1>
          <h1 class="fw-bolder mt-1 mb-3">Sequence Name: <?php echo $seq_n;?></h1>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-xxl-6 col-10 mb-5" style="min-width:800px;height:805px;position:relative;">
          <div id="myViewer" style="width:100%;"></div>
        </div>
        <div class="col-xxl-4 col-10 mb-5" style="min-width:350px;height:805px;">
          <div class="card bg-white" style="height:100%">
            <div id="bonds_show" class="card-body"></div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include './includes/footer.php';?>
  <script type="text/javascript" src="./assets/plugins/jquery-3.7.1/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/dataTables-2.0.0/datatables.min.js"></script>
  <script type="text/javascript" src="./assets/plugins/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.ebi.ac.uk/pdbe/pdb-component-library/js/pdbe-molstar-plugin-3.1.2.js" integrity="sha384-ESYEjSCvZE7rt+fgHCgvuOquUiLpYzfUCS3mX75lbfxmJgWoNAWGjqMa5zgGwj6C" crossorigin="anonymous"></script>
  <script type="text/javascript">window.PDBeMolstarPlugin || document.write('<script src="./assets/plugins/pdbe-molstar-3.1.2/pdbe-molstar-plugin-3.1.2.js"><\/script>')</script>
  <script type="text/javascript">
    const user_id = '<?php echo $user_id;?>';
    const seq_n = '<?php echo $seq_n;?>';
    let viewerInstance;
    function init_pdbe_molstar(){
      viewerInstance = new PDBeMolstarPlugin();
      let options = {
        customData: {
          url: "./user_data/"+user_id+"/eval_vis_output/align_pdb/"+seq_n+".pdb",
          format: "pdb"
        },
        bgColor: {r:0, g:0, b:0},
        sequencePanel: true,
        toggleControls: true,
        hideCanvasControls: ['expand'],
        landscape: true
      };
      let viewerContainer = document.getElementById('myViewer');
      viewerInstance.render(viewerContainer, options);
    }
    function init_pisa_load(){
      $.ajax({
        url: "./user_data/"+user_id+"/eval_vis_output/pi_score_outputs/"+seq_n+"/ranked_0/ranked_0_clean_pisa.xml",
        type: "HEAD",
        error: function(){
          let text = "<h2>No Interface Detected!!</h2>";
          $('#bonds_show').html(text);
        },
        success: function(){
          $.ajax({
            url: "./user_data/"+user_id+"/eval_vis_output/pi_score_outputs/"+seq_n+"/ranked_0/ranked_0_clean_pisa.xml",
            dataType: "xml",
            success:function(data){
              let $bonds = $(data).find('bond');
              bonds_xml_to_mark($bonds);
              bonds_xml_to_table($bonds);
            },
            error:function(xhr){
              console.log(xhr);
            }
          });
        }
      });
    }
    function bonds_xml_to_table($bonds){
      table_data = "<table id='clean_pisa_h-bonds_table' class='display'>";
      let $first_bond = $bonds.first();
      //alert($first_bond.parent()[0].tagName);
      table_data += "<thead><tr><th>"+$first_bond.find('chain-1').text()+"</th><th>"+$first_bond.find('chain-2').text()+"</th><th>bond_type</th><th>dist</th></tr></thead>";
      table_data += "<tbody>";
      $bonds.each(function(index, bond){
        let $bond = $(bond);
        table_data += "<tr>";
        table_data += "<td>"+$bond.find('res-1').text()+"&nbsp;"+$bond.find('seqnum-1').text()+"</td>";
        table_data += "<td>"+$bond.find('res-2').text()+"&nbsp;"+$bond.find('seqnum-2').text()+"</td>";
        table_data += "<td>"+$bond.parent()[0].tagName+"</td>";
        table_data += "<td>"+$bond.find('dist').text()+"</td>";
        table_data += "</tr>";
      });
      table_data += "</tbody></table>";
      $('#bonds_show').html(table_data);
      $('#clean_pisa_h-bonds_table').DataTable({
        'autoWidth': false,
        'scrollY': "615px",
        'layout':{
          'topStart': 'search',
          'topEnd': null,
          'bottomStart': null,
          'bottomEnd': 'paging'
        },
        'pageLength':15,
        'columnDefs':[
          {
            'targets': [3],
            'render': function (data, type, row) {
                if (type === 'display' || type === 'filter') {
                    return parseFloat(data).toFixed(2);
                }
                return data;
            }
          }
        ]
      });
    }
    function arr_add_val(arr, val){
      let found = arr.some(function(element){
        return JSON.stringify(element) === JSON.stringify(val);
      });
      if(!found){
        arr.push(val);
      }
    }
    function bonds_xml_to_mark($bonds){
      let selectColor = {r:255,g:255,b:0};
      let selectSections = [];
      $bonds.each(function(index, bond){
        let $bond = $(bond);
        arr_add_val(selectSections, {
          'struct_asym_id': $bond.find('chain-1').text(),
          'residue_number': parseInt($bond.find('seqnum-1').text()),
          'color': selectColor
        });
        arr_add_val(selectSections, {
          'struct_asym_id': $bond.find('chain-2').text(),
          'residue_number': parseInt($bond.find('seqnum-2').text()),
          'color': selectColor
        });
      });
      viewerInstance.events.loadComplete.subscribe(() => {
        viewerInstance.visual.select({ data: selectSections});
      });
    }
    $(document).ready(function(){
      init_pdbe_molstar();
      init_pisa_load();
    });
  </script>
</body>
</html>
