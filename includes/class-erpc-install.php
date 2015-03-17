<?php
/**
 * Installation related functions and actions.
 *
 * @author      AC SOFTWARE SP. Z O.O. [p.zygmunt@acsoftware.pl]
 * @category    Admin
 * @package     ERPC/Classes
 * @version     1.0
 */


class ERPC_Install {
     static function install() {
     	global $erpcOptions;
     	
     	if ( ! defined( 'ERPC_INSTALLING' ) ) {
     		define( 'ERPC_INSTALLING', true );
     		
     		$erpcOptions = get_option("erpc_options");
     		
     		if ( intval(self::get_opt('db_version'), 0) == 0 ) {
     			self::create_tables_v1_0();
     			self::create_functions_v1_0();
     		}
     		
     		self::create_pages();
     		self::create_directories();
     	}
     }
     
     private static function update_opt($oname, $value) {
     	global $erpcOptions;
     	$erpcOptions[$oname] = $value;
     	update_option("erpc_options", $erpcOptions);
     }
     
     private static function get_opt($oname) {
     	global $erpcOptions;
     	return @$erpcOptions[$oname];
     }
     
     private static function create_directories() {
     		$base = erpc_upload_dir();

     		if ( !file_exists($base)
     			 && mkdir($base) ) {
     			touch($base.'/index.html');
     		}
     }
     
     private static function create_pages() {

     	if ( intval(self::get_opt('page_invoices'), 0) == 0 ) {
     		

     		load_plugin_textdomain( 'erpc', false, erpc_plugin_dir() . '/lang' );

     		$page_data = array(
     				'post_status'       => 'publish',
     				'post_type'         => 'page',
     				'post_author'       => 1,
     				'post_name'         => __('Invoices', 'erpc'),
     				'post_title'        => __('Invoices', 'erpc'),
     				'post_content'      => '[erpc_invoice_list]',
     				'comment_status'    => 'closed'
     		);

     		self::update_opt('page_invoices', wp_insert_post( $page_data ));
     	}
     	

     }
     
     private static function create_tables_v1_0() {
     	global $wpdb;
     	
     	$collate = '';
     
     	if ( $wpdb->has_cap( 'collation' ) ) {
     		if ( ! empty( $wpdb->charset ) ) {
     			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
     		}
     		if ( ! empty( $wpdb->collate ) ) {
     			$collate .= " COLLATE $wpdb->collate";
     		}
     	}
     
     	$query = "
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erpc_locks` (
  `lock_name` varchar(100) NOT NULL,
  `lock_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lock_timeout` smallint(6) NOT NULL,
  `lock_owner` int(11) NOT NULL
) ENGINE=InnoDB $collate";
     	$wpdb->query($query);
     	
     	$query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erpc_ts` (
  `action` varchar(100) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB $collate";
     	$wpdb->query($query);

     	$query = "ALTER TABLE `{$wpdb->prefix}erpc_locks`
 ADD UNIQUE KEY `lock_name` (`lock_name`)";
     	$wpdb->query($query);
 
     	$query = "ALTER TABLE `{$wpdb->prefix}erpc_ts`
 ADD UNIQUE KEY `action` (`action`)";
     	$wpdb->query($query);
     	
     	self::update_opt('db_version', 1);
     	
     }
     
     private static function create_functions_v1_0() {
     	global $wpdb;
     	$query = "CREATE FUNCTION `{$wpdb->prefix}erpc_do_lock`(`name` VARCHAR(100), `timeout` INT, `owner` INT) RETURNS int(11)
BEGIN

   SET @lck_name = 'erpc_lck';
   SET @lck_result = 0;
   SET @result = 0;

   SELECT GET_LOCK(@lck_name, 10) INTO @lck_result;
  
   IF @lck_result = 1 THEN
   
       DELETE FROM {$wpdb->prefix}erpc_locks WHERE TIMESTAMPDIFF(SECOND, lock_time, CURRENT_TIMESTAMP) >= lock_timeout;
       
       SELECT 0 INTO @lck_result FROM {$wpdb->prefix}erpc_locks WHERE lock_name = name;
       IF @lck_result = 1 THEN
             INSERT INTO `{$wpdb->prefix}erpc_locks`(`lock_name`, `lock_timeout`, `lock_owner`) VALUES (name, timeout, owner);
             SET @result = 1;
       END IF;
             
          
       SELECT RELEASE_LOCK(@lck_name) INTO @lck_result; 
      
   END IF;

   RETURN @result;

END";
     	$wpdb->query($query);
     }
     

}
