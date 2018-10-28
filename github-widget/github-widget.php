<?php
/*
Plugin Name: GitHub widget
Description: Shows your GitHub projects in a window
Author: Leon Bogaert, leon@tim-online.nl
Version: 0.0.1
Author URI: http://www.vanutsteen.nl
*/

function widget_github_init() {
	// Register widget for use
	$widget_ops = array('classname' => 'widget_github', 'description' => __( "A list of your github projects"));
	$widget_ops = wp_parse_args($widget_ops, widget_github_get_options());
	register_sidebar_widget(array('Github projects', 'widgets'), 'widget_github', $widget_ops);

	// Register settings for use, 300x100 pixel form
	register_widget_control(array('Github projects', 'widgets'), 'widget_github_init_control');
}

//generates the html code
function widget_github($args, $inputs = null) {
	global $wpdb;
	$options = widget_github_get_options();

	// "$args is an array of strings that help widgets to conform to
	// the active theme: before_widget, before_title, after_widget,
	// and after_title are the array keys." - These are set up by the theme
	extract( $args );
	extract( $inputs );
	extract( $options );

	echo <<<EOT
$before_widget $before_title $github_title $after_title
	<ul id="github-badge"></ul>
	<script type="text/javascript" charset="utf-8">
	  GITHUB_USERNAME="$github_username";
	  GITHUB_LIST_LENGTH=$github_list_length;
	  GITHUB_HEAD="$github_head"; // e.g. change to "h2" for wordpress sidebars
	  GITHUB_THEME="$github_theme"; // try 'black'
	  GITHUB_TITLE = "&nbsp;";
	  GITHUB_SHOW_ALL = "$github_show_all"
	</script>
	<script src="http://drnicjavascript.rubyforge.org/github_badge/dist/github-badge-launcher.js" type="text/javascript"></script>
$after_widget
EOT;

	echo <<<EOT
<style>
#github-badge h2.header {
	display: none;	
}
</style>
EOT;

}

function widget_github_init_control() {
	$_POST['save-widgets'] ? widget_github_control_save() : widget_github_control_form();
}

function widget_github_control_form() {
	$options = widget_github_get_options();
	extract($options);
	?>
	<label for="github_username">Github username:</label>
	<input class="widefat" id="github_username" name="github_username" type="text" value="<?= $github_username; ?>" />
	<br />
	
	<label for="github_list_length">Number of projects to show:</label>
	<input id="github_list_length" name="github_list_length" type="text" value="<?= $github_list_length; ?>" size="2" />
	<br />
	
	<label for="github_head">Badge head:</label>
	<input id="github_head" name="github_head" type="text" value="<?= $github_head; ?>" size="2" />
	<br />
	
	<label for="github_theme">Badge theme:</label>
	<select id="github_theme" name="github_theme">
		<option value="white" <?= $github_theme == 'white' ? 'selected' : '';?>>White</option>
		<option value="black" <?= $github_theme == 'black' ? 'selected' : '';?>>Black</option>
	</select>
	<br />
	
	<label for="github_title">Title:</label>
	<input class="widefat" id="github_title" name="github_title" type="text" value="<?= $github_title; ?>" size="10" />
	<br />
	<br />
	
	<label for="github_show_all">Text "show all" button:</label>
	<input class="widefat" id="github_show_all" name="github_show_all" type="text" value="<?= $github_show_all; ?>" size="10" />
	<br />
	
	<input type="hidden" name="save-form" value="true" />
	<?php
}

function widget_github_control_save() {
	$options = widget_github_get_options();
	
	foreach ($options as $key => $value) {
		update_option($key, $_POST[$key]);
	}
}

function widget_github_get_options() {
	$default_options = array(
	'github_list_length' => 10,
	'github_head' => 'h2',
	'github_theme' => 'white',
	'github_title' => 'My Projects',
	'github_show_all' => 'Show all',
	);
	
	$options = array(
	'github_username' => (string)get_option('github_username'),
	'github_list_length' => (int)get_option('github_list_length'),
	'github_head' => (string)get_option('github_head'),
	'github_theme' => (string)get_option('github_theme'),
	'github_title' => (string)get_option('github_title'),
	'github_show_all' => (string)get_option('github_show_all'),
	);
	
	foreach ($options as $key => $value) {
		if (!$value) {
			$options[$key] = $default_options[$key];
		}
	}
	
	if (!$options['github_username']) {
		$options['github_username'] = github_guess_username();
	}
	
	return $options;
}

function github_guess_username() {
	global $wpdb;
	
	$query = "SELECT u.display_name FROM $wpdb->users as u
					LEFT JOIN $wpdb->usermeta as um ON u.id = um.user_id
					WHERE um.meta_key = 'wp_user_level' AND um.meta_value >= 10
					ORDER BY um.meta_value
					LIMIT 1";
	$github_username = $my_drafts = $wpdb->get_var($query);
	return $github_username;
}
// Run code and init
add_action("plugins_loaded", "widget_github_init");

?>
