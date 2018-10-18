<?php  
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


$cdm_download_file = new cdm_download_file;

add_action( 'init', array($cdm_download_file, 'download'));
add_action( 'init', array($cdm_download_file, 'download_project'));

class cdm_download_file{
 
 function download_project()
    {	
	if($_GET['action'] == 'cdm_download_project'){
	global $wpdb, $current_user;
	define('WP_MEMORY_LIMIT', '1024M');
	ini_set('memory_limit','1024M');	
		
		$pid     = sanitize_text_field($_GET['id']);
		
	
		
		if(cdm_folder_permissions($pid) == 0){
		exit('You do not have folder permissions');	
		}
		if(!is_user_logged_in() ) {
		exit('Not logged in');
		}
		
		$zip         = new Zip(true);
				 
		
		
		$folders   = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where id = %d",$pid), ARRAY_A);
		
		$zip_dir = "" . SP_CDM_UPLOADS_DIR."".md5(SP_CDM_UPLOADS_DIR)."/";
		$zip_path        = '' . SP_CDM_UPLOADS_DIR_URL . '' . md5(SP_CDM_UPLOADS_DIR). '/';
	
		 $return_file = "" . preg_replace('/[^\w\d_ -]/si', '', sanitize_title($folders[0]['name'])) . "-".time().".zip";
	
	#echo "SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where id  IN(".$folders_id.") order by name ";
	#print_r($folders);
	 for ($j = 0; $j < count($folders); $j++) {
				
			 $zip->addDirectory(spdm_ajax::folder_name($folders[$j]['id']));
			 			
			  $r =  spdm_ajax::folder_files($folders[$j]['id']);
			
					 for ($i = 0; $i < count($r); $i++) {
						
						 $dir         = '' . SP_CDM_UPLOADS_DIR . '' . $r[$i]['uid'] . '/';
    						
						 do_action('cdm/zip/before_add_file',$r[$i]);
						 $zip->addFile(spdm_ajax::get_file($dir . $r[$i]['file']), spdm_ajax::folder_name($folders[$j]['id']).'/'.$r[$i]['file'], @filectime($dir . $r[$i]['file']));	
						 do_action('cdm/zip/after_add_file',$r[$i]); 
						
						 unset($dir);
						 
						
						
						 }
		 spdm_ajax::sub_folders($folders[$j]['id'],spdm_ajax::folder_name($folders[$j]['id']),$zip);				 
			
		}

		$zip->getZipData( $return_file);
die();
	}
    }
 
 
 function download(){
	global $current_user,$wpdb;
	

	 if($_GET['cdm-download-file-id']){
		 $file_id = sanitize_text_field($_GET['cdm-download-file-id']);

	ini_set('memory_limit', '1024M');



	

if ( (is_user_logged_in() && get_option('sp_cu_user_require_login_download') == 1 ) or (get_option('sp_cu_user_require_login_download') == '' or get_option('sp_cu_user_require_login_download') == 0 )){

do_action('wp_cdm_download_before');


$file_decrypt = base64_decode($file_id);
$file_arr = explode("|",$file_decrypt);

#print_r($file_arr);
$fid = $file_arr[0];
$file_date = $file_arr[1];
$file_name = $file_arr[2];
if(!is_numeric($fid)){header("HTTP/1.0 404 Not Found");die();}
if(class_exists('cdmProductivityLog')){

$cdm_log = new cdmProductivityLog;	

$cdm_log->add($fid,$current_user->ID);	

}


	$query = $wpdb->prepare("SELECT *  FROM ".$wpdb->prefix."sp_cu   where id= %d AND date = %s  AND file = %s order by date desc",sanitize_text_field($fid),sanitize_text_field($file_date),sanitize_text_field($file_name));
	
	$r = $wpdb->get_results($query, ARRAY_A);

cdm_event_log($r[0]['id'],$current_user->ID,'file',''.__('File Downloaded','sp-cdm').'');


	if($r == false or (!file_exists(''.SP_CDM_UPLOADS_DIR.''.$r[0]['uid'].'/'.$r[0]['file'].'') && !file_exists(''.SP_CDM_UPLOADS_DIR.''.$r[0]['uid'].'/'.$r[0]['file'].'.lock'))){header("HTTP/1.0 404 Not Found"); die();}

$r_rev_check = $wpdb->get_results("SELECT *  FROM ".$wpdb->prefix."sp_cu   where parent= '".$r[0]['id']."'  order by date desc", ARRAY_A);

if(count($r_rev_check) > 0 && $_GET['original'] == ''){



unset($r);

$r = $wpdb->get_results("SELECT *  FROM ".$wpdb->prefix."sp_cu   where id= '".$r_rev_check[0]['id']."'  order by date desc", ARRAY_A);

}


do_action('sp_download_file_after_query', $r);



if(get_option('sp_cu_js_redirect') == 1){

$file = ''.SP_CDM_UPLOADS_DIR_URL.''.$r[0]['uid'].'/'.$r[0]['file'].'';	

	echo '<script type="text/javascript">

<!--

window.location = "'.$file.'"

//-->

</script>';

exit;

}else{



$file = ''.SP_CDM_UPLOADS_DIR.''.$r[0]['uid'].'/'.$r[0]['file'].'';






// grab the requested file's name

$file_name = $file ;



// make sure it's a file before doing anything!

if(is_file($file_name))

{



  /*

    Do any processing you'd like here:

    1.  Increment a counter

    2.  Do something with the DB

    3.  Check user permissions

    4.  Anything you want!

  */



  // required for IE

  if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');  }



 $mime = mime_content_type($file_name);


 

 if($_GET['thumb'] == 1){

	 header('Content-Type: '.$mime); 

  readfile($file_name,filesize($filename));    // push it out

  exit(); 

 }else{
  set_time_limit(0); 

smartReadFile($file_name,basename($file_name),$mime);

exit(0);

 }

}









}

}else{

	auth_redirect();	

	
}
}
 }
}
?>