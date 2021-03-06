<tr id="frm_usermeta_<?php echo $meta_key ?>" class="frm_usermeta_row">
<td width="250px">
	<label style="width:auto;min-width:0;padding-right:5px;"><?php _e( 'Name', 'frmreg' ) ?><span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e('The user meta name will be used to reference your user meta. The user meta name is synonymous with the user meta key. This name/key will be used in the [user_meta key="insert_key_here"] shortcode.', 'frmreg') ?>" ></span></label>
	<input type="text" value="<?php echo esc_attr( ( isset( $echo ) && $echo ) ? $meta_name : '' ) ?>" name="<?php echo $action_control->get_field_name( 'reg_usermeta' ) ?>[<?php echo esc_attr( $meta_key ) ?>][meta_name]"/>
</td>

<td>
	<label style="width:auto;min-width:0;padding-right:5px;"><?php _e( 'Form Field', 'frmreg' ) ?></label>
    <select name="<?php echo esc_attr( $action_control->get_field_name( 'reg_usermeta' ) ) ?>[<?php echo $meta_key ?>][field_id]">
        <option value="">- <?php _e('Select Field', 'frmreg') ?> -</option>
        <?php 
        if(isset($fields) and is_array($fields)){
            foreach($fields as $field){ ?>
			<option value="<?php echo esc_attr( $field->id ) ?>" <?php selected($field_id, $field->id) ?>><?php echo substr( esc_attr( stripslashes( $field->name ) ), 0, 50 );
            unset($field); 
            ?></option>
            <?php
            }
        }
        ?>
    </select> 
    <a class="frm_remove_tag frm_icon_font" data-removeid="frm_usermeta_<?php echo $meta_key ?>" data-showlast=".hide_registration .frm_add_meta_link"></a>
    <a class="frm_add_tag frm_icon_font" href="javascript:frm_add_usermeta_row();"></a>
</td>
</tr>