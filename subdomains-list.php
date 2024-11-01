<?php
/* 
* Plugin Name: Sub Domain List
* Description: Display list of all Subdomains.
* Version: 1.0  
* Author: KK
* License: GPL2
*/
/* On Plugin Activation */

/***** Registering Hooks *******/
define('SUBDOMAIN_LIST_URL',plugin_dir_url(__FILE__ ));
define('SUBDOMAIN_LIST_PATH',plugin_dir_path(__FILE__ ));

function sdl_scripts_styles() {
    wp_enqueue_style( 'datatable-style', SUBDOMAIN_LIST_URL . DIRECTORY_SEPARATOR. 'css/jquery.dataTables.css');
	wp_enqueue_script( 'datatable-js', SUBDOMAIN_LIST_URL . DIRECTORY_SEPARATOR .'js/jquery.dataTables.min.js', array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', 'sdl_scripts_styles' );

add_action('admin_menu', 'sdl_admin_menu_page');
register_activation_hook(__FILE__,'sdl_install_options');
add_action( 'admin_init', 'sdl_install_options' );

/***** Create Admin Menu *******/

function sdl_admin_menu_page(){  
	add_menu_page( 'Sub Domain List', 'Sub Domain List', 'manage_network', 'all_subdomains', 'sdl_list_all_subdomains');
}

/***** Create database table on plugin activation *******/

$table_name = $wpdb->prefix . 'subdomains';
					
function sdl_install_options() {
   	global $wpdb;
  	global $table_name;
 
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE " . $table_name . " (
		`ID` INT( 11 ) NOT NULL AUTO_INCREMENT,
		`subdomain_id` INT( 11 ) NOT NULL,
		 PRIMARY KEY id (ID)
		) ENGINE = MYISAM ;";
 
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
/***** Display list of all subdomains. *******/

function sdl_list_all_subdomains()
{
	// print_r($_POST);
	// if ( current_user_can('manage_network') ) {
		global $wpdb;
		$blog_list = get_blog_list( 0, 'all' );
	    
	    if(!empty($_POST)){
			$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}subdomains");
		    foreach($_POST['subdomainID'] as $subdomainID){
			   	$wpdb->insert("{$wpdb->prefix}subdomains", array(
			       "subdomain_id" => intval($subdomainID)
			   	));
		    }
		}

		if (count($blog_list) > 1){
			echo '<h1>List of all Sub Domains</h1>';
			echo '<form action="admin.php?page=all_subdomains" method="post"><table id="list-subdomain" class="display" cellspacing="0" width="50%">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Check</th>';
					echo '<th>Subdomain Names</th>';
					echo '<th>Link</th>';
				echo '</tr>';
			echo '</thead>';

			echo '<tfoot>';
				echo '<tr>';
					echo '<th>Check</th>';
					echo '<th>Subdomain Names</th>';
					echo '<th>Link</th>';
				echo '</tr>';
			echo '</tfoot>';
			echo '<tbody>';
			foreach ($blog_list as $blog) {
				$blog_id = intval($blog['blog_id']);
				$subdomains = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}subdomains WHERE subdomain_id =".$blog_id);
		    
				if ($subdomains->subdomain_id == $blog_id)
				{
					$checked = "checked=checked";
				}
				else
				{
					$checked = "";
				}
			
				$blog_details = get_blog_details($blog_id);
				$link = esc_url("http://".$blog['domain']."".$blog['path']);
				if ($blog_id != 1){
					echo '<tr>
						<td>
							<input type="checkbox" name="subdomainID[]" '.$checked.' value="'.$blog_id.'" />
						</td>
						<td>'.esc_html($blog_details->blogname).'</td>
						<td><a target="_blank" href='.$link.'>'.$link.'</a></td>
					</tr>';
				}
	  		}//while ends here
			echo '</tbody>';
			echo '</table><input type="submit" value="Save" id="save-subdomains" name="save_subdomains" class="save-subdomains" /></form>';
		
		}
	?>
	<script type="text/javascript">
	jQuery(function(){
		var table = jQuery('#list-subdomain').DataTable( {
        "scrollY": "100%",
        "paging": true
    });
	});
	</script>
<?php
/*	}else{
		echo 'You don\'t have permission to access this page!!!';
	}*/
}

function sdl_list_all_subdomains_front()
{
	global $wpdb;
	$blog_list = get_blog_list( 0, 'all' );
    
    if(!empty($_POST)){
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}subdomains");
	    foreach($_POST['subdomainID'] as $subdomainID){
		   	$wpdb->insert("{$wpdb->prefix}subdomains", array(
		       "subdomain_id" => intval($subdomainID)));
	    }
	}

	if (count($blog_list) > 1){
		echo '<h1>List of all Sub Domains</h1>';
		echo '<form action="admin.php?page=all_subdomains" method="post"><table id="list-subdomain-front" class="display" cellspacing="0" width="50%">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>Subdomain Names</th>';
				echo '<th>Link</th>';
			echo '</tr>';
		echo '</thead>';

		echo '<tfoot>';
			echo '<tr>';
				echo '<th>Subdomain Names</th>';
				echo '<th>Link</th>';
			echo '</tr>';
		echo '</tfoot>';
		echo '<tbody>';
		foreach ($blog_list as $blog) {
			$blog_id = intval($blog['blog_id']);
			$subdomains = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}subdomains WHERE subdomain_id =".$blog_id);

			$blog_details = get_blog_details($subdomains->subdomain_id);
			$link = esc_url("http://".$blog['domain']."".$blog['path']);
			if ($blog_details->blog_id != 1){
				echo '<tr>
					<td>'.esc_html($blog_details->blogname).'</td>
					<td><a target="_blank" href='.$link.'>'.$link.'</a></td>
				</tr>';
			}
  		}
		echo '</tbody>';
		echo '</table></form>';
	
	}
	?>
	<script type="text/javascript">
	jQuery(function(){
		var table = jQuery('#list-subdomain-front').DataTable( {
        "scrollY": "100%",
        "paging": true
    });
	});
	</script>
<?php
}

add_shortcode('sdl_list_all_subdomains','sdl_list_all_subdomains_front');
?>
