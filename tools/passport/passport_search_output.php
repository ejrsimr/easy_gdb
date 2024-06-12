<!-- HEADER -->
<?php include_once realpath("../../header.php");?> 

<!-- RETURN AND HELP-->
<div class="margin-20">
  <a class="float-right" href="/easy_gdb/help/01_search.php"><i class='fa fa-info' style='font-size:20px;color:#229dff'></i> Help</a>
</div>

<a href="search_input.php" class="float-left" style="text-decoration: underline;"><i class="fas fa-reply" style="color:#229dff"></i> Back to input</a>
<br>

<!-- HTML -->
<div class="page_container">


<!-- GET INPUT -->
<?php
  $raw_input = trim($_GET["search_keywords"]);
  $quoted_search = 0;
  if ( preg_match('/^".+"$/',$raw_input ) ) {
    $quoted_search = 1;
  }
?>


<!-- IS BETTER TO SET IN ANOTHER FILE -->
<?php
  function test_input2($data) {
    $data = preg_replace('/[\<\>\t\;]+/',' ',$data);
    $data = htmlspecialchars($data);
    if ( preg_match('/\s+/',$data) ) {
      $data_array = explode(' ',$data,99);
      foreach ($data_array as $key=>&$value) {
        if (strlen($value) < 3) {
            unset($data_array[$key]);
        }
      }
      $data = implode(' ',$data_array);
    }
    $data = stripslashes($data);
    return $data;
  }

  $search_input = test_input2($raw_input);
?>


<!-- SHOW INPUT -->
<?php
  echo "\n<br><h3>Search Input</h3>\n<div class=\"card bg-light\"><div class=\"card-body\">$search_input</div></div><br>\n";
?>


<!-- INCLUDE TABLE  -->
<?php
  if(empty($raw_input)) {
    echo "<h1>No words to search provided.</h1>";
  }

  else { //DECLARE THE FUNCTION  include_once realpath("search_annot_file.php");
    function print_search_table($grep_input, $annot_file, $annot_hash, $dataset_name, $table_counter) {

      echo "<div class=\"collapse_section pointer_cursor\" data-toggle=\"collapse\" data-target=\"#Annot_table_$table_counter\" aria-expanded=\"true\"><i class=\"fas fa-sort\" style=\"color:#229dff\"></i> $dataset_name</div>";
      $annot_file = str_replace(" ", "\\ ", $annot_file);

      $head_command = "head -n 1 $annot_file";
      $output_head = exec($head_command);

      $grep_command = "grep -i '$grep_input' $annot_file";
      exec($grep_command, $output);


      // TABLE BEGIN
      echo "<div id=\"Annot_table_$table_counter\" class=\"collapse show\"><div class=\"data_table_frame\"><table id=\"tblAnnotations\" class=\"tblAnnotations table table-striped table-bordered\">\n";


      // TABLE HEADER
      echo "<thead><tr>\n";
      $columns = explode("\t", $output_head);
      $col_number = count($columns);

      foreach ($columns as $col) {
        echo "<th>$col</th>\n";
      }
      echo "</tr></thead>\n";


      // TABLE BODY
      echo "<tbody>\n";

      foreach ($output as $line) {
        echo "<tr>\n";
        $data = explode("\t", $line);
        for ($n = 0; $n <= $col_number-1; $n++) {
          if ($data[$n]) {
            if ($n == 0) {
              echo "<td><a href=\"/easy_gdb/gene.php?name=$data[$n]\" target=\"_blank\">$data[$n]</a></td>\n";
            }
            else {
              $header_name = $columns[$n];
              if ($header_name == "TAIR10" || $header_name == "Araport11") {
                $query_id = preg_replace(['/query_id/', '/\.\d$/'], [$data[$n], ''], $annot_hash[$header_name]);
                echo "<td><a href=\"$query_id\" target=\"_blank\">$data[$n]</a></td>\n";
              }
              elseif (strpos($data[$n], ';') && $header_name == "InterPro") {
                $ipr_data = explode(';', $data[$n]);
                $ipr_links = '';
                foreach ($ipr_data as $ipr_id) {
                  $query_id = str_replace('query_id', $ipr_id, $annot_hash[$header_name]);
                  $ipr_links .= "<a href=\"$query_id\" target=\"_blank\">$ipr_id</a>;<br>";
                }
                $ipr_links = rtrim($ipr_links, ';<br>');
                echo "<td>$ipr_links</td>\n";
              }
              elseif (strpos($data[$n], ';') && $header_name == "Description") {
                $data_semicolon = str_replace(';', ';'."<br>", $data[$n]);
                echo "<td>$data_semicolon</td>\n";
              }
              elseif ($annot_hash[$header_name]) {
                $query_id = str_replace('query_id', $data[$n], $annot_hash[$header_name]);
                echo "<td><a href=\"$query_id\" target=\"_blank\">$data[$n]</a></td>\n";
              }
              else {
                echo "<td>$data[$n]</td>\n";
              }
            }
          }
          else {
            echo "<td></td>\n";
          }
        }
        echo "</tr>\n";
      }
      echo "</tbody></table></div></div><br>\n";
      $output = [];
    } // TABLE END


    // HASH ANNOTATION
    if (file_exists("$annotation_links_path/annotation_links.json")) {
      $annot_json_file = file_get_contents("$annotation_links_path/annotation_links.json");
      $annotation_hash = json_decode($annot_json_file, true);
    }


    // QUOTED INPUTS
    if ($quoted_search) {
      $search_query = preg_replace('/[\"\<\>\t\;]+/','',strtolower($raw_input) );
    }
    elseif (preg_match('/\s+/', $search_input)) {
      $search_query = preg_replace('/\s+/','\|',strtolower($search_input) );
    }
    else {
      $search_query = strtolower($search_input);
    }


    // COMMANDS AND PRINT
    $table_counter = 1;

    if ($_GET['sample_names']) {
      foreach ($_GET['sample_names'] as $sample) {
        list($annot_file,$dataset_name) = explode("@", $sample);
        print_search_table($search_query, $annot_file, $annotation_hash, $dataset_name, $table_counter);
        $table_counter++;
      }
    } else {
      include_once realpath("$easy_gdb_path/tools/common_functions.php");
      $all_datasets = get_dir_and_files($annotations_path);
      $annot_file = $annotations_path."/".$all_datasets[0];
      $dataset_name = $all_datasets[0];
      $dataset_name = preg_replace('/\.[a-z]{3}$/',"",$all_datasets[0]);
      $dataset_name = str_replace("_"," ",$dataset_name);
      print_search_table($search_query, $annot_file, $annotation_hash, $dataset_name, $table_counter);
    }
  }
?>
<!-- END TABLE  -->


<br>
<br>
</div>
<!-- END HTML -->


<!-- JS DATATABLE -->
<script type="text/javascript">
  $(".tblAnnotations").dataTable({
    dom:'Bfrtlpi',
    "oLanguage": {
      "sSearch": "Filter by:"
      },
    buttons: [
      'copy', 'csv', 'excel',
        {
          extend: 'pdf',
          orientation: 'landscape',
          pageSize: 'LEGAL'
        },
      'print', 'colvis'
      ]
    });

$(".dataTables_filter").addClass("float-right");
$(".dataTables_info").addClass("float-left");
$(".dataTables_paginate").addClass("float-right");

</script>

<!-- FOOTER -->
<?php include_once realpath("$easy_gdb_path/footer.php");?>