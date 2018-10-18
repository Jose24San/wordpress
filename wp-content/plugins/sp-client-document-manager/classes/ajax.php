<?php




class spdm_ajax
{
	
	
	function order_by(){
		
		$orderby = 'name';
		
		$orderby = apply_filters('sp_cdm_order_by_ajax',$orderby);
		
		return $orderby ;
	}
	
	function project_dropdown(){
		
		if(class_exists('spdm_sub_projects')){
		echo spdm_sub_projects::project_dropdown_replace();
		}else{
		echo sp_cdm_replace_project_select();	
		}
		
		
		
	}
    function view_file()
    {
		
        
		global $wpdb, $current_user, $cdm_comments, $cdm_google, $cdm_log;

		
	

        $file_types = array();
		$r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where id = %d order by date desc", sanitize_text_field($_GET['id'])), ARRAY_A);
		if(cdm_folder_permissions($r[0]['pid']) or $current_user->ID == $r[0]['uid'])
		
			$ext = substr(strrchr($r[0]['file'], '.'), 1);
			$stream_file_types = get_option('sp_cu_stream_file_types');
		if($stream_file_types != ''){
		$file_types = explode(",",$stream_file_types);	
		}
        $html .= '<div id="view_file_refresh">

		



	';
        $html .= '<div class="sp_cu_manage">';
        
		$html = apply_filters('sp_cdm_view_file_first_add_button',$html,$r);
		
		
        if (class_exists('cdmProductivityUser')) {
            $html .= '<span id="cdm_comment_button_holder">' . $cdm_comments->button() . '</span>';
        }
		
		$html = apply_filters('sp_cdm_view_file_add_button',$html,$r);
		
        $html = apply_filters('cdm/viewfile/top_navigation',$html,$r);
	   
	   
        if (get_option('sp_cu_js_redirect') == 1 or in_array($ext,$file_types)) {
            $target = 'target="_blank"';
        } else {
            $target = ' ';
        }
       
	   
	   
	    $download_url = '<a '. cdm_download_file_link(base64_encode($r[0]['id'].'|'.$r[0]['date'].'|'.$r[0]['file']),get_option('sp_cu_js_redirect'),$ext,$file_types).'   title="Download"   ><span class="dashicons 
dashicons-arrow-down-alt cdm-dashicons"></span> ' . __("Download File", "sp-cdm") . '</a> ';
		$html .= apply_filters('sp_cdm_viewfile_download_url', $download_url, $r);
		
		
		
        if ( cdm_user_can_delete($current_user->ID) == true && cdm_delete_permission($r[0]['pid']) == 1) {
           $delete_button = '

	<a href="javascript:sp_cu_confirm_delete(\'' . get_option('sp_cu_delete') . '\',200,\''.$r[0]['id'].'\');" title="Delete" class="sp-cdm-delete-file" ><span class="dashicons dashicons-no cdm-dashicons"></span> ' . __("Delete File", "sp-cdm") . '</a>';
	  $html .= apply_filters('sp_cdm_viewfile_delete_button', $delete_button,$r);
        }
		
		
		
	if(sp_cdm_is_featured_disabled('base', 'view_file_date_id') == false){	
        $html .= '
<div style="clear:both"></div>
	 <em>' . date('F jS Y h:i A', strtotime($r[0]['date'])) . ' &bull; File ID: #' . $r[0]['id'] . ' <br><a href="#" class="cdm_refresh_file_view" data-id="' . $r[0]['id'] . '"><span class="dashicons dashicons-image-rotate cdm-dashicons"></span> '.__('Refresh','sp-cdm').'</a></em>

				</div>';
	}
        $html .= '
<script type="text/javascript">


		if(jQuery.cookie("viewfile_tab")){
			
		var active_tab = jQuery.cookie("viewfile_tab"); 
			
		}else{
			
		var active_tab = 0;	
		}
		console.log(active_tab);

jQuery(".viewFileTabs").responsiveTabs({
 startCollapsed: false,
 active: active_tab,
 activateState: function(msg){

			
	 }
});
jQuery(function($) {
	$( ".viewFileTabs li a").on( "click", function() {
	 $.cookie("viewfile_tab", $(this).parent().index(), { expires: 7 , path:"/" }); 
		console.log($(this).parent().index())
	});
});
</script>';


	$html = apply_filters('cdm/viewfile/under_date',$html,$r);




		$html .='<div class="viewFileTabs">

	<ul>

		<li><a href="#cdm-file-main">'.__("File Info","sp-cdm").'</a></li>';
		$html = apply_filters('sp_cdm_view_file_after_file_info_tab',$html,$r);
		
        if (function_exists('sp_cdm_revision_add') && get_option('sp_cu_user_disable_revisions') != 1) {
            if($r[0]['form_id'] == '' or $r[0]['form_id'] == 0){
			$html .= '<li><a href="#cdm-file-revisions">'.__("Revisions","sp-cdm").'</a></li>';
			}
        }
        if (class_exists('cdmProductivityUser')) {
            $html .= '<li><a href="#cdm-file-comments">'.__("Comments","sp-cdm").'</a></li>';
        }
        if (class_exists('cdmProductivityLog')) {
			if((get_option('sp_cu_log_admin_only') == 1 && current_user_can('manage_options') )
	or (get_option('sp_cu_log_admin_only') == 0 or get_option('sp_cu_log_admin_only') == '')
	){
            $html .= '<li><a href="#cdm-file-log">'.__("Download Log","sp-cdm").'</a></li>';
	}
        }
		
		 if( sp_cdm_is_featured_disabled('base', 'event_logger') == false){
            $html .= '<li><a href="#cdm-events-log">'.__("Events Log","sp-cdm").'</a></li>';
        }
		
			$html = apply_filters('sp_cdm_view_file_tab',$html,$r);
		
        $html .= '</ul>

	';
		$html = apply_filters('sp_cdm_view_file_content',$html,$r);
	
        if (class_exists('cdmProductivityUser')) {
            $html .= '<div id="cdm-file-comments"><div id="cdm_comments_container">' . $cdm_comments->view($r[0]['id']) . '</div></div>';
        }
        if (class_exists('cdmProductivityLog')) {
            $html .= '<div id="cdm-file-log">' . $cdm_log->view($r[0]['id']) . '</div>';
        }
		if( sp_cdm_is_featured_disabled('base', 'event_logger') == false){
            $html .= '<div id="cdm-events-log">'.cdm_get_event_log($r[0]['id'],'file',250).'</div>';
        }
		
        $html .= '<div id="cdm-file-main">';
        if (get_option('sp_cu_wp_folder') == '') {
            $wp_con_folder = '/';
        } else {
            $wp_con_folder = get_option('sp_cu_wp_folder');
        }
        //print_r($r);
        $ext = substr(strrchr($r[0]['file'], '.'), 1);
        if ($r[0]['pid'] != 0) {
            $projecter     = $wpdb->get_results("SELECT *

	

									 FROM " . $wpdb->prefix . "sp_cu_project

									 WHERE id = '" . $r[0]['pid'] . "'

									 ", ARRAY_A);
            $project_title = '' . stripslashes($projecter[0]['name']) . '';
        } else {
            $project_title = '' . __("None", "sp-cdm") . '';
        }
        if ($ext == 'png' or $ext == 'jpg' or $ext = 'jpeg' or $ext = 'gif') {
            $icon = '<td width="160"><img src="' . SP_CDM_UPLOADS_DIR_URL . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '" width="150"></td>';
        } else {
            $icon = '';
        }
        $ext        = preg_replace('/^.*\./', '', $r[0]['file']);
        $images_arr = array(
            "jpg",
            "png",
            "jpeg",
            "gif",
            "bmp"
        );
      
	  
	  
			if(get_option('sp_cu_user_projects_thumbs_pdf') == 1 && class_exists('imagick')){
	
			$info = new Imagick();
			$formats = $info->queryFormats();
			
			}else{
				$formats = array();
			}
	  
	  
	  
	    if (in_array(strtolower($ext), $images_arr)) {
            if (get_option('sp_cu_overide_upload_path') != '' && get_option('sp_cu_overide_upload_url') == '') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/package_labled.png">';
            } else {
				
                $img = '<img src="' . sp_cdm_thumbnail('' . SP_CDM_UPLOADS_DIR_URL . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '', 250) . '">';
            }
        } elseif ($ext == 'xls' or $ext == 'xlsx') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_excel.png">';
        } elseif ($ext == 'doc' or $ext == 'docx') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_word.png">';
        } elseif ($ext == 'pub' or $ext == 'pubx') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_publisher.png">';
        } elseif ($ext == 'ppt' or $ext == 'pptx') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_powerpoint.png">';
        } elseif ($ext == 'adb' or $ext == 'accdb') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_access.png">';
        } elseif (in_array(strtoupper($ext),$formats)) {
            if (file_exists('' . SP_CDM_UPLOADS_DIR . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '_big.png')) {
                $img = '<img src="' . SP_CDM_UPLOADS_DIR_URL . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '_big.png" width="250">';
            } else {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/adobe.png">';
            }
        } elseif ($ext == 'pdf' or $ext == 'xod') {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/adobe.png">';
        } else {
            $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/package_labled.png">';
        }
		
		$img = apply_filters('sp_cdm_viewfile_image', $img,$r[0]);
        $file_info .= '

				

				<div id="sp_cu_viewfile">

				

				

				

				<div class="sp_cu_item">

				

		<div class="cdm-two-column"><div class="l-column">';
		
		$info_left_column .= '
