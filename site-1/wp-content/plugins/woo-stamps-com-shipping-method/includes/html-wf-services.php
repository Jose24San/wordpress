<tr valign="top" id="service_options">
	<td class="forminp" colspan="2" style="padding-left:0px">
	<strong><?php _e( 'Services', 'wf-usps-stamps-woocommerce' ); ?></strong><br/>
		<table class="usps_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php _e( 'Service(s)', 'wf-usps-stamps-woocommerce' ); ?></th>
			</thead>
			<tbody>
				<?php
					$sort = 0;
					$this->ordered_services = array();

					foreach ( $this->services as $code => $values ) {

						if ( isset( $this->custom_services[ $code ]['order'] ) ) {
							$sort = $this->custom_services[ $code ]['order'];
						}

						while ( isset( $this->ordered_services[ $sort ] ) )
							$sort++;

						$this->ordered_services[ $sort ] = array( $code, $values );

						$sort++;
					}

					ksort( $this->ordered_services );

					foreach ( $this->ordered_services as $value ) {
						$code   = $value[0];
						$values = $value[1];
						if ( ! isset( $this->custom_services[ $code ] ) )
							$this->custom_services[ $code ] = array();
						?>
						<tr>
							<td class="sort">
								<input type="hidden" class="order" name="usps_service[<?php echo $code; ?>][order]" value="<?php echo isset( $this->custom_services[ $code ]['order'] ) ? $this->custom_services[ $code ]['order'] : ''; ?>" />
							</td>
							<td>
								<ul class="sub_services" style="font-size: 0.92em; color: #555">
									<?php foreach ( $values['services'] as $key => $name ) : ?>
									<li style="line-height: 23px;">
										<label>
											<input type="checkbox" name="usps_service[<?php echo $code; ?>][<?php echo $key; ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ][ $key ]['enabled'] ) || ! empty( $this->custom_services[ $code ][ $key ]['enabled'] ) ), true ); ?> />
											<?php echo $name; ?>
										</label>
									</li>
									<?php endforeach; ?>
								</ul>
							</td>
							
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</td>
</tr>
<script>
// Ordering
	jQuery(window).load(function(){
		jQuery('.usps_services tbody').sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: '.sort',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css('baclbsround-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
				usps_services_row_indexes();
			}
		});

		function usps_services_row_indexes() {
			jQuery('.usps_services tbody tr').each(function(index, el){
				jQuery('input.order', el).val( parseInt( jQuery(el).index('.usps_services tr') ) );
			});
		};

	});

</script>
<style type="text/css">
	.usps_services
	{
		border-spacing: 0;
		width: 51.5%;
		clear: both;
		margin: 0;
	}
	.usps_services th.sort
	{
		width: 16px;
	}
	.usps_services td.sort 
	{
		cursor: move;
		width: 16px;
		padding: 0;
		cursor: move;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;					
	}
	.usps_services td,.usps_services th 
	{
		vertical-align: middle;
		padding: 4px 7px;
	}
</style>