<?php
/**
 * contains_record.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui;

/**
 * Interface that specifies that a class is directly related to a single record.
 * @package cenozo\ui
 */
interface contains_record
{
  /**
   * Returns this object's record.
   * 
   * @return database\record
   * @access public
   */
  public function get_record();

  /**
   * Sets this object's record.
   * 
   * @param $record database\record
   * @access public
   */
  public function set_record( $record );
}
