<?php
/**
 * base_list_record.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\pull;
use cenozo\lib, cenozo\log;

/**
 * Base class for all pull operations which 'list records' pertaining to a single record.
 * 
 * @abstract
 * @package cenozo\ui
 */
abstract class base_list_record extends base_record
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $subject, $child, $args )
  {
    $this->child = $child;
    parent::__construct( $subject, 'list_'.$this->child, $args );
    $this->set_record( lib::create( 'database\\'.$this->get_subject(), $this->get_argument( 'id', NULL ) ) );
  }

  /**
   * Returns the data provided by this pull operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return associative array
   * @access public
   */
  public function finish()
  {
    $data = array();
    
    $child_list_method = 'get_'.$this->child.'_list';
    foreach( $this->get_record()->$child_list_method() as $db_record )
    {
      $item = array();
      foreach( $db_record->get_column_names() as $column ) $item[ $column ] = $db_record->$column;
      $data[] = $item;
    }

    return $data;
  }

  /**
   * This class always returns json format
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_data_type() { return "json"; }
  
  /**
   * The name of the items being listed.
   * @var string
   * @access protected
   */
  protected $child = NULL;
}
?>
