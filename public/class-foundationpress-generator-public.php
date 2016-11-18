<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.colinduwe.com
 * @since      1.0.0
 *
 * @package    Foundationpress_Generator
 * @subpackage Foundationpress_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Foundationpress_Generator
 * @subpackage Foundationpress_Generator/public
 * @author     Colin Duwe <colinduwe@gmail.com>
 */
class Foundationpress_Generator_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	var $build_dir = 'build';
	var $repo_url = 'https://github.com/olefredrik/FoundationPress/archive/master.zip';
	var $repo_file_name = 'master.zip';
	var $components_dir;
	var $prototype_dir;
	var $bypass_cache = false;
	var $logging = true;	

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		// Initialize class properties.
		$this->bypass_cache = apply_filters( 'foundationpress_generator_bypass_cache', false );
		$this->logging = apply_filters( 'foundationpress_generator_logging', true );
		$this->build_dir = sprintf( '%s/%s', plugin_dir_path(__FILE__), $this->build_dir );
		$this->repo_url = esc_url_raw( $this->repo_url );
		$this->components_dir = $this->build_dir . '/' . str_replace( '.zip', '', $this->repo_file_name );		

	}
	
	/**
	 * This is an init function to grab the theme so we can control when it's called by the generator.
	 */
	public function get_theme_foundationpress_init() {
		// Ensure build directory exists.
		$this->ensure_directory( $this->build_dir );

		// Grab FoundationPress theme from its Github repo.
		$this->get_theme_from_github( $this->build_dir );

	}	

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Foundationpress_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Foundationpress_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/foundationpress-generator-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Foundationpress_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Foundationpress_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/foundationpress-generator-public.js', array( 'jquery' ), $this->version, true );

	}
	
	public function register_shortcode(){
		// Use the shortcode [foundationpress-generator] to render the form.
		add_shortcode( 'foundationpress-generator', array( $this, 'render_generator_form') );
	}
	
	/**
	 * Renders the form when called as a shortcode.
	 */
	public function render_generator_form( $atts ){
		$this->render_types_form();
	}
	
	/**
	 * Places data in JSON files in an array for later use.
	 */
	public function read_json( $file ) {
		$json = file_get_contents( $file );
		return json_decode( $json, TRUE );
	}

	/**
	 * This gets our zip from the Github repo.
	 */
	public function get_theme_from_github( $destination ) {
		// Let's use the latest copy of FoundationPress from Github.
		// The zip file path.
		$zipfile = $destination . '/' . $this->repo_file_name;

		// Get our download from Github.
		$this->download_file( $this->repo_url, $zipfile );

		// Unzip the file.
		$this->unzip_file( $zipfile );
	}

	/**
	 * Read files to process from base. Stores files on array for processing.
	 */
	public function read_base_dir( $dir ) {
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );
		$filelist = array();
		$exclude = array( '.travis.yml', 'codesniffer.ruleset.xml', 'README.md', 'CONTRIBUTING.md', '.git', '.svn', '.DS_Store', '.gitignore', '.', '..' );
		foreach( $files as $file ) {
			if ( ! in_array( basename( $file ), $exclude )	) {
				$filelist[] = $file;
			}
		}
		return $filelist;
	}

	/**
	 * Reads a directory excluding wildcards.
	 */
	public function read_dir( $path, $fullpath=false ) {
		$files = preg_grep( '/^[\\.]{1,2}$/', scandir( $path ), PREG_GREP_INVERT );
		sort( $files ); // Ensure indexes start from zero.
		if ( $fullpath ) {
			foreach ( $files as $i => $file ) {
				$files[$i] = $path . '/' . $file;
			}
		}
		return $files;
	}

	/**
	 * Recursively reads a directory.
	 */
	public function read_dir_recursive( $path, $regex_filter=null ) {
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) );
		$files = array();
		foreach( $iterator as $file ) {
			$files[] = (string) $file->getPathName();
		}
		$files = preg_grep( '%/[\\.]{1,2}$%', $files, PREG_GREP_INVERT );
		if ( $regex_filter ) {
			$files = preg_grep( $regex_filter, $files );
		}
		return $files;
	}

	/**
	 * Renders the generator type form.
	 */
	function render_types_form() {
		// Get the types available.
		//$types = $this->get_types(); ?>
		<section id="generator">

			<div class="wrap">
				<h2>Build your own FoundationPress theme</h2>
				<p>Fill out the information about your new theme and download it.</p>
				<div id="generator-form" class="generator-form">
					<form method="POST" novalidate>
						<input type="hidden" name="foundationpress_generator_generate" value="1" />

						<div class="theme-input clear">

							<div class="generator-form-secondary">
								<div class="foundationpress-generator-form-field">
									<label class="foundationpress-generator-label" for="foundationpress-generator-name">Theme Name <span class="required">(Required)</span></label>
									<input type="text" id="foundationpress-generator-name" class="foundationpress-generator-input" name="foundationpress_generator_types_name" placeholder="Awesome Theme" required>
								</div>

								<div class="foundationpress-generator-form-field">
									<label class="foundationpress-generator-label" for="foundationpress-generator-slug">Theme Slug</label>
									<input type="text" id="foundationpress-generator-slug" class="foundationpress-generator-input" name="foundationpress_generator_types_slug" placeholder="awesome-theme">
								</div>

								<div class="foundationpress-generator-form-field">
									<label class="foundationpress-generator-label" for="foundationpress-generator-author">Author Name</label>
									<input type="text" id="foundationpress-generator-author" class="foundationpress-generator-input" name="foundationpress_generator_types_author" placeholder="Your Name">
								</div>

								<div class="foundationpress-generator-form-field">
									<label class="foundationpress-generator-label" for="foundationpress-generator-author-uri">Author URI</label>
									<input type="url" id="foundationpress-generator-author-uri" class="foundationpress-generator-input" name="foundationpress_generator_types_author_uri" placeholder="http://themeshaper.com/">
								</div>

								<div class="foundationpress-generator-form-field">
									<label class="foundationpress-generator-label" for="foundationpress-generator-description">Theme description</label>
									<input type="text" id="foundationpress-generator-description" class="foundationpress-generator-input" name="foundationpress_generator_types_description" placeholder="A brief description of your awesome theme">
								</div>

								<div class="generator-form-submit">
									<button type="submit" class="button" name="foundationpress_generator_types_generate_submit" value="Download Theme">Download Theme</button>
								</div><!-- .generator-form-submit -->
							</div><!-- .generator-form-secondary -->
						</div><!-- .generator-form-inputs -->
					</form>
				</div><!-- .generator-form -->
			</div><!-- .wrap -->
		</section><!-- #generator -->
	<?php }

	/**
	 * Runs when looping through files contents, does the replacements fun stuff.
	 */
	function replace_theme_fields( $contents, $filename ) {
		// Replace only text files, skip png's and other stuff.
		$valid_extensions = array( 'php', 'css', 'scss', 'js', 'txt' );
		$valid_extensions_regex = implode( '|', $valid_extensions );
		if ( ! preg_match( "/\.({$valid_extensions_regex})$/", $filename ) ) {
			return $contents;
		}

		// Special treatment for style.css
		if ( in_array( $filename, array( 'style.css', 'assets/stylesheets/style.scss' ), true ) ) {
			$theme_headers = array(
				'Theme Name'  => $this->theme['name'],
				'Theme URI'	=> esc_url_raw( $this->theme['uri'] ),
				'Author'		=> $this->theme['author'],
				'Author URI'  => esc_url_raw( $this->theme['author_uri'] ),
				'Description' => $this->theme['description'],
				'Text Domain' => $this->theme['slug'],
			);
			foreach ( $theme_headers as $key => $value ) {
				$contents = preg_replace( '/(' . preg_quote( $key ) . ':)\s?(.+)/', '\\1 ' . $value, $contents );
			}
			$contents = preg_replace( '/\bFoundationPress\b/', $this->theme['name'], $contents );
			// Grab the GPL statement in stylesheets and re-replace with FoundationPress
			$contents = preg_replace( '/\b' . preg_quote( $this->theme['name'] ) . ' is distributed\b/', 'FoundationPress is distributed', $contents );
			return $contents;
		}
		// Function names can not contain hyphens.
		$slug = str_replace( '-', '_', $this->theme['slug'] );
		// Regular treatment for all other files.
		$contents = str_replace( "@package FoundationPress", sprintf( "@package %s", str_replace( ' ', '_', $this->theme['name'] ) ), $contents ); // Package declaration.
		$contents = str_replace( "@since FoundationPress", sprintf( "@since %s", str_replace( ' ', '_', $this->theme['name'] ) ), $contents ); // Since declaration.
		$contents = str_replace( "foundation.css", sprintf( "%s.css",  $slug ), $contents ); // css.
		$contents = str_replace( "foundation.js", sprintf( "%s.js",  $slug ), $contents ); // js.
		$contents = str_replace( "wp_enqueue_script( 'foundation'", sprintf( "wp_enqueue_script( '%s'",  $slug ), $contents ); // Script handle.
		//$contents = str_replace( "foundationpress-", sprintf( "%s-",  $this->theme['slug'] ), $contents ); // Script/style handles.
		$contents = str_replace( "'foundationpress'", sprintf( "'%s'",  $this->theme['slug'] ), $contents ); // Textdomains.
		$contents = str_replace( "foundationpress_", $slug . '_', $contents ); // Function names.
		$class_slug = implode('_', array_map('ucfirst', explode('_', $slug)));
		$contents = str_replace( "Foundationpress_", $class_slug . '_', $contents ); // Class names.
		$contents = preg_replace( '/\bFoundationPress\b/', $this->theme['name'], $contents );
		// Special treatment for readme.txt
		if ( 'readme.txt' == $filename ) {
			$contents = preg_replace('/(?<=Description ==) *.*?(.*(?=(== Installation)))/s', "\n\n" . $this->theme['description'] . "\n\n", $contents );
			$contents = str_replace( 'FoundationPress, or foundationpress', $this->theme['name'], $contents );
		}
		// Special treatment for gulpfile.js
		if ( 'gulpfile.js' == $filename ) {
			$contents = str_replace( "assets/scss/foundation.scss", sprintf( "assets/scss/%s.scss",  $this->theme['slug'] ), $contents ); // scss.
		}
		return $contents;
	}

	/**
	 * Let's take the form input, generate and zip of the theme.
	 */
	function create_zippity_zip() {
		if ( ! isset( $_REQUEST['foundationpress_generator_generate'], $_REQUEST['foundationpress_generator_types_name'] ) ) {
			return;
		}

		$tmp = $this->build_dir . '/tmp';

		$this->ensure_directory( $tmp );

		if ( empty( $_REQUEST['foundationpress_generator_types_name'] ) ) {
			wp_die( 'Please enter a theme name. Go back and try again.' );
		}

		$this->theme = array(
			'name'		  => 'Theme Name',
			'slug'		  => 'theme-name',
			'uri'		  => 'https://foundationpress.olefredrik.com',
			'author'	  => 'Ole Fredrik Lie',
			'author_uri'  => 'http://olefredrik.com/',
			'description' => 'Description',
		);

		$hash = md5( print_r( $this->theme, true ) );
		$this->prototype_dir = $tmp . '/FoundationPress-' . $hash;
		$this->copy_build_files( $this->build_dir . '/FoundationPress-master', $this->prototype_dir );	

		$this->theme['name']  = trim( $_REQUEST['foundationpress_generator_types_name'] );
		$this->theme['slug']  = sanitize_title_with_dashes( $this->theme['name'] );
		if ( ! empty( $_REQUEST['foundationpress_generator_types_slug'] ) ) {
			$this->theme['slug'] = sanitize_title_with_dashes( $_REQUEST['foundationpress_generator_types_slug'] );
		}

		// Let's check if the slug can be a valid function name.
		if ( ! preg_match( '/^[a-z_]\w+$/i', str_replace( '-', '_', $this->theme['slug'] ) ) ) {
			wp_die( 'Theme slug could not be used to generate valid function names. Special characters are not allowed. Please go back and try again.' );
		}
		// Let's check if the name can be a valid theme name.
		if ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=+¬"]/', $this->theme['name'] ) ) {
			wp_die( 'Theme name could not be used to generate valid theme name. Special characters are not allowed. Please go back and try again.' );
		}
		if ( ! empty( $_REQUEST['foundationpress_generator_types_description'] ) ) {
			$this->theme['description'] = trim( $_REQUEST['foundationpress_generator_types_description'] );
		}
		if ( ! empty( $_REQUEST['foundationpress_generator_types_author'] ) ) {
			$this->theme['author'] = trim( $_REQUEST['foundationpress_generator_types_author'] );
		}
		if ( ! empty( $_REQUEST['foundationpress_generator_types_author_uri'] ) ) {
			$this->theme['author_uri'] = trim( $_REQUEST['foundationpress_generator_types_author_uri'] );
			// Let's check if the uri is valid.
			if ( ! preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $this->theme['author_uri'] ) ) {
				wp_die( 'Author URI is not valid. Be sure to include <code>http://</code>. Please go back and try again.' );
			}
		}

		$zip = new ZipArchive;
		$zip_filename = $this->prototype_dir . sprintf( 'foundationpress-%s.zip', md5( print_r( $this->theme, true ) ) );
		$res = $zip->open( $zip_filename, ZipArchive::CREATE && ZipArchive::OVERWRITE );
		$exclude_files = array( '.travis.yml', 'codesniffer.ruleset.xml', 'README.md', 'CONTRIBUTING.md', '.git', '.svn', '.DS_Store', '.gitignore', '.', '..' );
		$exclude_directories = array( '.git', '.github', '.svn', '.', '..' );

		$iterator = new RecursiveDirectoryIterator( $this->prototype_dir );
		foreach ( new RecursiveIteratorIterator( $iterator ) as $filename ) {
			if ( in_array( basename( $filename ), $exclude_files ) ) {
				continue;
			}
			foreach ( $exclude_directories as $directory ) {
				if ( strstr( $filename, "/{$directory}/" ) ) {
					continue 2; // continue the parent foreach loop
				}
			}
			$contents = file_get_contents( $filename );
			$local_filename = str_replace( trailingslashit( $this->prototype_dir ), '', $filename );
			//rename scss
			if ( $local_filename == 'assets/scss/foundation.scss' ){
				$local_filename = 'assets/scss/' . $this->theme['slug'] . '.scss';
			}
			$contents = apply_filters( 'foundationpress_generator_file_contents', $contents, $local_filename );
			$zip->addFromString( trailingslashit( $this->theme['slug'] ) . $local_filename, $contents );
		}
		$zip->close();
		//$this->do_tracking();
		header( 'Content-type: application/zip' );
		header( sprintf( 'Content-Disposition: attachment; filename="%s.zip"', $this->theme['slug'] ) );
		readfile( $zip_filename );
		unlink( $zip_filename );
		$this->delete_directory( $this->prototype_dir );
		exit();
	}

	// Utility functions: These help the generator do its work.

	/**
	 * Copies files to a given directory
	 */
	public function copy_files( $src_dir, $files, $target_dir ) {
		// Do nothing if no files to copy
		if ( empty( $files ) ) return;

		// Make sure target directory exists.
		$this->ensure_directory( $target_dir );

		// Copy over the files
		foreach( $files as $file ) {

			// If the files specified are inside a directory, we need to make sure these
			// directories exist before copying the files, otherwise we get a warning.
			if ( preg_match( '%/%', $file ) ) {
				$this->ensure_directory( $target_dir . '/' . dirname( $file ) );
			}

			copy( $src_dir . '/' . $file, $target_dir . '/' . $file );
		}
	}

	/**
	 * Copy files to temporary build directory.
	 */
	public function copy_build_files( $source_dir, $target_dir, $exclude = array() ) {
		// Bail if source directory is not a directory.
		if ( ! is_dir( $source_dir ) ) {
			return;
		}

		// Make sure target directory exists.
		$this->ensure_directory( $target_dir, true );

		// Add current and previous directory wildcards to excludes.
		$exclude = array_merge( array( '.', '..' ), $exclude );

		// Open directory handle.
		$dir = opendir( $source_dir );

		// Iterate, as long as we have files.
		$file = readdir( $dir );
		while ( false !== $file ) {
			if ( ! in_array( $file, $exclude ) ) {
				if ( is_dir( $source_dir . '/' . $file ) ) {
					// Calling the method recursively, without passing the files to exclude.
					// This has the side effect of only excluding files in the root, and not the ones in subdirectories.
					$this->copy_build_files( $source_dir . '/' . $file, $target_dir . '/' . $file );
				} else {
					copy( $source_dir . '/' . $file, $target_dir . '/' . $file );
				}
			}
			$file = readdir( $dir ); // Set file for next iteration.
		}

		// Close directory handle.
		closedir( $dir );
	}

	/**
	 * This downloads a file at a URL.
	 */
	public function download_file( $URI, $file_name ) {
		$fp = fopen( $file_name, 'w' );
		$ch = curl_init( $URI );
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		$data = curl_exec( $ch );
		curl_close( $ch );
		fclose( $fp );
	}

	/**
	 * This unzips our zip from the Github repo.
	 */
	public function unzip_file( $zip_file ) {
		$path = pathinfo( realpath( $zip_file ), PATHINFO_DIRNAME );
		$zip = new ZipArchive;
		$res = $zip->open( $zip_file );
		if ( true === $res	) {
			$zip->extractTo( $path ); // Extract it to the path we determined above.
			$zip->close();
		} else {
			die( 'Oh no! I couldn\'t open the zip: ' . $zip_file . '.' );
		}
	}

	/**
	 * Checks if a directory exists, creates it otherwise.
	 *
	 * @see http://php.net/mkdir
	 */
	public function ensure_directory( $directory, $delete_if_exists=false ) {
		if ( ! file_exists( $directory ) && ! is_dir( $directory ) ) {

			// Create the directory recursively
			if ( ! mkdir( $directory, 0755, true ) ) {
				$this->log_message( sprintf( __( 'Error: %s directory was not able to be created.', 'foundationpress-generator' ), $directory ) );
			}

		} else if ( $delete_if_exists && is_dir( $directory ) ) {
			$this->delete_directory( $directory );
			$this->ensure_directory( $directory );
		}
	}

	/**
	 * This deletes a file.
	 */
	public function delete_file( $URI ) {
		if ( ! unlink( $URI ) ) {
			$this->log_message( sprintf( __( 'Error: %s file was not able to be deleted.', 'foundationpress-generator' ), $URI ) );
		}
	}

	/**
	 * Delete a directory of files.
	 */
	 function delete_directory( $directory ) {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $fileinfo ) {
			$fname = $fileinfo->isDir() ? 'rmdir' : 'unlink';
			if ( ! call_user_func( $fname, $fileinfo->getRealPath() ) ) {
				$this->log_message( sprintf( __( 'Error: %1$s function was not able to be executed. Arguments were: %2$s.', 'foundationpress-generator' ), $fname, $fileinfo->getRealPath() ) );
			}
		}
		return rmdir( $directory );
	}

	/**
	 * Let's set an expiration on the last download and get current time.
	 */
	function set_expiration_and_go() {
		// We only need to grab the file info of one type zip file since all files are created at once.
		$file_name = $this->build_dir . '/' . $this->repo_file_name;

		// Determine if we need to hook the init method
		$init = ! is_dir( $this->build_dir );
		if ( file_exists( $file_name ) ) {
			$file_time_stamp = date( filemtime( $file_name ) );
			$time = time();
			$expired = 1800; // Expire cache after 30 minutes.
			if ( $this->bypass_cache ) {
				$init = true; // Bypass the cache if debug filter is true
			} else {
				$init = $expired <= ( $time - $file_time_stamp ) ? true : false;
			}
		} else {
			// If no file exists run the init function anyway.
			$init = true;
		}

		if ( $init ) {
			$this->get_theme_foundationpress_init();
		}
	}

	/**
	 * Logs messages to debug.log in wp-content folder
	 */
	public function log_message ( $data )  {
		if ( $this->logging ) {
			if ( is_array( $data ) || is_object( $data ) ) {
				error_log( print_r( $data, true ) );
			} else {
				error_log( $data );
			}
		}
	}	

}
