<?php
/**
 * Plugin Name:       WP Auto Post
 * Plugin URI:        https://github.com/mmdmahbub
 * Description:       This plugin will allow to import post and schedule posting to requested site.
 * Version:           1.0
 * Author:            Mahbub Ansary
 * Author URI:        https://github.com/mmdmahbub
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      WP Auto Post
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




include( plugin_dir_path(__FILE__) .'/posts.php');

function load_wpeiePro_js(){
	wp_enqueue_style( 'wpeiePro_css', plugins_url( "/css/wpeiePro.css", __FILE__ ) );	
	wp_enqueue_style( 'wpeiePro_css');		

	
	if( ! wp_script_is( "wpeiePro_fa", 'enqueued' ) ) {
		wp_enqueue_style( 'wpeiePro_fa', plugins_url( '/css/font-awesome.min.css', __FILE__ ));	
	}
	
	wp_enqueue_script( 'wpeiePro-xlsx', plugins_url( "/js/xlsx.js", __FILE__ ), array('jquery') , null, true );	
	wp_enqueue_script( 'wpeiePro-xlsx');	
	wp_enqueue_script( 'wpeiePro-filesaver', plugins_url( "/js/filesaver.js", __FILE__ ), array('jquery') , null, true );	
	wp_enqueue_script( 'wpeiePro-filesaver');
	
	wp_enqueue_script( 'wpeiePro-tableexport', plugins_url( "/js/tableexport.js", __FILE__ ), array('jquery') , null, true );	
	wp_enqueue_script( 'wpeiePro-tableexport');
	
    wp_enqueue_script( 'wpeiePro-mainJs', plugins_url( '/js/wpeiePro.js', __FILE__ ), array('jquery','jquery-ui-core','jquery-ui-tabs','jquery-ui-draggable','jquery-ui-droppable') , null, true);		
	wp_enqueue_script( 'wpeiePro-mainJs');
	
	if( !empty(get_option('wpeieProChooseExcelMethod'))  ) {
		
		$export = get_option('wpeieProChooseExcelMethod');
		
		if( get_option('wpeieProChooseExcelMethod') =='2' ){
			
			wp_enqueue_script( 'wpeiePro-table2excel', plugins_url( "/js/table2excel.js", __FILE__ ), array('jquery') , null, true );	
			wp_enqueue_script( 'wpeiePro-table2excel');	
			
		}
		
			
	}else $export='';
	
    $WpAutoPost = array( 
		'RestRoot' => esc_url_raw( rest_url() ),
		'plugin_url' => plugins_url( '', __FILE__ ),
		'siteUrl'	=>	site_url(),
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'exportfile' => plugins_url( '/js/tableexport.js', __FILE__ ),
		'exportMethod' => $export 
	);	
    wp_localize_script( 'wpeiePro-mainJs', 'wpeiePro', $WpAutoPost );	
	
}

add_action('admin_enqueue_scripts', 'load_wpeiePro_js');

//RUN IMPORT BATCH

add_action( 'wp_ajax_webd_copy_file_over', 'wpeiePro_process' );
add_action( 'wp_ajax_nopriv_webd_copy_file_over',  'wpeiePro_process' );

//RUN EXPORT BATCH

add_action( 'wp_ajax_wpeiePro_exportProducts', 'wpeiePro_exportProducts' );
add_action( 'wp_ajax_nopriv_wpeiePro_exportProducts',  'wpeiePro_exportProducts' );

//ON ACTIVATION 

function wp_table_install(){
    global $wpdb;
    global $db_version;

    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

     $sql="CREATE TABLE IF NOT EXISTS wp_auto_post_files_info ( 
         id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
         file_name varchar(255) DEFAULT '' NOT NULL,
         file_path varchar(255) DEFAULT '' NOT NULL,
         file_status int(11) DEFAULT '0' NOT NULL,
         uploaded_at timestamp NOT NULL,
         PRIMARY KEY (id)) $charset_collate;";
		 
    //dbDelta($sql);
	
	$sql="CREATE TABLE IF NOT EXISTS wp_auto_post_sites ( 
         id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
         site_name varchar(255) DEFAULT '' NOT NULL,
         server_url varchar(255) DEFAULT '' NOT NULL,
         server_username varchar(255) DEFAULT '' NOT NULL,
         server_password varchar(255) DEFAULT '' NOT NULL,
         website_url varchar(255) DEFAULT '' NOT NULL,
		 site_status int(11) DEFAULT '0' NOT NULL,
         added_at timestamp NOT NULL,
         PRIMARY KEY (id)) $charset_collate;";
		 
    dbDelta($sql);

    add_option("db_version", $db_version);

}
register_activation_hook( __FILE__, 'wp_table_install' );

$role = get_role( 'administrator' );
$role->add_cap( 'WpAutoPost' );
				
//ON DEACTIVATION 
function wpeiePro_deactivate(){
	
	$role = get_role( 'administrator' );
	$role->remove_cap( 'WpAutoPost' );
	
	if( get_option( 'wpeiePro_usr_access' ) ) {
		
		$role = get_role( get_option( 'wpeiePro_usr_access' ) );
		$role->remove_cap( 'WpAutoPost' );
	}
			
}
register_deactivation_hook( __FILE__, 'wpeiePro_deactivate' );



//ADD MENU LINK AND PAGE FOR WOOCOMMERCE IMPORTER

add_action('admin_menu', 'wpeiePro_menu');

function wpeiePro_menu() {
	add_menu_page('Post Uplaod',__("WP Auto Post","WpAutoPost"), 'WpAutoPost', 'WpAutoPost', 'wpeiePro_plugin_init', 'dashicons-backup','50');
}


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_wpeiePro_links' );

function add_wpeiePro_links ( $links ) {
	
	$links[] =  "<a href='" . admin_url( 'admin.php?page=WpAutoPost' ) . "'>".__("Settings","WpAutoPost")."</a>";
	return $links;
   
}

function wpeiePro_plugin_init(){
	wpeiePro_init();
}




//MAIN VIEW
function wpeiePro_init() {
		//$products = new WpeieProProducts;

		?>
		<div class="wpeiePro">
			<?php			
			$tabs = array(
				//'main' => __("Import","WpAutoPost"),
				//'upload_file' => __("Upload File","WpAutoPost"),
				'files' => __("Files","WpAutoPost"),
				'Sites' => __("Sites","WpAutoPost"),
				'post_scheduler' => __("Post Scheduler","WpAutoPost"),
				'settings' => __("Settings","WpAutoPost"),
			);
			
			if(isset($_GET['tab']) && $_GET['tab'] ){
				$current = $_GET['tab'] ;
			}else $current = 'main';
			echo '<h2 class="nav-tab-wrapper" >';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab === $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=WpAutoPost&tab=$tab'>$name</a>";

			}
			echo '</h2>';?>
			
			<div class='msg'></div>
			<?php
			if(isset ( $_GET['tab'] )  && $_GET['tab']==='deleteProduct'){
				$products->deleteProductsDisplay();
				
			}elseif(isset ( $_GET['tab'] )  && $_GET['tab']==='settings'){
				?><form method="post" id='wpeieProForm'  >
					<input type='hidden' name="wpeiePro_adminProcessSettings" value='1' />
				<?php
				
				settings_fields( 'wpeieProgeneral-options' );
				do_settings_sections( 'wpeieProgeneral-options' );

				wp_nonce_field( "wpeiePro_settings" );
					wp_auto_post_func();
				
				?></form>
				<div class='result'><?php wpeiePro_adminProcessSettings(); ?> </div>
				<?php							
			}elseif(isset ( $_GET['tab'] )  && $_GET['tab']==='files'){
				upload_file();
			}elseif(isset ( $_GET['tab'] )  && $_GET['tab']==='Sites'){
				add_site();
			}elseif(isset ( $_GET['tab'] )  && $_GET['tab']==='exportProducts'){
				//$products->exportProductsDisplay() ;
			}elseif(isset ( $_GET['tab'] )  && $_GET['tab']==='instructions'){?>
				<br/>
				<div class='center'>
					<p>
						<?php _e("Instructions","WpAutoPost");?> - 
						<a href='<?php echo plugins_url( '/documentation/documentation.pdf', __FILE__ ); ?>'>
							<?php _e("Download Documentation","WpAutoPost");?>
						</a>		
					</p>				
					<iframe src="https://www.youtube.com/embed/w8YkbMrUuMY?rel=0" frameborder="0" allowfullscreen></iframe>				
				</div>			
			<?php
			}else  upload_file();?>
		</div>
		<?php	
}












function files(){ ?>
	<h2>File Manager</h2>

	<a href="" class="button" download="<?php echo plugins_url( '/import-posts.xlsx', __FILE__ ); ?>" > Download Example File</a>

	<a class="button upload_file">Upload a File</a>
	<?php 
		global $wpdb;
		$results = $wpdb->get_results("SLECT * FROM uploaded_files_info"); 
		
		foreach($results as $k => $v){
			echo $v['file_name'];
		}
	?>
	
<?php 
}
function sites(){ ?>
	<h2>Site Manager</h2>
	<a class="button upload_file">Add Website</a>
	<?php 
		global $wpdb;
		$results = $wpdb->get_results("SLECT * FROM uploaded_files_info"); 
		
		foreach($results as $k => $v){
			echo $v['file_name'];
		}
	?>
	
<?php 
}

function upload_file(){
	
	if(isset($_FILES['uplaod_file'])){
	
		$filename = $_FILES['uplaod_file']['name'];
		// destination of the file on the server
		$destination = plugin_dir_path( __FILE__ ).'uploads/' . $filename;

		// get the file extension
		$ext = pathinfo($_FILES["uplaod_file"]['name'], PATHINFO_EXTENSION);
	
		$name=$_FILES['uplaod_file']['name'];
		$size=$_FILES['uplaod_file']['size'];
		$type=$_FILES['uplaod_file']['type'];
		echo $temp=$_FILES['uplaod_file']['tmp_name'];
		$random = rand(0000,9999).time();
		$name = $random."_".$name;
		
		
		if($ext == 'xlsx'){
			
			if (move_uploaded_file($temp,plugin_dir_path( __FILE__ ).'uploads/'.$name)) {
				
				global $wpdb;


				$tablename= 'uploaded_files_info';
				
				$data=array(
					'file_name' => $name, 
					'file_path' => $destination,
					'file_status' => 1, 

				);
				 $wpdb->insert( $tablename, $data);
				echo "<div class='notice notice-success is-dismissible'>";
					echo _e( 'Done!' ); 
				echo "</div>";
				
			} else {
				echo "<div class='notice notice-error is-dismissible'>";
					echo _e( 'Failed to upload!' ); 
				echo "</div>";
			}
			
			
		}else{
			echo "<div class='notice notice-error is-dismissible'>";
					echo _e( 'Please upload an excel file!' ); 
				echo "</div>";
		}
	
	}
	
	files();
	
	
	
	



?>
	<div class="card wap_upload_filed" >
		<form method="post"  enctype="multipart/form-data" action= "<?php echo admin_url( 'admin.php?page=WpAutoPost&tab=upload_file' ); ?>">
			<label><strong>Upload an excel file(.xlsx)</strong> <br> <br><input type="file" name="uplaod_file" required><br/></label>
			
			<br/>
			<input type="submit" class="button" value="Upload File">
			<br/><br/>
		</form>
	</div>
	<div class="card">
		<h2>Uploaded Files</h1>
		<table class=" ">
			<thead>
				<th class="manage-column">File Name<th>
				<th >Uploaded Path<th>
				<th>File Status<th>
				<th>Action<th>
			</thead>
			
			<tbody>
				<tr>
					<td>Fila Name<td>
					<td>path <td>
					<td>Status<td>
					<td>Action<td>
					<td><td>
				<tr>
			</tbody>
		</table>
	</div>
	
<?php
}


function add_site(){
	
	if(isset($_POST['add_site'])){
	
		$filename = $_FILES['uplaod_file']['name'];
		// destination of the file on the server
		$destination = plugin_dir_path( __FILE__ ).'uploads/' . $filename;

		// get the file extension
		$ext = pathinfo($_FILES["uplaod_file"]['name'], PATHINFO_EXTENSION);
	
		$name=$_FILES['uplaod_file']['name'];
		$size=$_FILES['uplaod_file']['size'];
		$type=$_FILES['uplaod_file']['type'];
		echo $temp=$_FILES['uplaod_file']['tmp_name'];
		$random = rand(0000,9999).time();
		$name = $random."_".$name;
		
		
		if($ext == 'xlsx'){
			
			if (move_uploaded_file($temp,plugin_dir_path( __FILE__ ).'uploads/'.$name)) {
				
				global $wpdb;


				$tablename= 'uploaded_files_info';
				
				$data=array(
					'file_name' => $name, 
					'file_path' => $destination,
					'file_status' => 1, 

				);
				 $wpdb->insert( $tablename, $data);
				echo "<div class='notice notice-success is-dismissible'>";
					echo _e( 'Done!' ); 
				echo "</div>";
				
			} else {
				echo "<div class='notice notice-error is-dismissible'>";
					echo _e( 'Failed to upload!' ); 
				echo "</div>";
			}
			
			
		}else{
			echo "<div class='notice notice-error is-dismissible'>";
					echo _e( 'Please upload an excel file!' ); 
				echo "</div>";
		}
	
	}
	
	sites();

?>
	<div class="card wap_upload_filed" >
		<h1>Add New Website</h1>
		<form method="post"  enctype="multipart/form-data" action= "<?php echo admin_url( 'admin.php?page=WpAutoPost&tab=upload_file' ); ?>">
			<input type="text" placeholder="Enter Website Server URL" name="server">
			<br/>
			<input type="text" placeholder="Server User Name" name="user_name">
			<br/>
			<input type="text" placeholder="Server Password" name="server_password">
			<br/>
			<input type="text" placeholder="Website URL" name="website_url">
			<br/>
			
			<br/>
			<input type="submit" class="button" value="Add Site">
			<br/><br/>
		</form>
	</div>
	<div class="card">
		<h2>Uploaded Files</h1>
		<table class=" ">
			<thead>
				<th class="manage-column">File Name<th>
				<th >Uploaded Path<th>
				<th>File Status<th>
				<th>Action<th>
			</thead>
			
			<tbody>
				<tr>
					<td>Fila Name<td>
					<td>path <td>
					<td>Status<td>
					<td>Action<td>
					<td><td>
				<tr>
			</tbody>
		</table>
	</div>
	
<?php
}