<a ' . $target . ' '. cdm_download_file_link(base64_encode($r[0]['id'].'|'.$r[0]['date'].'|'.$r[0]['file']),get_option('sp_cu_js_redirect'),$ext,$file_types).' title="Download" style="margin-right:15px"  >


' . $img . '

</a>

';

		
  $info_left_column = apply_filters('sp_cdm_viewfile_replace_file_info', $info_left_column, $r);
  $file_info .=$info_left_column;
  

 $file_info .= '</div><div class="r-column">';
	if(sp_cdm_is_featured_disabled('base', 'view_file_name') == false){
	$info_right_column .= '<div class="sp_su_project">
	
	<strong>' . __("File Name", "sp-cdm") . ': </strong> ' . stripslashes($r[0]['name']) . '<br>
	
	
	</div>';
	}
	if(sp_cdm_is_featured_disabled('base', 'view_file_owner') == false){
	
		if($r[0]['uid'] == $current_user->ID){
		$info_right_column .= '<div class="sp_su_project">
		
		<strong>' . __("File Owner", "sp-cdm") . ': </strong> '.__('You own this file','sp-cdm').'<br>
		
		
		</div>';	
		}else{
			$owner = get_userdata( $r[0]['uid']); 
		$info_right_column .= '<div class="sp_su_project">
		
		<strong>' . __("File Owner", "sp-cdm") . ': </strong> '.$owner->display_name .'<br>
		
		
		</div>';		
		}
	}
	if(sp_cdm_is_featured_disabled('base', 'view_folder_name') == false){
	$info_right_column .= '<div class="sp_su_project">
	
	<strong>' .sp_cdm_folder_name()  . ': </strong>' . $project_title . '
	
	</div>';
	}
	if(sp_cdm_is_featured_disabled('base', 'view_file_type') == false){
	$info_right_column .= '<div class="sp_su_project">
	
	<strong>' . __("File Type ", "sp-cdm") . ': </strong>' . $ext . '
	
	</div>';
	}
	if(sp_cdm_is_featured_disabled('base', 'view_file_size') == false){
		$info_right_column .= '<div class="sp_su_project">
		
		<strong>' . __("File Size ", "sp-cdm") . ': </strong>' . cdm_file_size(''.SP_CDM_UPLOADS_DIR . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '') . ' 
		
		</div>
		';
	}
$extra_file_info = '';
$info_right_column  .= apply_filters('sp_cdm_file_view_info', $extra_file_info,$r[0]);


  if (CU_PREMIUM == 1) {
	 
	 if($r[0]['cid'] != '' && $r[0]['cid'] != 0){
		 
	    
	
	 }	
	  
  }
        if ($r[0]['tags'] != "") {
           $info_right_column .= '

<div class="sp_su_notes">

<strong>' . __("Tags ", "sp-cdm") . ': </strong> ' . stripslashes($r[0]['tags']) . '

</div>';
        }
		
		if ($r[0]['notes'] != "") {
           $info_right_column .= '

<div class="sp_su_notes">

<strong>' . __("Notes ", "sp-cdm") . ': </strong> ' . stripslashes($r[0]['notes']) . '

</div>';
        }
		
        if (CU_PREMIUM == 1) {
         
				 if(sp_cdm_get_form_fields($r[0]['id']) != ''){
				  $info_right_column  .= '
		
		<div class="sp_su_notes">
		
		' . sp_cdm_get_form_fields($r[0]['id']) . '
		
		</div>';
				 }
        } 
       
	   
	   $info_right_column = apply_filters('sp_cdm_view_file_notes',$info_right_column,$r);
	   $file_info .=$info_right_column;
	    $file_info .= '

	




