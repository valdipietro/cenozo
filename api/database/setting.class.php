<?php
/**
 * setting.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\database
 * @filesource
 */

namespace cenozo\database;
use cenozo\lib, cenozo\log;

/**
 * setting: record
 *
 * @package cenozo\database
 */
class setting extends record
{
  /**
   * Get an setting given it's category and name
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $category
   * @param string $name
   * @static
   * @access public
   */
  public static function get_setting( $category, $name )
  {
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'category', '=', $category );
    $modifier->where( 'name', '=', $name );

    $id = static::db()->get_one(
      sprintf( 'SELECT id FROM %s %s',
               static::get_table_name(),
               $modifier->get_sql() ) );

    return is_null( $id ) ? NULL : new static( $id );
  }
}
?>
