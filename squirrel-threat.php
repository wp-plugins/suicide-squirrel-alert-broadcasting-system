<?php
/*
Plugin Name: Suicide Squirrel Threat Meter
Description: Adds a sidebar widget that connects to the Suicide Squirrel Advisory Broadcasting System, and loads the correct image to indicate the current threat of suicidal squirral activity, as established by SCIENCE! Do your part to counter the Global Squirrel Insurgency!
Author: Jerry Seeger
License: GPL vs3.0 or higher.
Version: 1.0.4
Author URI: http://muddledramblings.com
Plugin URI: http://muddledramblings.com/suicide-squirrel-widget
*/

// Put functions into one big function we'll call at the plugins_loaded
// action. This ensures that all required plugin functions are defined.
function widget_squirrel_threat_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function jsh_get_squirrel_threat_data() {
		$url='http://muddledramblings.com/current-squirrel-threat';
		$data = NULL;
		
		// Check for 404? Doesn't seem necessary since page is static. If server's down woun't get 404 anyway.
		
		$raw_data = wp_remote_fopen($url); // more flexible than using simplexml_load_file (I think)
		
		//print_r($raw_data);

		if ($raw_data) {
			$parser = xml_parser_create();
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parse_into_struct($parser, $raw_data, $items);
			xml_parser_free($parser);
			
			foreach($items as $item) {
				if ($item['type'] == 'complete') {
					$data->$item['tag'] = $item['value'];
				}
			}
		}
		else {
			echo "<br>Error connecting to Suicide Squirrel Alert Broadcasting Syetem. Protect your nuts and pray to whatever's handy that this isn't the start of something big.<br /><br />";
		}

		return $data;
	}
	
	function widget_squirrel_threat($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		$data = jsh_get_squirrel_threat_data();
		if ($data) {
			$imageURI = $data->imageURI;
			$linkURI = $data->linkURI;
			$chatter = $data->chatter;
			$color = $data->color;
	
			$options = get_option('widget_squirrel_threat');
			$title = $options['title'];
			$remark = $options['remark'];
			$do_link = $options['do_link'];
			
			echo $before_widget;
			if (strlen($title) > 0)
				echo $before_title . $title . $after_title;
			
	
			echo '<div class="sstl_display" style="text-align:center;">';
			if ($do_link == 'Yes') {
				echo '<a href="'.$linkURI.'">';
			}
			echo '<img src="'.$imageURI.'" alt="Suicide Squirrel Alert Level" title="Suicide Squirrel Alert Level: '.$color.'; chatter: '.$chatter.'" />';
			if ($do_link == 'Yes') {
				echo '</a>';
			}
			echo '</div>';
			
			if (strlen($remark) > 0)
				echo '<p class="sstl_remark">'.$remark.'</p>';
			echo $after_widget;
		} else {
			echo "<br /> Level 1 Squirrel Emergency! PANIC!";
		}
	}

	function widget_squirrel_threat_control() {

		// Get options and see if we're handling a form submission.
		$options = get_option('widget_squirrel_threat');
		if ( !is_array($options) )
			$options = array('title'=>'', 'remark'=>'', 'do_link'=>'Yes');
		if ( $_POST['squirrel_threat-submit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['squirrel_threat-title']));
			$options['remark'] = strip_tags(stripslashes($_POST['squirrel_threat-remark']));
			$options['do_link'] = $_POST['do_link'];
			update_option('widget_squirrel_threat', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$remark = htmlspecialchars($options['remark'], ENT_QUOTES);
		$do_link = $options['do_link'];
		
		echo '<p style="text-align:right;"><label for="squirrel_threat-title">' . __('Title:') . ' <input style="width: 200px;" id="squirrel_threat-title" name="squirrel_threat-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="squirrel_threat-remark">' . __('Remark:', 'widgets') . ' <input style="width: 200px;" id="squirrel_threat-remark" name="squirrel_threat-remark" type="text" value="'.$remark.'" /></label></p>';
		
		$checked = $do_link == 'Yes' ? ' CHECKED' : '';
		echo '<p style="text-align:right;"><label for="squirrel_threat-remark">' . __('Link to explanation:', 'widgets') . ' <input id="squirrel_threat-link" name="do_link" type="checkbox" value="Yes"'.$checked.' /> </label></p>';
		
		echo '<input type="hidden" id="squirrel_threat-submit" name="squirrel_threat-submit" value="1" />';
	}
	
	register_sidebar_widget(array('Suicide Squirrel Threat', 'widgets'), 'widget_squirrel_threat');
	register_widget_control(array('Suicide Squirrel Threat', 'widgets'), 'widget_squirrel_threat_control', 300, 130);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_squirrel_threat_init');

?>