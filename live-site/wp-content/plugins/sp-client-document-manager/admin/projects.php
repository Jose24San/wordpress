<?php



if (!class_exists('cdmProjects')) {
	


    class cdmProjects
    {
		
        function add()
        {
            global $wpdb;
            echo '

<form action="admin.php?page=sp-client-document-manager-projects" method="post">'.wp_nonce_field( 'cdm_nonce', 'cdm_nonce', true, false ).'';

if($_GET['parent'] != ''){
	$parent = $wpdb->get_results("SELECT  * FROM " . $wpdb->prefix . "sp_cu_project where id = '" . sanitize_text_field($_GET['parent']) . "'  ", ARRAY_A);
	echo '<input type="hidden" name="parent" value="'.sanitize_text_field($_GET['parent']).'">';

$selected = $parent[0]['uid'];
}else{
	
$selected = $r[0]['uid'];	
}
            if ($_GET['id'] != "") {
                $r = $wpdb->get_results("SELECT  * FROM " . $wpdb->prefix . "sp_cu_project where id = '" . sanitize_text_field($_GET['id'] ). "'  ", ARRAY_A);
                echo '<input type="hidden" name="id" value="' . $r[0]['id'] . '"><input type="hidden" name="old_id" value="' . $r[0]['id'] . '">';
        		 $selected = $r[0]['uid'];
		 
		    } //$_GET['id'] != ""
            $users = $wpdb->get_results("SELECT * FROM " . $wpdb->base_prefix . "users order by display_name  ", ARRAY_A);
           
            echo '

	 <table class="wp-list-table widefat fixed posts" cellspacing="0">

  <tr>

    <th width="250">' . __("Name:", "sp-cdm") . '</th>

    <td><input type="text" name="project-name" value="' . stripslashes($r[0]['name']) . '" style="width:100%"></td>

  </tr>

  <tr>

    <th>' . __("User:", "sp-cdm") . '</th>

    <td>';
            wp_dropdown_users(array(
                'name' => 'uid',
                'selected' => $selected,
				'show_option_none'  => __("Not Assigned","sp-cdm")
            ));
            echo '</td>

  </tr>';
  
   do_action('sp_cdm_edit_project_main_form', $r);
  echo '

  <tr>

    <td>&nbsp;</td>

    <td><input type="submit" name="save-project" value="' . __("Save", "sp-cdm") . '"></td>

  </tr>

</table>';
           
		   if($_GET['id'] != ''){
		    do_action('sp_cdm_edit_project_form', sanitize_text_field($_GET['id']));
		   }
            echo '

</form>





';
        }
		
		function getParentName($id){
			global $wpdb;	
			
		$r = $wpdb->get_results("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project   where id = '" . $id . "'", ARRAY_A);	
		
		return $r[0]['name'];
		}
		function getChildren($id,$level = 0){
			
		global $wpdb;
		
		    
		
			$r = $wpdb->get_results("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project   where parent = '" . $id . "' order by parent", ARRAY_A);
			
			if(count($r)>0){
				$level += 1;
				
				for ($x = 1; $x <= $level; $x++) {
				$spacer .= '<span style="margin-right:10px">&rarr; </div>';
				}
				
				for ($i = 0; $i < count($r ); $i++) {
					
					//start loop
					
			
                  $html .= '	<tr>
<td colspan="2">'.$spacer.'' . stripslashes($r[$i]['name']) . '</td>
				

<td>'.$spacer.'<em>Parent: '.$this->getParentName($r[$i]['parent']).'</em></td>



<td>

<a href="javascript: cdm_download_project('.$r[$i]['id'] .',\''.wp_create_nonce( 'my-action_'.$r[$i]['id'] ).'\')" style="margin-right:15px" >' . __("Download Archive", "sp-cdm") . '</a> ';

if($r[$i]['parent'] == 0 or class_exists('spdm_sub_projects')){
 
 $html .='<a href="admin.php?page=sp-client-document-manager-projects&function=add&parent=' . $r[$i]['id'] . '" style="margin-right:15px" >' . __("Add Sub Folder", "sp-cdm") . '</a> '; 

}


 $html .='<a href="admin.php?page=sp-client-document-manager-projects&function=delete&id=' . $r[$i]['id'] . '" style="margin-right:15px" >' . __("Delete", "sp-cdm") . '</a> 

<a href="admin.php?page=sp-client-document-manager-projects&function=edit&id=' . $r[$i]['id'] . '" >' . __("Modify", "sp-cdm") . '</a></td>

</tr><tr><td colspan="4">'.$this->getChildren($r[$i]['id'],	$level ).'</td></tr>';
      
					
					//end loop
					
					
				}
			}
			
			
			return $html;
		}
		
		function move_sub_folders($id,$uid){
			global $wpdb;
			 
			 $projects = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project   where parent = %d",$id), ARRAY_A);
			 
			 if( $projects != false){
			  for ($p = 0; $p < count( $projects); $p++) {
			 $insert['uid']  = $uid;
			 $where['id'] =  $projects[$p]['id'];
			 $wpdb->update("" . $wpdb->prefix . "sp_cu_project", $insert, $where);
                  
                 
					$r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where pid = %d", $projects[$p]['id']), ARRAY_A);	
					 if($r != false){
						 for ($i = 0; $i < count($r); $i++) {
							 if(file_exists('' . SP_CDM_UPLOADS_DIR . ''.$r[$i]['uid'].'/'.$r[$i]['file'].'')){
								rename('' . SP_CDM_UPLOADS_DIR . ''.$r[$i]['uid'].'/'.$r[$i]['file'].'', '' . SP_CDM_UPLOADS_DIR . '' . $uid . '/'.$r[$i]['file'].'');
							 }
						 }
					 }
					
					
					$update['uid']        = $uid;
                    $where_project['pid'] =  $projects[$p]['id'];
                  
				    $wpdb->update("" . $wpdb->prefix . "sp_cu", $update, $where_project);
					$this->move_sub_folders(  $projects[$p]['id'],$uid);
			  }
			 }
		}
		
		

        function view()
        {
            global $wpdb;
			  echo '<h2>' . sp_cdm_folder_name(1) . '</h2>' . sp_client_upload_nav_menu() . '';
			
            if ($_POST['save-project'] != "") {
				if ( ! isset( $_REQUEST['cdm_nonce'] ) || ! wp_verify_nonce( $_REQUEST['cdm_nonce'], 'cdm_nonce' )) {exit('Security Error');}
				$old_project_details = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu_project   where id = %d", sanitize_text_field($_POST['id'])), ARRAY_A);	
				
				
                $insert['name'] = sanitize_text_field($_POST['project-name']);
                $insert['uid']  = sanitize_text_field($_POST['uid']);
               if($_POST['parent'] != ''){
				  $insert['parent']  = sanitize_text_field($_POST['parent']); 
				   
			   }
			   
			   
			    if ($_POST['id'] != "") {
                    $where['id'] = sanitize_text_field($_POST['id']);
                    $wpdb->update("" . $wpdb->prefix . "sp_cu_project", $insert, $where);
                  
				  #move files if ID is different
                 if($old_project_details[0]['uid'] != $_POST['uid']){
					 #make folder if it doesnt exist
					  $dir = '' . SP_CDM_UPLOADS_DIR . '' . sanitize_text_field($_POST['uid'] ). '/';
						if (!is_dir($dir)) {
							mkdir($dir, 0777);
						}
					#get all files in this folder and move them
					$r = $wpdb->get_results($wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where pid = %d",sanitize_text_field( $_POST['id'])), ARRAY_A);	
					 if($r != false){
					 for ($i = 0; $i < count($r); $i++) {
						 if(file_exists('' . SP_CDM_UPLOADS_DIR . ''.$r[$i]['uid'].'/'.$r[$i]['file'].'')){
								rename('' . SP_CDM_UPLOADS_DIR . ''.$r[$i]['uid'].'/'.$r[$i]['file'].'', '' . SP_CDM_UPLOADS_DIR . '' . sanitize_text_field($_POST['uid']) . '/'.$r[$i]['file'].'');
							 }
						}
					 }
					
					#update the user id for files in this folder
					$update['uid']        = sanitize_text_field($_POST['uid']);
                    $where_project['pid'] = sanitize_text_field($_POST['id']);                  
				    $wpdb->update("" . $wpdb->prefix . "sp_cu", $update, $where_project);
					
					#move all sub folders
					$this->move_sub_folders( sanitize_text_field($_POST['id']),sanitize_text_field($_POST['uid']));
					
				 }
					
					$insert_id = sanitize_text_field( $_POST['id']);
					
					do_action('sp_cdm_edit_project_update', $insert_id);
                } else {
					foreach($insert as $key=>$value){ if(is_null($value)){ unset($insert[$key]); } }
                    $wpdb->insert("" . $wpdb->prefix . "sp_cu_project", $insert);
                    $insert_id = $wpdb->insert_id;
					do_action('sp_cdm_edit_project_add', $insert_id);
                }
                do_action('sp_cdm_edit_project_save', $insert_id);
            } //$_POST['save-project'] != ""
            if ($_GET['function'] == 'add' or $_GET['function'] == 'edit') {
                $this->add();
            } //$_GET['function'] == 'add' or $_GET['function'] == 'edit'
            elseif ($_GET['function'] == 'delete') {
                $wpdb->query("DELETE FROM " . $wpdb->prefix . "sp_cu_project WHERE id = " .sanitize_text_field( $_GET['id'] ). "	");
              
				$r = $wpdb->get_results("SELECT *  FROM " . $wpdb->prefix . "sp_cu   where pid = '" .sanitize_text_field( $_GET['id'] ). "'", ARRAY_A);	
		$num = 0;
				
			
				 if(count($r)>0){
					 $last = count($r) - 1;
				 $array .= 'var myArray = [';
				 for ($i = 0; $i < count($r); $i++) {
					 
						if($i != $last){
						$comma = ',';	
						}else{
						$comma = '';	
						}
						$array .=''. $r[$i]['id'].''.$comma.' ';

						
				 }
				$array .= '];';
				 }else{
					
				echo '<script type="text/javascript">
				window.location = "admin.php?page=sp-client-document-manager-projects";
				</script>';	 
				exit;
					 
				 }
	
		
		
		
	
				


            } //$_GET['function'] == 'delete'
            else {
                $r = $wpdb->get_results("SELECT " . $wpdb->prefix . "sp_cu_project.name as projectName,

									" . $wpdb->prefix . "sp_cu_project.uid,
									" . $wpdb->prefix . "sp_cu_project.parent,
									" . $wpdb->prefix . "sp_cu_project.id AS projectID,
									" . $wpdb->base_prefix . "users.ID,
									" . $wpdb->base_prefix . "users.user_nicename								
									
									FROM " . $wpdb->prefix . "sp_cu_project
									LEFT JOIN " . $wpdb->base_prefix . "users ON " . $wpdb->prefix . "sp_cu_project.uid = " . $wpdb->base_prefix . "users.ID
										
									 WHERE " . $wpdb->prefix . "sp_cu_project.parent = 0 
									 	
									 order by " . $wpdb->prefix . "sp_cu_project.name", ARRAY_A);
              
                echo '

								

									 

									 

									 <div style="margin:10px">

									 <a href="admin.php?page=sp-client-document-manager-projects&function=add" class="button">' . __("Add", "sp-cdm") . ' ' . sp_cdm_folder_name() . '</a>

									 </div>

									 <table class="wp-list-table widefat fixed posts" cellspacing="0">

	<thead>

	<tr>

<th style="width:40px"><strong>' . __("ID", "sp-cdm") . '</strong></th>

<th><strong>' . __("Name", "sp-cdm") . '</strong></th>

<th><strong>' . __("User", "sp-cdm") . '</strong></th>

<th><strong>' . __("Action", "sp-cdm") . '</strong></th>

</tr>

	</thead><tbody id="sortable_projects">';
                for ($i = 0; $i < count($r); $i++) {
                    $vendor_info[$i] = unserialize($vendors[$i]['option_value']);
                    echo '	<tr>

<td style="font-weight:bold;background-color:#EFEFEF">' . $r[$i]['projectID'] . '</td>				

<td style="font-weight:bold;background-color:#EFEFEF">' . stripslashes($r[$i]['projectName']) . '</td>

<td style="font-weight:bold;background-color:#EFEFEF">' . $r[$i]['user_nicename'] . '</td>

<td style="font-weight:bold;background-color:#EFEFEF">

<a href="javascript:cdm_download_project('.$r[$i]['projectID'].',\''.wp_create_nonce( 'cdm-download-archive' ).'\');" style="margin-right:15px" >' . __("Download Archive", "sp-cdm") . '</a>  ';


if($r[$i]['parent'] == 0 or class_exists('spdm_sub_projects')){
 
 echo '<a href="admin.php?page=sp-client-document-manager-projects&function=add&parent=' . $r[$i]['projectID'] . '" style="margin-right:15px" >' . __("Add Sub Folder", "sp-cdm") . '</a> '; 

}




 echo '<a href="admin.php?page=sp-client-document-manager-projects&function=delete&id=' . $r[$i]['projectID'] . '" style="margin-right:15px" >' . __("Delete", "sp-cdm") . '</a> 

<a href="admin.php?page=sp-client-document-manager-projects&function=edit&id=' . $r[$i]['projectID'] . '" >' . __("Modify", "sp-cdm") . '</a></td>

</tr><tr><td colspan="4">'.$this->getChildren($r[$i]['projectID'] ).'</td></tr>';
                } //$i = 0; $i < count($r); $i++
                echo '</tbody></table>';
            }
        }
    }
} //!class_exists('cdmProjects')
?>