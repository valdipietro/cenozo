<?php
/**
 * base_view.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\widget;
use cenozo\lib, cenozo\log;

/**
 * Base class for widgets which view current or new records.
 * 
 * @abstract
 * @package cenozo\ui
 */
abstract class base_view extends base_record
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject being viewed.
   * @param string $name The name of the operation.
   * @param array $args An associative array of arguments to be processed by th  widget
   * @throws exception\argument
   * @access public
   */
  public function __construct( $subject, $name, $args )
  {
    parent::__construct( $subject, $name, $args );
    
    if( 'view' == $this->get_name() )
    {
      // make sure we have an id (we don't actually need to use it since the parent does)
      $this->get_argument( 'id' );

      // determine properties based on the current user's permissions
      $operation_class_name = lib::get_class_name( 'database\operation' );
      $session = lib::create( 'business\session' );
      $this->editable = $session->is_allowed(
        $operation_class_name::get_operation( 'push', $subject, 'edit' ) );
      $this->removable = $session->is_allowed( 
        $operation_class_name::get_operation( 'push', $subject, 'delete' ) );

      $this->set_heading( 'Viewing '.$this->get_subject().' details' );
    }
    else // 'add' == $this->get_name()
    {
      $this->addable = true;
      $this->editable = false;
      $this->removable = false;
      $this->set_heading( 'Creating a new '.$this->get_subject() );
    }
  }
  
  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();

    // define all template variables for this widget
    $this->set_variable( 'editable', $this->editable );
    $this->set_variable( 'removable', $this->removable );
    $this->set_variable( 'addable', $this->addable );

    // keep track of now many of these widgets have been finished
    self::$base_view_count++;
    $this->set_variable( 'base_view_count', self::$base_view_count );
  }
  
  /**
   * Add an item to the view.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $item_id The item's id, can be one of the record's column names.
   * @param string $type The item's type, one of "boolean", "date", "time", "number", "string",
                   "text", "enum" or "constant"
   * @param string $heading The item's heading as it will appear in the view
   * @param string $note A note to add below the item.
   * @param string $note_is_error Whether the note is error text.
   * @access public
   */
  public function add_item(
    $item_id, $type, $heading = NULL, $note = NULL, $note_is_error = false )
  {
    $util_class_name = lib::get_class_name( 'util' );

    // add timezone info to the note if the item is a time or datetime
    if( 'time' == $type || 'datetime' == $type )
    {
      // build time time zone help text
      $date_obj = $util_class_name::get_datetime_object();
      $time_note = sprintf( 'Time is in %s\'s time zone (%s)',
                            lib::create( 'business\session' )->get_site()->name,
                            $date_obj->format( 'T' ) );
      $note = is_null( $note ) ? $time_note : $time_note.'<br>'.$note;
    }

    $this->items[$item_id] = array( 'type' => $type );
    if( !is_null( $heading ) ) $this->items[$item_id]['heading'] = $heading;
    if( !is_null( $note ) )
    {
      $this->items[$item_id]['note'] = $note;
      $this->items[$item_id]['note_is_error'] = $note_is_error;
    }
  }

  public function set_note( $item_id, $note = NULL, $note_is_error = false )
  {
    // make sure the item exists
    if( !array_key_exists( $item_id, $this->items ) )
      throw lib::create( 'exception\argument', 'item_id', $item_id, __METHOD__ );

    if( !is_null( $note ) )
    {
      $this->items[$item_id]['note'] = $note;
      $this->items[$item_id]['note_is_error'] = $note_is_error;
    }
    else
    {
      unset( $this->items[$item_id]['note'] );
      unset( $this->items[$item_id]['note_is_error'] );
    }
  }

  /**
   * Sets and item's value and additional data.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $item_id The item's id, can be one of the record's column names.
   * @param mixed $value The item's value.
   * @param boolean $required Whether the item can be left blank.
   * @param mixed $data For enum item types, an array of all possible values, for date types an
   *              associative array of min_date and/or max_date and for datetime types an
   *              associative array of min_datetime and/or max_datetime
   * @param boolean $force Whether to show enums even if there is only one possible value.
   * @throws exception\argument
   * @access public
   */
  public function set_item( $item_id, $value, $required = false, $data = NULL, $force = false )
  {
    // make sure the item exists
    if( !array_key_exists( $item_id, $this->items ) )
      throw lib::create( 'exception\argument', 'item_id', $item_id, __METHOD__ );
    
    $util_class_name = lib::get_class_name( 'util' );
    $type = $this->items[$item_id]['type'];
    
    // process the value so that it displays correctly
    if( 'boolean' == $type )
    {
      if( is_null( $value ) ) $value = '';
      else $value = $value ? 'Yes' : 'No';
    }
    else if( 'date' == $type )
    {
      if( strlen( $value ) )
      {
        $date_obj = $util_class_name::get_datetime_object( $value );
        $value = $date_obj->format( 'Y-m-d' );
      }
      else $value = '';
    }
    else if( 'time' == $type )
    {
      if( strlen( $value ) )
      {
        $date_obj = $util_class_name::get_datetime_object( $value );
        $value = $date_obj->format( 'H:i' );
      }
      else $value = '12:00';
    }
    else if( 'hidden' == $type )
    {
      if( is_bool( $value ) ) $value = $value ? 'true' : 'false';
    }
    else if( 'constant' == $type &&
             ( ( is_int( $value ) && 0 == $value ) ||
               ( is_string( $value ) && '0' == $value ) ) )
    {
      $value = ' 0';
    }
    else if( 'number' == $type )
    {
      $value = floatval( $value );
    }
    
    $this->items[$item_id]['value'] = $value;
    $this->items[$item_id]['required'] = $required;
    $this->items[$item_id]['force'] = $force;
    
    // if necessary process the $data argument
    if( 'enum' == $type )
    {
      $enum = $data;
      if( is_null( $enum ) )
        throw lib::create( 'exception\runtime',
          'Trying to set enum item without enum values.', __METHOD__ );
      
      // add a null entry (to the front of the array) if the item is not required
      if( !$required )
      {
        $enum = array_reverse( $enum, true );
        $enum['NULL'] = '';
        $enum = array_reverse( $enum, true );
      }
      $this->items[$item_id]['enum'] = $enum;
    }
    else if( 'date' == $type || 'datetime' == $type )
    {
      if( is_array( $data ) )
      {
        $date_limits = $data;
        if( array_key_exists( 'min_date', $date_limits ) )
          $this->items[$item_id]['min_date'] = $date_limits['min_date'];
        if( array_key_exists( 'max_date', $date_limits ) )
          $this->items[$item_id]['max_date'] = $date_limits['max_date'];
      }
    }
  }

  /**
   * Must be called after all items have been set.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish_setting_items()
  {
    $this->set_variable( 'item', $this->items );
  }

  /**
   * Set whether a new record can be added.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param boolean $enable
   * @access public
   */
  public function set_addable( $enable )
  {
    $this->addable = $enable;
  }

  /**
   * Set whether the record can be edited.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param boolean $enable
   * @access public
   */
  public function set_editable( $enable )
  {
    $this->editable = $enable;
  }

  /**
   * Set whether the record can be removed.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param boolean $enable
   * @access public
   */
  public function set_removable( $enable )
  {
    $this->removable = $enable;
  }
  
  /**
   * Determines which mode the widget is in.
   * Must be one of 'view', 'edit' or 'add'.
   * @var string
   * @access private
   */
  private $mode = 'view';

  /**
   * When in view mode, determines whether an edit button should be available.
   * @var boolean
   * @access protected
   */
  protected $editable = false;

  /**
   * When in view mode, determines whether a remove button should be available.
   * @var boolean
   * @access protected
   */
   protected $removable = false;

  /**
   * Used by the add mode to display add/cancel buttons.
   * @var boolean
   * @access protected
   */
   protected $addable = false;

  /**
   * An associative array where the key is a unique identifier (usually a column name) and the
   * value is an associative array which includes:
   * "heading" => the label to display
   * "type" => the type of variable (see {@link add_item} for details)
   * "value" => the value of the column
   * "enum" => all possible values if the item type is "enum"
   * "required" => boolean describes whether the value can be left blank
   * @var array
   * @access private
   */
  private $items = array();
  
  /**
   * Keeps track of how many base_view widgets have been finished
   * @var integer
   * @access private
   */
  private static $base_view_count = 0;
}
?>
