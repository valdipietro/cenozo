<?php
/**
 * cenozo.ini.php
 * 
 * Defines initialization settings for cenozo.
 * DO NOT edit this file, to override these settings use cenozo.local.ini.php instead.
 * When this file is loaded it only defines setting values if they are not already defined.
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo
 */

global $SETTINGS;

// Framework software version (is never overridded by the application's ini file)
$fwk_settings['general']['cenozo_version'] = '0.1.5';

// When set to true all operations are disabled
$fwk_settings['general']['maintenance_mode'] = false;

// always leave as false when running as production server
$fwk_settings['general']['development_mode'] = false;

$fwk_settings['path']['COOKIE'] = substr( $_SERVER['SCRIPT_NAME'], 0, -9 );

// the location of cenozo internal path
$fwk_settings['path']['CENOZO'] = '/usr/local/lib/cenozo';

// the location of libraries
$fwk_settings['path']['ADODB'] = '/usr/local/lib/adodb';

// javascript and css paths
$fwk_settings['url']['JS'] = $SETTINGS['url']['CENOZO'].'/js';
$fwk_settings['url']['CSS'] = $SETTINGS['url']['CENOZO'].'/css';

// javascript libraries
$fwk_settings['version']['JQUERY'] = '1.4.4';
$fwk_settings['version']['JQUERY_UI'] = '1.8.9';

$fwk_settings['url']['JQUERY'] = '/jquery';
$fwk_settings['url']['JQUERY_UI'] = $fwk_settings['url']['JQUERY'].'/ui';
$fwk_settings['url']['JQUERY_PLUGINS'] = $fwk_settings['url']['JQUERY'].'/plugins';
$fwk_settings['path']['JQUERY_UI_THEMES'] = '/var/www/jquery/ui/css';

$fwk_settings['url']['JQUERY_JS'] = 
  $fwk_settings['url']['JQUERY'].'/jquery-'.$fwk_settings['version']['JQUERY'].'.min.js';
$fwk_settings['url']['JQUERY_UI_JS'] =
  $fwk_settings['url']['JQUERY_UI'].'/js/jquery-ui-'.
  $fwk_settings['version']['JQUERY_UI'].'.custom.min.js';

$fwk_settings['url']['JQUERY_LAYOUT_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/layout.js';
$fwk_settings['url']['JQUERY_COOKIE_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/cookie.js';
$fwk_settings['url']['JQUERY_HOVERINTENT_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/hoverIntent.js';
$fwk_settings['url']['JQUERY_METADATA_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/metadata.js';
$fwk_settings['url']['JQUERY_FLIPTEXT_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/flipText.js';
$fwk_settings['url']['JQUERY_EXTRUDER_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/extruder.js';
$fwk_settings['url']['JQUERY_JEDITABLE_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/jeditable.js';
$fwk_settings['url']['JQUERY_TIMEPICKER_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/timepicker.js';
$fwk_settings['url']['JQUERY_RIGHTCLICK_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/rightClick.js';
$fwk_settings['url']['JQUERY_TOOLTIP_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/tooltip.js';
$fwk_settings['url']['JQUERY_FULLCALENDAR_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/fullcalendar.js';
$fwk_settings['url']['JQUERY_FONTSCALE_JS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/fontscale.js';

// css files
$fwk_settings['url']['JQUERY_UI_THEMES'] = $fwk_settings['url']['JQUERY_UI'].'/css';
$fwk_settings['url']['JQUERY_FULLCALENDAR_CSS'] =
  $fwk_settings['url']['JQUERY_PLUGINS'].'/fullcalendar.css';

// the location of log files
$fwk_settings['path']['LOG_FILE'] = '/var/local/cenozo/log';

// the location of the compiled template cache
$fwk_settings['path']['TEMPLATE_CACHE'] = '/tmp/cenozo'.$SETTINGS['path']['APPLICATION'];

// database settings
$fwk_settings['db']['driver'] = 'mysql';
$fwk_settings['db']['server'] = 'localhost';
$fwk_settings['db']['username'] = 'cenozo';
$fwk_settings['db']['password'] = '';
$fwk_settings['db']['database'] = 'cenozo';
$fwk_settings['db']['prefix'] = '';

// ldap settings
$fwk_settings['ldap']['server'] = 'localhost';
$fwk_settings['ldap']['port'] = 389;
$fwk_settings['ldap']['base'] = '';
$fwk_settings['ldap']['username'] = '';
$fwk_settings['ldap']['password'] = '';
$fwk_settings['ldap']['active_directory'] = true;

// themes
$fwk_settings['interface']['default_theme'] = 'smoothness';

// now put these settings in the global settings variable

// temporarily put all existing settings aside
if( isset( $SETTINGS ) && is_array( $SETTINGS ) )
{
  $app_settings = $SETTINGS;
}

// create all setting categories
$SETTINGS = array();

// put in all cenozo settings, then the application settings
foreach( array_merge( array_keys( $fwk_settings ), array_keys( $app_settings ) ) as $category )
{
  $SETTINGS[$category] = array();
  if( array_key_exists( $category, $fwk_settings ) )
    $SETTINGS[$category] = array_merge( $SETTINGS[$category], $fwk_settings[$category] );
  if( array_key_exists( $category, $app_settings ) )
    $SETTINGS[$category] = array_merge( $SETTINGS[$category], $app_settings[$category] );
}
?>
