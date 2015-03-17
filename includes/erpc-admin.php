<?php
/**
 * ERP CONNECTOR Admin Functions
 *
 *
 * @version		1.0
 * @package		ERPC/Functions
 * @category	Functions
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */


function erpc_admin(){
	add_options_page(__("ERPC Options","erpc"), 'ERP CONNECTOR', 'manage_options', __FILE__, 'erpc_admin_page');
}

function erpc_upload_urlaccess($path) {
		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $path."/index.html");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_GET, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$content = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if ( $code == 200 ) {
	    return true;
	}
	

	return false;
}

function erpc_admin_page(){
	
	$erpc_nonce = wp_create_nonce('erpc_nonce');
	global $erpcOptions;
	
	$url = erpc_upload_url();
	if ( !empty($url)
		  && erpc_upload_urlaccess($url) ) {
		echo '<div id="message" class="error fade"><p><strong>'.sprintf(__("Disable url access to %s by .htaccess","erpc"), $url."/*").'</strong></p></div>';
	}
	
	$dir = erpc_upload_dir();
	$file = $dir."/index.html";
	touch($file);
	if ( !is_writable($file) ) {
		echo '<div id="message" class="error fade"><p><strong>'.sprintf(__("Can't write to %s","erpc"), $dir).'</strong></p></div>';
	}
	
	if(isset($_POST['erpc_update']) && isset($_POST['erpc_nonce_update'])){
		if(!wp_verify_nonce(trim($_POST['erpc_nonce_update']),'erpc_nonce')){
			wp_die('Security check not passed!');
		}
		
		$erpcOptions["server"] = trim($_POST['erpc_server']);
		$erpcOptions["username"] = trim($_POST['erpc_username']);
		$erpcOptions["password"] = trim($_POST['erpc_password']);
		update_option("erpc_options", $erpcOptions);
		
		echo '<div id="message" class="updated fade"><p><strong>' . __("Options saved.","erpc") . '</strong></p></div>';
	}
	
	if(isset($_POST['erpc_test']) && isset($_POST['erpc_nonce_test'])){	
		if(!wp_verify_nonce(trim($_POST['erpc_nonce_test']),'erpc_nonce')){
			wp_die('Security check not passed!');
		}

		$error = FALSE;
		$css = 'error';
		
		$ra = erpc_ra_init($error, false);
		if ( $error !== FALSE ) {
			$msg = $error;
		} else {
			
			$hello = erpc_ra_hello($ra, $error);
			if ( $error ) {
				$msg = $error;
			} else {
				$result = $ra->RegisterDevice();
				if ( $result->status->success !== TRUE ) {
					$msg = $result->status->message;
				} else {
					$result = $ra->Login();
					if ( $result->status->success === TRUE ) {
						$css = 'updated';
						$msg = sprintf(__("Connection OK | ERP: %s | Server version: %d.%d","erpc"), $hello->erp_name, $hello->ver_major, $hello->ver_minor);
					} else {
						$msg = $result->status->message;
					}
				}
				
			}
			

		}
		
		echo '<div id="message" class="'.$css.' fade"><p><strong>' . $msg . '</strong></p></div>';
				
	}
	
	
	
?>
<div class="wrap">
	
<?php screen_icon(); ?>
<h2>
ERP CONNECTOR
</h2>

<form action="" method="post" enctype="multipart/form-data" name="erpc_form">

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e('Server','erpc'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="erpc_server" value="<?php echo $erpcOptions["server"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('Username','erpc'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="erpc_username" value="<?php echo $erpcOptions["username"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('Password','erpc'); ?>
		</th>
		<td>
			<label>
				<input type="password" name="erpc_password" value="<?php echo $erpcOptions["password"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('AuthKey','erpc'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="erpc_authkey" value="<?php echo $erpcOptions["authkey"]; ?>" size="60" style="width:340px;height:24px;" readonly/>
			</label>
		</td>
	</tr>
</table>

<p class="submit">
<input type="hidden" name="erpc_update" value="update" />
<input type="hidden" name="erpc_nonce_update" value="<?php echo $erpc_nonce; ?>" />
<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes'); ?>" />
</p>
</form>

<form action="" method="post" enctype="multipart/form-data" name="erpc_testform">
<p class="submit">
<input type="hidden" name="erpc_test" value="test" />
<input type="hidden" name="erpc_nonce_test" value="<?php echo $erpc_nonce; ?>" />
<input type="submit" class="button-primary" value="<?php _e('Connection Test','erpc'); ?>" />
</p>
</form>
</div>
<?php 
}
add_action('admin_menu', 'erpc_admin');
?>