</div><div style="clear:both"></div>

  </div></div>';
  
  $file_info = apply_filters('sp_cdm_viewfile_replace_file_infos', $file_info, $r,$info_left_column,$info_right_column);
  $html .= $file_info;
  
  $html .='</div></div>

  

 

  </div>

  

  

  

  </div>

  ';
  		$html = apply_filters('sp_cdm_viewfile', $html,$r);
        return $html;
    }
    function delete_file()
    {
		
        global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
		$file_id = sanitize_text_field($_POST['file_id']);
		if($file_id != NULL){
		$file_id = $file_id;	
		}else{
		$file_id = sanitize_text_field($_GET['dlg-delete-file']);	
		}
        $r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where id = %d  order by date desc",$file_id), ARRAY_A);
       
	   
	    if ((($current_user->ID == $r[0]['uid'] or cdmFindLockedGroup($current_user->ID, $r[0]['uid']) == true) && get_option('sp_cu_user_delete_disable') != 1) or current_user_can('manage_options')) {
            $wpdb->query($wpdb->prepare("
	DELETE FROM " . $wpdb->prefix . "sp_cu WHERE id = %d ",$file_id)

	);
            unlink('' . SP_CDM_UPLOADS_DIR . '' . $r[0]['uid'] . '/' . $r[0]['file'] . '');
			        $ext        = preg_replace('/^.*\./', '', $r[0]['file']);
					$small = '' . SP_CDM_UPLOADS_DIR . '' . $r[0]['uid'] . '/'.$r[0]['file'].'_small.png';
					$big = '' . SP_CDM_UPLOADS_DIR . '' . $r[0]['uid'] . '/'.$r[0]['file'].'_big.png';
			@unlink($small);
			@unlink($big);
			sp_cdm_user_logs::write('Deleted file: '.$r[0]['name'].'');
			do_action('sp_cdm_delete_file',  $r[0]); 
        }
    }
    function get_file_info()
    {
        global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where id = %d",  sanitize_text_field($_GET['id'])), ARRAY_A);
        return str_replace(array(
            '[',
            ']'
        ), '', htmlspecialchars(json_encode($r[0]), ENT_NOQUOTES));
    }
	

    function remove_cat($project_id= NULL)
    {
        global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
		if($project_id != NULL){
		$project_id = $project_id;	
		}else{
		$project_id = sanitize_text_field($_REQUEST['id']);	
		}
         $r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project   where id = %d ",$project_id), ARRAY_A);
       
	   
		if ((($current_user->ID == $r[0]['uid'] or cdmFindLockedGroup($current_user->ID, $r[0]['uid']) == true) && get_option('sp_cu_user_delete_disable') != 1) or current_user_can('manage_options') or cdm_folder_permissions($project_id) == 1) {
		  
					
     
				 
					  #delete this projects files
						$f = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu where pid = %d ",$project_id), ARRAY_A);
							
							for ($j = 0; $j < count($f); $j++) {
							
								$this->delete_file($f[$j]['id']);
								
								#$this->remove_cat($id);
							}
					
						#find and remove sub folders
						$p = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where parent = %d ",$project_id), ARRAY_A);
						for ($i = 0; $i < count($p); $i++) {
							
							$this->remove_cat($p[$i]['id']);
						}
					#delete the project
					sp_cdm_user_logs::write('Deleted folder: '.$r[0]['name'].'');
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "sp_cu_project WHERE id = %d",$project_id ));							
						
				
		}else{
		sp_cdm_user_logs::write('Error: Failed removing folder: '.$r[0]['name'].'');	
		}
    }
    function save_cat()
    {
		
        global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
        $insert['name'] = sanitize_text_field($_POST['name']);
        if ($_POST['id'] != "") {
            $where['id'] = sanitize_text_field($_POST['id']);
            $wpdb->update("" . $wpdb->prefix . "sp_cu_project", $insert, $where);
            echo '' . __("Updated Category Name", "sp-cdm") . ': ' . $insert['name'] . '';
            exit;
        } else {
            $insert['uid']    = sanitize_text_field($_POST['uid']);
            $insert['parent'] = sanitize_text_field($_POST['parent']);
           foreach($insert as $key=>$value){ if(is_null($value)){ unset($insert[$key]); } }
		    $wpdb->insert("" . $wpdb->prefix . "sp_cu_project", $insert);
            echo $wpdb->insert_id;
            exit;
        }
        echo 'Error!';
    }
    function file_list()
    {
        global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
         if (function_exists('cdmFindGroups')) {
            $find_groups = cdmFindGroups(sanitize_text_field($_GET['uid']), 1);
        }
		
		
		
		
		
       
        if ($_REQUEST['search'] != "") {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.name LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%' ";
        }else{
        if ($_GET['pid'] == '' or $_GET['pid'] == 'undefined') {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.parent = '0' ";
        } else {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.parent = '" .sanitize_text_field($_GET['pid'] ). "' ";
        }
		}
          if (get_option('sp_cu_hide_project') == 1) {
			
			
			$r_projects_query = "SELECT " . $wpdb->prefix . "sp_cu.name,

												 " . $wpdb->prefix . "sp_cu.id,

												 " . $wpdb->prefix . "sp_cu.pid ,

												 " . $wpdb->prefix . "sp_cu.uid,

												 " . $wpdb->prefix . "sp_cu.parent,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												 " . $wpdb->prefix . "sp_cu_project.parent

												 

										FROM " . $wpdb->prefix . "sp_cu   

										LEFT JOIN " . $wpdb->prefix . "sp_cu_project  ON " . $wpdb->prefix . "sp_cu.pid = " . $wpdb->prefix . "sp_cu_project.id

										WHERE (" . $wpdb->prefix . "sp_cu.uid = '" . sanitize_text_field($_GET['uid']) . "'  " . $find_groups . ")

										AND pid != 0

										AND  " . $wpdb->prefix . "sp_cu.parent = 0 

										" . $sub_projects . "";
										
								if($_GET['pid'] == 0 or $_GET['pid'] == ''){
									$r_projects_query = apply_filters('sp_cdm_projects_query', $r_projects_query ,sanitize_text_field($_GET['uid']));	
										}
									
									$r_projects_query .="	" . $search_project . "
										
										GROUP BY pid

										ORDER by date desc";
				if(get_option('sp_cu_release_the_kraken') == 1){
								unset($r_projects_query);								
								$r_projects_query =	 "SELECT 										 
													" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent
										FROM " . $wpdb->prefix . "sp_cu_project
										WHERE id != ''
										
										" . $search_project . " ORDER by name
";
								}
			
            $r_projects = $wpdb->get_results($r_projects_query, ARRAY_A);
        } else {
			
			
									$r_projects_groups_addon = apply_filters('sp_cdm_projects_query', $r_projects_groups_addon ,sanitize_text_field($_GET['uid']));	
					
			$r_projects_query = "SELECT 

												" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent

												 

										FROM " . $wpdb->prefix . "sp_cu_project

										WHERE (" . $wpdb->prefix . "sp_cu_project.uid = '" . sanitize_text_field($_GET['uid']) . "'  " . $find_groups . " ".$r_projects_groups_addon.")										

										

										" . $search_project . "

										";
									
										$r_projects_query .="

										ORDER by name";
							
						
							
		if(get_option('sp_cu_release_the_kraken') == 1){
								unset($r_projects_query);								
								$r_projects_query =	 "SELECT 										 
													" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent
										FROM " . $wpdb->prefix . "sp_cu_project
										WHERE id != ''
										
										" . $search_project . " ORDER by name
";
								}
								
			 $r_projects_query = apply_filters('sp_cdm_project_query_final', $r_projects_query);					
            $r_projects = $wpdb->get_results($r_projects_query, ARRAY_A);
        }
        echo '<div id="dlg_cdm_file_list">

		<table border="0" cellpadding="0" cellspacing="0">

		<thead>';
        if ($_GET['pid'] == '') {
            $jscriptpid = "''";
        } else {
            $jscriptpid = "'" . sanitize_text_field($_GET['pid']) . "'";
        }
        echo '<tr>';
		
		do_action('spdm_file_list_column_before_sort');

		echo '<th></th>

		<th class="cdm_file_info" style="text-align:left"><a href="javascript:sp_cdm_sort(\'name\',' . $jscriptpid . ')">' . __("Name", "sp-cdm") . '</a></th>

		<th class="cdm_file_date"><a href="javascript:sp_cdm_sort(\'date\',' . $jscriptpid . ')">' . __("Date", "sp-cdm") . '</a></th>

	

		<th class="cdm_file_type">' . __("Type", "sp-cdm") . '</th>	

		</tr>	

		

		';
		
		
		
        if (($_GET['pid'] != "0" && $_GET['pid'] != '') && ((get_option('sp_cu_user_projects') == 1 and get_option('sp_cu_user_projects_modify') != 1) or current_user_can('manage_options'))) {
            $r_project_info = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sp_cu_project where id = " .sanitize_text_field( $_GET['pid'] ). "", ARRAY_A);
          
		  if($r_project_info[0]['uid'] == $_GET['uid']){
		    echo '<tr>

	

		<th colspan="100%" style="text-align:right">

		<div style="padding-right:10px">';

	echo'<a href="javascript:sp_cu_dialog(\'#edit_category_' . sanitize_text_field($_GET['pid']) . '\',550,130)"><img src="' . SP_CDM_PLUGIN_URL . 'images/application_edit.png"> '. __("Edit", "sp-cdm").' '.sp_cdm_folder_name() .' '. __("Name", "sp-cdm").'</a>   
	<a href="#" class="sp-cdm-delete-category" data-id="' . sanitize_text_field($_GET['pid']) . '" style="margin-left:20px"> <img src="' . SP_CDM_PLUGIN_URL . 'images/delete_small.png">  '. __("Remove", "sp-cdm").' '.sp_cdm_folder_name().'</a>';
	
	
	do_action('cdm/ajax/folder/navigation', sanitize_text_field($_GET['pid']));
		
		

		echo'<div style="display:none">	

		

		

		

		<div id="delete_category_' . sanitize_text_field($_GET['pid']) . '" title="' . __("Delete Category?", "sp-cdm") . '">

	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' . __("Are you sure you would like to delete this category? Doing so will remove all files related to this category.", "sp-cdm") . '</p>

		</div>



		

		

				<div id="edit_category_' . sanitize_text_field($_GET['pid']) . '">			

			

			'.sp_cdm_folder_name() .' ' . __("Name", "sp-cdm") . ': <input value="' . stripslashes($r_project_info[0]['name']) . '" id="edit_project_name_' . sanitize_text_field($_GET['pid']) . '" type="text" name="name"  style="width:200px !important"> 

			<input type="submit" value="' . __("Save", "sp-cdm") . ' '.sp_cdm_folder_name() .'" class="sp-cdm-save-category" data-id="'.sanitize_text_field($_GET['pid']).'">

			

			</div>

			

		

		

		</div>

		

		

		</th>

		

		</tr>	

		

		';
		  }
        }
        echo '</thead><tbody>';
        if ($_GET['pid'] != 0) {
            $query_project = $wpdb->get_results("SELECT *

	

									

									 FROM " . $wpdb->prefix . "sp_cu_project

									WHERE  id = '" . sanitize_text_field($_GET['pid']). "'

									

									 ", ARRAY_A);
            echo '<tr >';
			
			do_action('spdm_file_list_column_before_folder_back');

		echo '<td class="cdm_file_icon ext_directory" onclick="sp_cdm_load_project(' . $query_project[0]['parent'] . ')"></td>

		<td class="cdm_file_info" onclick="sp_cdm_load_project(' . $query_project[0]['parent'] . ')">&laquo; ' . __("Go Back", "sp-cdm") . '</td>

		<td class="cdm_file_date" onclick="sp_cdm_load_project(' . $query_project[0]['parent'] . ')">&nbsp;</td>

		

		<td class="cdm_file_type" onclick="sp_cdm_load_project(' . $query_project[0]['parent'] . ')">' . __("Folder", "sp-cdm") . '</td>	

		</tr>	

		';
        }
        if (count($r_projects) > 0) {
            for ($i = 0; $i < count($r_projects); $i++) {
                if ($r_projects[$i]['project_name'] != "") {
                    echo '<tr >
';
do_action('spdm_file_list_column_before_folder', $r_projects[$i]['pid']);
echo '
		<td class="cdm_file_icon ext_directory" onclick="sp_cdm_load_project(' . $r_projects[$i]['pid'] . ')"></td>

		<td class="cdm_file_info" onclick="sp_cdm_load_project(' . $r_projects[$i]['pid'] . ')">' . stripslashes($r_projects[$i]['project_name']) . '</td>

		<td class="cdm_file_date" onclick="sp_cdm_load_project(' . $r_projects[$i]['pid'] . ')">&nbsp;</td>

		

		<td class="cdm_file_type">Folder</td>	

		</tr>	

		';
                }
            }
        }
        if ($_GET['sort'] == '') {
            $sort = $this->order_by();
		
        } else {
            $sort = $_GET['sort'];
        }

		
        if ($_GET['pid'] == "" or $_GET['pid'] == "0" or $_GET['pid'] == "undefined" or $_GET['pid'] == "null") {
            if ($_REQUEST['search'] != "") {
                $search_file .= " AND (name LIKE '%" .sanitize_text_field( $_REQUEST['search'] ). "%' or  tags LIKE '%" .sanitize_text_field( $_REQUEST['search'] ). "%')  ";
            	$r_projects_groups_addon_search = str_replace("wp_sp_cu_project.id", "pid",$r_projects_groups_addon);
			} else {
                $search_file .= " AND pid = 0  AND parent = 0  ";
            }
			$search_file = apply_filters("sp_cdm_file_search_query", $search_file, sanitize_text_field($_GET['pid']));
            $r = $wpdb->get_results("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where (uid = '" . $_GET['uid'] . "' ".$r_projects_groups_addon_search.")  	 " . $search_file . " order by " . $sort . " ", ARRAY_A);
			
        } else {
            if ($_REQUEST['search'] != "") {
                $search_file .= " AND (name LIKE '%" .sanitize_text_field( $_REQUEST['search'] ). "%' or  tags LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%')  ";
            } else {
                $search_file .= "  AND parent = 0   ";
            }
			$search_file = apply_filters("sp_cdm_file_search_query", $search_file, sanitize_text_field($_GET['pid']));
            $r = $wpdb->get_results("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where (pid = '" .sanitize_text_field( $_GET['pid'] ). "') " . $search_file . "  order by " . $sort . "  ", ARRAY_A);
			
        }
		
		
		if(get_option('sp_cu_release_the_kraken') == 1){
		unset($r);
		if($_GET['pid'] == ''){
		
		$_GET['pid'] = 0;
		
		}
		
		
		
		 if ($_REQUEST['search'] == "") {
		
			 $search_file .= " AND (pid = '" . $_GET['pid'] . "') ";
		 }
		 $search_file = apply_filters("sp_cdm_file_search_query", $search_file, $_GET['pid']);
		 $query = "SELECT *  FROM " . $wpdb->prefix . "sp_cu  where id != ''   " . $search_file . " and parent = 0   order by " . $sort . "  ";
		//echo  $query ;
		 $r = $wpdb->get_results( $query , ARRAY_A);	
		 
		
		}
	
        for ($i = 0; $i < count($r); $i++) {
            $ext   = preg_replace('/^.*\./', '', $r[$i]['file']);
            $r_cat = $wpdb->get_results("SELECT name  FROM " . $wpdb->prefix . "sp_cu_cats   where id = '" . $r[$i]['cid'] . "' ", ARRAY_A);
            if ($r_cat[0]['name'] == '') {
                $cat = stripslashes($r_cat[0]['name']);
            } else {
                $cat = '';
            }
            if ($_REQUEST['search'] != "" && sp_cdm_get_project_name($r[$i]['pid']) != false) {
                $project_name = ' <em>('.sp_cdm_folder_name() .': ' . sp_cdm_get_project_name($r[$i]['pid']) . ')</em> ';
            } else {
                $project_name = '';
            }
            echo '<tr >
			';
			do_action('spdm_file_list_column_before_file',$r[$i]['id'] );
			
			
			if(get_option('sp_cu_file_direct_access') == 1){
			$file_link = 	'window.open(\''. cdm_download_file_link(base64_encode($r[$i]['id'].'|'.$r[$i]['date'].'|'.$r[$i]['file']),get_option('sp_cu_js_redirect')).'\')'; ;
			}else{
			$file_link =  'cdmViewFile(' . $r[$i]['id'] . ')';	
			}
			if(cdm_file_permissions($r[$i]['pid']) == 1){
			$file_link = apply_filters('spcdm/file_list/link', $file_link, $r[$i]);
			echo '
				<td class="cdm_file_icon ext_' . $ext . '" onclick="cdmViewFile(' . $r[$i]['id'] . ')"></td>

		<td class="cdm_file_info" onclick="'.$file_link.'">' . stripslashes($r[$i]['name']) . ' ' . $project_name . '</td>

		<td class="cdm_file_date" onclick="'.$file_link.'">' . date("F jS Y g:i A", strtotime($r[$i]['date'])) . '</td>



		<td class="cdm_file_type" onclick="'.$file_link.'">' . $ext . '</td>	

		</tr>	

		';
			}
        }
        echo '</tbody></table><div style="clear:both"></div></div>';
    }
    function thumbnails()
    {
    global $wpdb, $current_user;
	if ( !is_user_logged_in() ) 
exit; 
			$back_image = '' . SP_CDM_PLUGIN_URL . 'images/my_projects_folder.png';
			$back_image = apply_filters('spcdm/files/images/back_button', $back_image);
			$folder_image = '' . SP_CDM_PLUGIN_URL . 'images/my_projects_folder.png';
				$folder_image = apply_filters('spcdm/files/images/folder_button', $folder_image);
	
         if (function_exists('cdmFindGroups')) {
            $find_groups = cdmFindGroups($_GET['uid'], 1);
        }
		
		$current_user_projects = sp_cdm_get_user_projects($_GET['uid']);
	
        if ($_REQUEST['search'] != "") {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.name LIKE '%" .sanitize_text_field( $_REQUEST['search']) . "%' ";
        }else{
        if ($_GET['pid'] == '' or $_GET['pid'] == 'undefined') {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.parent = '0' ";
        } else {
            $search_project .= " AND " . $wpdb->prefix . "sp_cu_project.parent = '" . $_GET['pid'] . "' ";
        }
		}
		

          if (get_option('sp_cu_hide_project') == 1) {
			
		
			$r_projects_query = "SELECT " . $wpdb->prefix . "sp_cu.name,

												 " . $wpdb->prefix . "sp_cu.id,

												 " . $wpdb->prefix . "sp_cu.pid ,

												 " . $wpdb->prefix . "sp_cu.uid,

												 " . $wpdb->prefix . "sp_cu.parent,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												 " . $wpdb->prefix . "sp_cu_project.parent

												 

										FROM " . $wpdb->prefix . "sp_cu   

										LEFT JOIN " . $wpdb->prefix . "sp_cu_project  ON " . $wpdb->prefix . "sp_cu.pid = " . $wpdb->prefix . "sp_cu_project.id

										WHERE (" . $wpdb->prefix . "sp_cu.uid = '" . $_GET['uid'] . "'  " . $find_groups . ")

										AND pid != 0

										AND  " . $wpdb->prefix . "sp_cu.parent = 0 

										" . $sub_projects . "";
										
								if($_GET['pid'] == 0 or $_GET['pid'] == ''){
									$r_projects_query = apply_filters('sp_cdm_projects_query', $r_projects_query ,$_GET['uid']);	
										}
									
									$r_projects_query .="	" . $search_project . "
										
										GROUP BY pid

										ORDER by date desc";
				if(get_option('sp_cu_release_the_kraken') == 1){
								unset($r_projects_query);								
								$r_projects_query =	 "SELECT 										 
													" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent
										FROM " . $wpdb->prefix . "sp_cu_project
										WHERE id != ''
										
										" . $search_project . " ORDER by name
";
								}
			
            $r_projects = $wpdb->get_results($r_projects_query, ARRAY_A);
        } else {
			
			
									$r_projects_groups_addon = apply_filters('sp_cdm_projects_query', $r_projects_groups_addon ,$_GET['uid']);	
									
									$search_project = apply_filters('sp_cdm_search_project_query', $search_project );
									
									
					//echo $r_projects_groups_addon ;
			
			if($_GET['pid'] == 0 or $_GET['pid'] == ''){
			$user_query = " AND " . $wpdb->prefix . "sp_cu_project.uid = '" . $_GET['uid'] . "' ";	
			}
			$r_projects_query = "SELECT 

												" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent

												 

										FROM " . $wpdb->prefix . "sp_cu_project

									  WHERE (" . $wpdb->prefix . "sp_cu_project.id != '' ".$user_query." " . $find_groups . " ".$r_projects_groups_addon.")										

										

										" . $search_project . "

										";
									
										$r_projects_query .="

										ORDER by name";
							
					  $r_projects_query = apply_filters('sp_cdm_project_query_final', $r_projects_query);		
						
		if(get_option('sp_cu_release_the_kraken') == 1){
			
			
								unset($r_projects_query);								
								$r_projects_query =	 "SELECT 										 
													" . $wpdb->prefix . "sp_cu_project.id,

												" . $wpdb->prefix . "sp_cu_project.id AS pid,

												" . $wpdb->prefix . "sp_cu_project.uid,

												 " . $wpdb->prefix . "sp_cu_project.name AS project_name,

												  " . $wpdb->prefix . "sp_cu_project.parent
										FROM " . $wpdb->prefix . "sp_cu_project
										WHERE id != ''
										
										" . $search_project . " ORDER by name
";


		}
								
						
	

		   $r_projects = $wpdb->get_results($r_projects_query, ARRAY_A);
        }
		
		
		if($_GET['pid'] == 'drafts'){
		unset($r_projects);
		}
		 echo '
		 <script type="text/javascript">
		 
		 jQuery( document ).ready(function() {
	
	jQuery(".cdm-hover-thumb").hover(function() {
			
		
			jQuery(this).before("<div  class=cdm-preview-image><img src=" + jQuery(this).attr("rel") + " ></div>");
		}, function () {
			jQuery(".cdm-preview-image").remove();
    });
  });
		 </script>
		 
		  <div id="dlg_cdm_thumbnails">';
      
	  
	    if ($_GET['pid'] == '') {
            $jscriptpid = "''";
        } else {
            $jscriptpid = "'" .sanitize_text_field( $_GET['pid']) . "'";
        }
        echo '<div class="sp-cdm-r-head">';
					
		do_action('spdm_file_list_column_before_sort');
		echo '

	<div style="float:right">' . __("Sort by", "sp-cdm") . ': <a href="javascript:sp_cdm_sort(\'name\',' .sanitize_text_field( $_GET['pid']) . ')">' . __("Name", "sp-cdm") . '</a>   <a href="javascript:sp_cdm_sort(\'date\',' . $_GET['pid']. ')">' . __("Date", "sp-cdm") . '</a></div>

	
		

		';
		
		echo '

		
		</div>	

		

		';
	
		
		
        if (($_GET['pid'] != "0" && $_GET['pid'] != '') && ((get_option('sp_cu_user_projects') == 1 and get_option('sp_cu_user_projects_modify') != 1) or current_user_can('manage_options'))) {
            $r_project_info = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sp_cu_project where id = " . sanitize_text_field($_GET['pid']) . "", ARRAY_A);
          
		  if($r_project_info[0]['uid'] == $_GET['uid'] && get_option('sp_cu_user_delete_folders') != 1){
		    echo '<div style="padding-right:10px">';

	echo '<a href="javascript:sp_cu_dialog(\'#edit_category_' .sanitize_text_field( $_GET['pid']) . '\',550,130)"><img src="' . SP_CDM_PLUGIN_URL . 'images/application_edit.png"> '. __("Edit Folder Name", "sp-cdm").'</a>';   
	echo '<a href="#" class="sp-cdm-delete-category" data-id="' . sanitize_text_field($_GET['pid']) . '" style="margin-left:20px"> <img src="' . SP_CDM_PLUGIN_URL . 'images/delete_small.png">  '. __("Remove Folder", "sp-cdm").' </a>';
	
	do_action('cdm/ajax/folder/navigation', sanitize_text_field($_GET['pid']));
		

		echo'<div style="display:none">	

		

		

		<script type="text/javascript">

		




		</script>	

		<div id="delete_category_' . $_GET['pid'] . '" title="' . __("Delete Category?", "sp-cdm") . '">

	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' . __("Are you sure you would like to delete this category? Doing so will remove all files related to this category.", "sp-cdm") . '</p>

		</div>



		

		

				<div id="edit_category_' . $_GET['pid'] . '">			

			

			<input type="hidden"  name="edit_project_id" id="edit_project_id_' . sanitize_text_field($_GET['pid'] ). '" value="' . sanitize_text_field($_GET['pid']) . '">		

			'.sp_cdm_folder_name() .' ' . __("Name", "sp-cdm") . ': <input value="' . stripslashes($r_project_info[0]['name']) . '" id="edit_project_name_' .sanitize_text_field( $_GET['pid']) . '" type="text" name="name"  style="width:200px !important"> 

			<input type="submit" value="' . __("Save", "sp-cdm") . ' '.sp_cdm_folder_name() .'" class="sp-cdm-save-category" data-id="'.sanitize_text_field($_GET['pid']).'" >

			

			</div>

			

		

		

		</div>

		

		


		

		';
		  }
        }

       
	   echo '				<div class="sp_isotope_filter">';
	   
	   do_action('spdm_file_responsive_top',sanitize_text_field($_GET['pid']));			
	 	
	
	    if ($_GET['pid'] != 0) {
	
            $query_project = $wpdb->get_results("SELECT *

	

									

									 FROM " . $wpdb->prefix . "sp_cu_project

									WHERE  id = '" . sanitize_text_field($_GET['pid']) . "'

									

									 ", ARRAY_A);
									 
					 
									 
          
			do_action('spdm_file_list_column_before_folder_back');
			
	 echo '

				<div class="dlg_cdm_thumbnail_folder">

				<a href="javascript:sp_cdm_load_project(' . $query_project[0]['parent'] . ')"><img src="'.$back_image.'">

				<div class="dlg_cdm_thumb_title">

				&laquo; ' . __("Go Back", "sp-cdm") . '

				</div>

				</a>

				</div>

		

			

		

		';
        }
	
	
		$r_projects = apply_filters('sp_cdm_project_array_filter', $r_projects);
	

	    if (count($r_projects) > 0) {
            for ($i = 0; $i < count($r_projects); $i++) {
                if ($r_projects[$i]['project_name'] != "" && in_array( $r_projects[$i]['pid'],$current_user_projects) or get_option('sp_cu_release_the_kraken') == 1) {
                  
					



				   echo '

		<div class="dlg_cdm_thumbnail_folder">

				<a href="javascript:sp_cdm_load_project(' . $r_projects[$i]['pid'] . ')"><img src="'.$folder_image .'">

				<div class="dlg_cdm_thumb_title">

				' . stripslashes($r_projects[$i]['project_name']) . '

				</div>

				</a>';
				
				do_action('spdm_file_thumbs_column_before_folder', $r_projects[$i]['pid']);
				echo '

				</div>

		

		';
                }
            }
        }
        if ($_GET['sort'] == '') {
            $sort = spdm_ajax::order_by();
		
        } else {
            $sort = $_GET['sort'];
        }

		
        if ($_GET['pid'] == "" or $_GET['pid'] == "0" or $_GET['pid'] == "undefined" or $_GET['pid'] == "null") {
            if ($_REQUEST['search'] != "") {
                $search_file .= " AND (name LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%' or  tags LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%')  ";
            $r_projects_groups_addon_search = str_replace("wp_sp_cu_project.id", "pid",$r_projects_groups_addon);
			} else {
                $search_file .= " AND pid = 0  AND parent = 0  ";
            }
			$search_file = apply_filters("sp_cdm_file_search_query", $search_file,sanitize_text_field( $_GET['pid']));
           	$query = "SELECT *  FROM " . $wpdb->prefix . "sp_cu   where (uid = '" . sanitize_text_field($_GET['uid']) . "' ".$r_projects_groups_addon_search.")  	 " . $search_file . " order by " . $sort . " ";
		   	$query = apply_filters('sp_cdm_query_string', $query);
			
		    $r = $wpdb->get_results($query, ARRAY_A);
			
        } else {
			$search_file .= " where (pid = '" .sanitize_text_field( $_GET['pid']) . "')";
            if ($_REQUEST['search'] != "") {
                $search_file .= " AND (name LIKE '%" .sanitize_text_field( $_REQUEST['search'] ). "%' or  tags LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%')  ";
            } else {
                $search_file .= "  AND parent = 0   ";
            }
			$search_file = apply_filters("sp_cdm_file_search_query", $search_file,sanitize_text_field( $_GET['pid']));
            $query = "SELECT *  FROM " . $wpdb->prefix . "sp_cu   " . $search_file . "  order by " . $sort . "  ";
		
			$query = apply_filters("sp_cdm_file_main_responsive_query", $query , sanitize_text_field($_GET['pid']));
		
			$r = $wpdb->get_results( $query , ARRAY_A);
			
        }
		
		
		if(get_option('sp_cu_release_the_kraken') == 1){
		unset($r);
		unset($search_file);
				if($_GET['pid'] == ''){
				
				$_GET['pid'] = 0;
				
		}
		
		 if ($_GET['pid'] == "" or $_GET['pid'] == "0" or $_GET['pid'] == "undefined" or $_GET['pid'] == "null") {
		
			 $search_file .= " AND (pid = '0') "; 
		 }else{
			 $search_file .= " AND (pid = '" . sanitize_text_field($_GET['pid']) . "') "; 
		 }
		  
		 $search_file = apply_filters("sp_cdm_file_search_query", $search_file, sanitize_text_field($_GET['pid']));
		 
		  if ($_REQUEST['search'] != "") {
               	unset( $search_file);
			    $search_file .= " AND (name LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%' or  tags LIKE '%" . sanitize_text_field($_REQUEST['search']) . "%')  ";
            } 
		 $query = "SELECT *  FROM " . $wpdb->prefix . "sp_cu  where id != ''   " . $search_file . "  and parent = 0  order by " . $sort . "  ";
	
		
		 $r = $wpdb->get_results( $query , ARRAY_A);	
	
		
		}
		 if ($_REQUEST['search'] == "") {
		$r = apply_filters('sp_cdm_file_loop_array', $r,sanitize_text_field($_GET['pid']));
			}
			
			
        for ($i = 0; $i < count($r); $i++) {
			
			 $ext        = preg_replace('/^.*\./', '', $r[$i]['file']);
            $images_arr = array(
                "jpg",
                "png",
                "jpeg",
                "gif",
                "bmp"
            );
			
					if(get_option('sp_cu_user_projects_thumbs_pdf') == 1 && class_exists('imagick')){
	
			$info = new Imagick();
			$formats = $info->queryFormats();
			
			}else{
				$formats = array();
			}
	  
			
         if (in_array(strtolower($ext), $images_arr)) {
                if (get_option('sp_cu_overide_upload_path') != '' && get_option('sp_cu_overide_upload_url') == '') {
                    $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/package_labled.png">';
                } else {
                    $img = '<img src="' . sp_cdm_thumbnail('' . SP_CDM_UPLOADS_DIR_URL . '' . $r[$i]['uid'] . '/' . $r[$i]['file'] . '',NULL, 70) . '">';
				
                }
					 } elseif (in_array( $ext , array('mp4','ogg','webm','avi','mpg','mpeg','mkv'))) {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/video.png" >';	
            } elseif ($ext == 'xls' or $ext == 'xlsx') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_excel.png">';
            } elseif ($ext == 'doc' or $ext == 'docx') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_word.png">';
            } elseif ($ext == 'pub' or $ext == 'pubx') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_publisher.png">';
            } elseif ($ext == 'ppt' or $ext == 'pptx') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_powerpoint.png">';
            } elseif ($ext == 'adb' or $ext == 'accdb') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/microsoft_office_access.png">';
            } elseif (in_array(strtoupper($ext),$formats)) {
                if (file_exists('' . SP_CDM_UPLOADS_DIR . '' . $r[$i]['uid'] . '/' . $r[$i]['file'] . '_small.png')) {
                    $img = '<img src="' . sp_cdm_thumbnail('' . SP_CDM_UPLOADS_DIR_URL . '' . $r[$i]['uid'] . '/' . $r[$i]['file'] . '_small.png',NULL, 70).'">';
				
                } else {
                    $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/adobe.png">';
                }
            } elseif ($ext == 'pdf') {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/adobe.png">';
            } else {
                $img = '<img src="' . SP_CDM_PLUGIN_URL . 'images/package_labled.png">';
            }
            if ($_REQUEST['search'] != "" && sp_cdm_get_project_name($r[$i]['pid']) != false) {
                $project_name = ' <br><em>('.sp_cdm_folder_name() .': ' . sp_cdm_get_project_name($r[$i]['pid']) . ')</em> ';
            } else {
                $project_name = '';
            }
			
			$img = apply_filters('sp_cdm_viewfile_image', $img,$r[$i]);
		    $ext   = preg_replace('/^.*\./', '', $r[$i]['file']);
            $r_cat = $wpdb->get_results("SELECT name  FROM " . $wpdb->prefix . "sp_cu_cats   where id = '" . $r[$i]['cid'] . "' ", ARRAY_A);
            if ($r_cat[0]['name'] == '') {
                $cat = stripslashes($r_cat[0]['name']);
            } else {
                $cat = '';
            }
            if ($_REQUEST['search'] != "" && sp_cdm_get_project_name($r[$i]['pid']) != false) {
                $project_name = ' <em>('.sp_cdm_folder_name() .': ' . sp_cdm_get_project_name($r[$i]['pid']) . ')</em> ';
            } else {
                $project_name = '';
            }
           
		   if(get_option('sp_cu_file_direct_access') == 1){
			$file_link = 	'window.open(\''. cdm_download_file_link(base64_encode($r[$i]['id'].'|'.$r[$i]['date'].'|'.$r[$i]['file']),get_option('sp_cu_js_redirect')).'\'); void(0)'; ;
			}else{
			$file_link =  'cdmViewFile(' . $r[$i]['id'] . ')';	
			}
			
			
			if( in_array( $r[$i]['pid'],$current_user_projects)  or   $r[$i]['pid'] == 0 or get_option('sp_cu_release_the_kraken') == 1){
		  $file_link = apply_filters('spcdm/file_list/link', $file_link, $r[$i]);
		    echo '<div class="dlg_cdm_thumbnail_folder">

			<div class="dlg_cdm_thumbnail_image">

				<a href="javascript:'.$file_link.'" ><div class="cdm_img_container">' . $img . '</div>

				<div class="dlg_cdm_thumb_title">

				' . stripslashes($r[$i]['name']) . '' . $project_name . '

				</div>

				</a>

				</div>';
				do_action('spdm_file_thumbs_column_before_file', $r[$i]['id']);
				echo '

				</div>';
		}
        }
        echo '<div style="clear:both"></div></div></div>';
	
	
	
    }
	
