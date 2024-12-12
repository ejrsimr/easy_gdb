<?php

$passport_path_file = $_POST["passport_path"];
$all_passport_array= array();


if ( file_exists($passport_path_file."/passport.json") ) {
$pass_json_file = file_get_contents($passport_path_file."/passport.json");
$pass_hash = json_decode($pass_json_file, true);
// print_r($pass_hash);

$passport_file = $pass_hash["passport_file"];
$phenotype_file_array = $pass_hash["phenotype_files"];
// $unique_link = $pass_hash["acc_link"];


if ( !preg_match('/\.php$/i', $passport_file) && !is_dir($passport_path_file.'/'.$passport_file) &&  !preg_match('/\.json$/i', $passport_file) && file_exists($passport_path_file.'/'.$passport_file)   ) {

// echo "unique_link: $unique_link<br>";
$one_passport_array=read_passport_file($passport_path_file,$passport_file);
// array_push($all_passport_array,read_passport_file($passport_path_file,$passport_file));
$all_passport_array=array_merge($all_passport_array,$one_passport_array);

foreach ($phenotype_file_array as $phenotype_file) {
  $phenotype_passport_array=read_passport_file($passport_path_file,$phenotype_file);
  $all_passport_array=array_merge($all_passport_array,$phenotype_passport_array);
}
//
}//if preg_match
echo (json_encode($all_passport_array));

}//foreach all_dir


// $n_passport_files=[];

function read_passport_file($passport_path,$passport_file) {
  
  $passport_array = array();

  $dataset_name = preg_replace('/\.[a-z]{3}$/',"",$passport_file);
  $dataset_name = str_replace("_"," ",$dataset_name);
  $frame_id = preg_replace('/[. ]txt/',"",$passport_file);
  
  
  if (file_exists("$passport_path/$passport_file")) {
       
    array_push($passport_array,"<div class=\"collapse_section pointer_cursor\" data-toggle=\"collapse\" data-target=\"#collapse_$frame_id\" aria-expanded=\"true\">");
    array_push($passport_array,"<i class=\"fas fa-sort\" style=\"color:#229dff\"></i> $dataset_name </div>");


    array_push($passport_array,"<div id=\"collapse_$frame_id\" class=\"hide collapse\" style=\" border-radius: 5px; border:solid 1px; background-color:#efefef; padding-top:7px\">");
    // array_push($GLOBALS['n_passport_files'],$frame_id);
    
    $pass_array = file("$passport_path/$passport_file");
    // $pass_array = explode("\n", $pass_array);
    $header = array_shift($pass_array);
    $header_array = explode("\t", $header);
    
    // echo "passport header: $header";
    
    $no_spc_file = str_replace(" ","\ ","$passport_path/$passport_file");
      

    array_push($passport_array,"<form id=\"passport_form\" action=\"passport_search_output_avanced.php\" method=\"post\">");
      array_push($passport_array,"<div class=\"container\" style=\"margin-left:20px\">");
          array_push($passport_array,"<div class=\"row\">");
          array_push($passport_array, "<div class=\"col\">");
              array_push($passport_array,"<label for=\"select_$frame_id\" style=\"margin-left:15px;margin-right:15px \"><i>Filter by: </i></label>");
              array_push($passport_array,"<select class=\"form-control sel_opt\" id=\"$frame_id\" name=\"$no_spc_file\" style=\"width:auto; display: inline-block;\">");
              // echo "<option selected></option>";
              foreach ($header_array as $index => $value) {
                // if ($value != $acc_header_name) {
                  array_push($passport_array,"<option name=\"$index\">$value</option>");
                // }
              }
              array_push($passport_array,"</select>");

           array_push($passport_array,"</div>");
           array_push($passport_array,"<div class=\"col\">");
            array_push($passport_array,"<label for=\"text_$frame_id\"  style=\"margin-left:45px;margin-top:10px\"><i>Added filter:</i></label>");
          array_push($passport_array,"</div>");
          array_push($passport_array,"</div>");

          array_push($passport_array,"<div class=\"d-flex\" style=\"display: inline-block;margin:10px\">");
            array_push($passport_array,"<select multiple id=\"select_$frame_id\" size=\"11\" class=\"form-control select\"></select>");
            array_push($passport_array,"<input id=\"numeric_input_$frame_id\" type=\"number\" class=\"form-control\" name=\"\" style=\"height:50px;display:none; background-color:#ffff; margin-left: 20px\" placeholder=\"0\">");
      
            array_push($passport_array,"<div id=\"button_$frame_id\" style=\"margin:10px;margin-top:60px;width:20%; text-align: center\">");
            array_push($passport_array,"<button class=\"btn btn-success add\" style=\"width:90%;height:20%;font-size:small\">Add <span class=\"fas fa-angle-double-right\"></span></button><br>");
            array_push($passport_array,"<button class=\"btn btn-danger delete\" style=\"margin-top:40px; width:90%;height:20%; font-size:small\"><span class=\"fas fa-angle-double-left\"></span> Quit</button>");
            array_push($passport_array,"</div>");

          array_push($passport_array,"<textarea id=\"text_$frame_id\" class=\"form-control\" name=\"filters\" rows=\"10\" cols=\"5\" readonly=\"true\" wrap=\"hard\" style=\"background-color:#ffff;resize: none\"></textarea>"); 
          array_push($passport_array,"</div>"); // col
          array_push($passport_array,"</div>");

      array_push($passport_array,"<div style=\"display: flex; justify-content: flex-end;\">");
      array_push($passport_array,"<button id=\"search_$frame_id\" name=\"file\" value=\"$frame_id\" type=\"submit\" class=\"btn btn-info search_button\" style=\"margin:10px; width:95px\"><span class=\"fas fa-search\"></span> Search</button>");
      array_push($passport_array,"<input name=\"passport\" value=\"$passport_path\" style=\"display:none\"/></div>");
      array_push($passport_array,"</div>");
   array_push($passport_array,"</form>"); 

  } // if file exist
  return($passport_array);
}
?>

