<?php
/**
 * operation_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\widget;
use cenozo\lib, cenozo\log;

/**
 * widget operation list
 * 
 * @package cenozo\ui
 */
class operation_list extends base_list
{
  /**
   * Constructor
   * 
   * Defines all variables required by the operation list.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'operation', $args );
    
    $this->add_column( 'type', 'string', 'type', true );
    $this->add_column( 'subject', 'string', 'subject', true );
    $this->add_column( 'name', 'string', 'name', true );
    $this->add_column( 'restricted', 'boolean', 'restricted', false );
    $this->add_column( 'description', 'text', 'description', false, false, 'left' );
  }

  /**
   * Set the rows array needed by the template.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();
    
    foreach( $this->get_record_list() as $record )
    {
      $this->add_row( $record->id,
        array( 'type' => $record->type,
               'subject' => $record->subject,
               'name' => $record->name,
               'restricted' => $record->restricted,
               'description' => $record->description ) );
    }

    $this->finish_setting_rows();
  }
}
?>