function sub_folders($id,$main,$zip){
	 global $wpdb, $current_user;

	$folders   = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where parent = %d", sanitize_text_field($id)), ARRAY_A);

	#echo "SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where parent = $id  ";
		#print_r($folders);
	if(count($folders) > 0){
			
			 for ($j = 0; $j < count($folders); $j++) {
				$dir =  spdm_ajax::get_folder_structure($folders[$j]['id']);
			 $zip->addDirectory($dir);
			$main =   $dir;
				
						$r =  spdm_ajax::folder_files($folders[$j]['id']);
						//	print_r($r);
					 for ($i = 0; $i < count($r); $i++) {
						
						 $dir         = '' . SP_CDM_UPLOADS_DIR . '' . $r[$i]['uid'] . '/';
  #   echo $main.'/'.$r[$i]['file'];
							do_action('cdm/zip/before_add_file',$r[$i]);
							 $zip->addFile(spdm_ajax::get_file($dir . $r[$i]['file']), $main.''.$r[$i]['file'], @filectime($dir . $r[$i]['file']));	 
							do_action('cdm/zip/after_add_file',$r[$i]);
						 unset($dir);
						 
					
						
						 }
						 	spdm_ajax::sub_folders($folders[$j]['id'],$main,$zip);
			
		}
				
		
	}
	
	
}
	function folder_files($id){
		global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
		$r_project   = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu where pid = %d", sanitize_text_field($id)), ARRAY_A);
		
		return $r_project;
		
	}
	function folder_name($id){
		global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
		$r_project   = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where id = %d", sanitize_text_field($id)), ARRAY_A);
		
		return stripslashes($r_project[0]['name']);
	}
