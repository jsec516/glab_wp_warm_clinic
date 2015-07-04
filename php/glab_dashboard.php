<?php
class glab_dashboard {

	function __construct() {
            add_action( 'admin_init', array($this, 'remove_all_dashboard_widgets'));
            remove_action('welcome_panel ', 'wp_welcome_panel ');
		add_action('wp_dashboard_setup', array($this, 'daily_view_dashboard_widgets'));
	}
	/**
	 * Add a widget to the dashboard.
	 *
	 * This function is hooked into the 'wp_dashboard_setup' action below.
	 */
	function daily_view_dashboard_widgets() {
		
		//$this->remove_all_dashboard_widgets();
		wp_add_dashboard_widget('daily_view_dashboard_widget', // Widget slug.
								"Today's appointments", // Title.
								array($this, 'daily_view_dashboard_widget_function') // Display function.
		);
		add_action('admin_head', array($this,'add_stylesheets'));
		$this->sort_dashboard_widgets();
		

	}

	function remove_all_dashboard_widgets(){
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');//since 3.8

	}
	
	function add_stylesheets(){
		wp_enqueue_style( 'DailyViewWidgetStylesheet',  plugins_url('assets/css/daily_view_widget.css',dirname(__FILE__)));
		
	}
	
	function sort_dashboard_widgets(){
		// Globalize the metaboxes array, this holds all the widgets for wp-admin
		
		global $wp_meta_boxes;
		
		// Get the regular dashboard widgets array
		// (which has our new widget already but at the end)
		
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		
		// Backup and delete our new dashboard widget from the end of the array
		
		$daily_widget_backup = array( 'daily_view_dashboard_widget' => $normal_dashboard['daily_view_dashboard_widget'] );
		unset( $normal_dashboard['daily_view_dashboard_widget'] );
		
		// Merge the two arrays together so our widget is at the beginning
		
		$sorted_dashboard = array_merge( $daily_widget_backup, $normal_dashboard );
		
		// Save the sorted array back into the original metaboxes
		
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}


	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 */
	function daily_view_dashboard_widget_function() {

		require_once 'glab_calendar.php';
		$calendar=new glab_calendar();
		echo  $calendar->daily();
		// Display whatever it is you want to show.
		//echo "Hello World, I'm a great Dashboard Widget";
	}
}
