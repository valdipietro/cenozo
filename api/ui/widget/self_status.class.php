<?php
/**
 * self_status.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\widget;
use cenozo\lib, cenozo\log;

/**
 * widget self status
 * 
 * @package cenozo\ui
 */
class self_status extends \cenozo\ui\widget
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'self', 'status', $args );
    $this->show_heading( false );
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

    $util_class_name = lib::get_class_name( 'util' );

    $datetime_obj = $util_class_name::get_datetime_object();
    $this->set_variable( 'timezone_name', $datetime_obj->format( 'T' ) );
    $this->set_variable( 'timezone_offset',
      $util_class_name::get_timezone_object()->getOffset( $datetime_obj ) );
  }
}
?>