function get_folder_structure($pid){
	
		global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
	$array =  array_reverse( spdm_ajax::get_structure($pid));
	
	
	foreach($array as $id =>$name){
		
	$folder .= ''.$name.'/';	
	}
	return $folder;
}
function get_structure($pid,$folder_structure = array()){
		global $wpdb, $current_user;
		if ( !is_user_logged_in() ) 
exit; 
	
	$r  = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where id = %d", sanitize_text_field($pid)), ARRAY_A);
	$folder_structure[$r[0]['id']] =  spdm_ajax::folder_name($r[0]['id']);
	if($r[0]['parent'] == 0){
	$folder_structure[$r[0]['id']] = spdm_ajax::folder_name($r[0]['id']);
	}else{
		
		$s  = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project where id = %d",$r[0]['parent']), ARRAY_A);
		$folder_structure[$s[0]['id']] = spdm_ajax::folder_name($s[0]['id']);
		$folder_structure = spdm_ajax::get_structure($r[0]['parent'],$folder_structure);
		
			
		
				
	}
	

	return $folder_structure;
}	
	function get_file($file){
	
	$filename =$file;
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);	
		return $contents;
	
	}
    
    
	function vendor_replace_vars( $message,$post){
		
			  $message   = str_replace('[file]', $post['links'], $message);	 
			   $message   = str_replace('[notes]',  $post['vendor-message'], $message);		
				$message = wpautop($message);
				return $message;
		
	}
    function email_vendor()
    {
		if ( ! isset( $_REQUEST['cdm_nonce'] ) || ! wp_verify_nonce( $_REQUEST['cdm_nonce'], 'cdm_nonce' )) {exit('Security Error');}
		
        global $wpdb, $current_user;
        if (count($_POST['vendor_email']) == 0) {
            echo '<p style="color:red;font-weight:bold">' . __("Please select at least one file!", "sp-cdm") . '</p>';
        } else {
           
		   
		    $files = sanitize_text_field($_POST['vendor_email']);    
		 	$how_many = count($files);
			$placeholders = array_fill(0, $how_many, '%d');
			$format = implode(', ', $placeholders);
			$query = $wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu  WHERE id IN (%s)", $format);
		 
			$r     = $wpdb->get_results($query, ARRAY_A);
      
            for ($i = 0; $i < count($r); $i++) {
                if ($r[$i]['name'] == "") {
                    $name = $r[$i]['file'];
                } else {
                    $name = $r[$i]['name'];
                }
				
				if($r[$i]['name'] == ''){
				$filename = $r[$i]['file'];
				}else{
				$filename = $r[$i]['name'];	
				}
                $attachment_links .= '<a '. cdm_download_file_link(base64_encode($r[$i]['id'].'|'.$r[$i]['date'].'|'.$r[$i]['file']),get_option('sp_cu_js_redirect')).'>'.$filename . '</a><br>';
                $attachment_array[$i] = '' . SP_CDM_UPLOADS_DIR . '' . $r[$i]['uid'] . '/' . $r[$i]['file'] . '';
            }
         
        
       
            if ($_POST['vendor_attach'] == 3) {
                $attachments = $attachment_array;
                $links.= $attachment_links;
            } elseif ($_POST['vendor_attach'] == 1) {
                $attachments = $attachment_array;
            } else {
                $links .= $attachment_links;
            }
     	
		$post['links'] = $links;
		$post['vendor-message'] = sanitize_text_field($_POST['vendor-message']);
		 	$message =spdm_ajax::vendor_replace_vars(get_option('sp_cu_vendor_email'), $post);      
             $subject = spdm_ajax::vendor_replace_vars(get_option('sp_cu_vendor_email_subject'), $post);
             
			//$headers = apply_filters('spcdm_admin_email_headers',$headers,$post, $uid);
			 if (get_option('sp_cu_vendor_email') != "") {
			 add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		     wp_mail(sanitize_text_field( $_POST['vendor']), stripslashes($subject),stripslashes( $message), $headers, $attachments);
			 remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
          
 do_action('sp_cdm_email_send','sp_cu_vendor_email',$r[0]['id'],$post, $uid,$to, $subject, $message, $headers, $attachments);		  	
      		  }	
		  
		   
            echo '<p style="color:green;font-weight:bold">' . __("Files Sent to", "sp-cdm") . ' ' . $_POST['vendor'] . '</p>';
        }
    }
}
$spcdm_ajax = new spdm_ajax;
?>