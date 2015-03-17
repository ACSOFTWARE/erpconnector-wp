<?
/**
 * ERP CONNECTOR User Functions
 *
 *
 * @version		1.0
 * @package		ERPC/Functions
 * @category	Functions
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

function erpc_add_user_profile_fields( $user ) {
?>
	<h3><?php _e('ERP CONNECTOR', 'erpc'); ?></h3>
	
	<table class="form-table">
		<tr>
			<th>
				<label for="erpc_cid"><?php _e('Contractor ID', 'erpc'); ?>
			</label></th>
			<td>
				<input type="text" name="erpc_cid" id="erpc_cid" value="<?php echo esc_attr( get_the_author_meta( 'erpc_cid', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter ERP Contractor ID.', 'erpc'); ?></span>
			</td>
		</tr>
	</table>
<?php }

function erpc_save_user_profile_fields( $user_id ) {
	
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	
	update_usermeta( $user_id, 'erpc_cid', $_POST['erpc_cid'] );
}

add_action( 'show_user_profile', 'erpc_add_user_profile_fields' );
add_action( 'edit_user_profile', 'erpc_add_user_profile_fields' );

add_action( 'personal_options_update', 'erpc_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'erpc_save_user_profile_fields' );
?>