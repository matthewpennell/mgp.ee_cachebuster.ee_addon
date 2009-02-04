<?php  if ( ! defined('EXT')) exit('No direct script access allowed');
/**
 * Force the browser to download the latest version of a file
 *
 * An ExpressionEngine Extension that causes the browser to ignore cached versions of a file and
 * instead download the latest version
 *
 * @package		ExpressionEngine
 * @author		Matthew Pennell
 * @copyright	Copyright (c) 2008, Matthew Pennell
 * @license		http://creativecommons.org/licenses/by-sa/3.0/
 * @link		http://www.thewatchmakerproject.com/blog/new-expressionengine-extension-cachebuster
 * @since		Version 0.1
 * @filesource
 * 
 * This work is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/
 * or send a letter to Creative Commons, 171 Second Street, Suite 300,
 * San Francisco, California, 94105, USA.
 * 
 */
class Cachebuster {

	var $settings		= array();
	var $title			= 'Cachebuster';
	var $name			= 'Cachebuster';
	var $version		= '1.0';
	var $description	= 'Force visitors to load the latest version of a file instead of cached ones.';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.thewatchmakerproject.com/blog/new-expressionengine-extension-cachebuster';

	/**
	 * Constructor
	 */
	function Cachebuster($settings = '')
	{
		$this->settings = $settings;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Register hooks by adding them to the database
	 */
	function activate_extension()
	{
		global $DB;

		// default settings
		$settings =	array();
		$settings['version'] = '1';
		$settings['css'] = 'yes';
		$settings['css_files'] = '';
		$settings['js'] = 'yes';
		$settings['js_files'] = '';
		
		$hook = array(
						'extension_id'	=> '',
						'class'			=> __CLASS__,
						'method'		=> 'add_version_querystring',
						'hook'			=> 'before_display_final_output',
						'settings'		=> serialize($settings),
						'priority'		=> 1,
						'version'		=> $this->version,
						'enabled'		=> 'y'
					);
	
		$DB->query($DB->insert_string('exp_extensions',	$hook));
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * No updates yet.
	 * Manual says this function is required.
	 * @param string $current currently installed version
	 */
	function update_extension($current = '')
	{
		global $DB, $EXT;

		if ($current < '1.0')
		{
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE class = '".$DB->escape_str(__CLASS__)."'");
			
			$this->settings = unserialize($query->row['settings']);
			unset($this->settings['prefix']);
			
			$DB->query($DB->update_string('exp_extensions', array('settings' => serialize($this->settings), 'version' => $this->version), array('class' => __CLASS__)));
		}
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Uninstalls extension
	 */
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '".__CLASS__."'");
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * EE extension settings
	 * @return array
	 */
	function settings()
	{
		$settings = array();
		
		$settings['version'] = "1";
		$settings['css'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
		$settings['css_files'] = "";
		$settings['js'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
		$settings['js_files'] = "";
		
		return $settings;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Parses the buffered HTML browser output and appends a version number to
	 * any linked stylesheets or script files.
	 * 
	 * @param array $html The contents of the buffer
	 */
	function add_version_querystring($html)
	{
		global $EXT, $LANG, $OUT, $FNS;
		
		$find = array();
		$replace = array();

		if ($this->settings['css'] == 'yes')
		{
			$filenames = explode(",", $this->settings['css_files']);
			foreach ($filenames as $file)
			{
				$file = trim($file);
				if ($file)
				{
					$find[] = (stristr($file, '.css')) ? $file . '"' : $file . '.css"';
					$replace[] = (stristr($file, '.css')) ? $file . '?v=' . $this->settings['version'] . '"' : $file . '.css?v=' . $this->settings['version'] . '"';
				}
			}
		}
		
		if ($this->settings['js'] == 'yes')
		{
			$filenames = explode(",", $this->settings['js_files']);
			foreach ($filenames as $file)
			{
				$file = trim($file);
				if ($file)
				{
					$find[] = (stristr($file, '.js')) ? $file . '"' : $file . '.js"';
					$replace[] = (stristr($file, '.js')) ? $file . '?v=' . $this->settings['version'] . '"' : $file . '.js?v=' . $this->settings['version'] . '"';
				}
			}
		}

		return str_replace($find, $replace, $html);

		$EXT->end_script = TRUE;
	
	}
	
	// --------------------------------------------------------------------
	
}
// END CLASS Cachebuster

/* End of file ext.cachebuster.php */
/* Location: ./system/extensions/ext.cachebuster.php */