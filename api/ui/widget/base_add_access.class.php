<?php
/**
 * base_add_access.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\widget;
use cenozo\lib, cenozo\log;

/**
 * Base class for adding access to sites and users.
 * 
 * @package cenozo\ui
 */
class base_add_access extends base_add_list
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The operation's subject.
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $subject, $args )
  {
    parent::__construct( $subject, 'access', $args );
    $this->show_heading( false );
    
    try
    {
      // build the role list widget
      $this->role_list = lib::create( 'ui\widget\role_list', $args );
      $this->role_list->set_parent( $this, 'edit' );
      $this->role_list->set_heading( 'Select roles to grant' );
    }
    catch( \cenozo\exception\permission $e )
    {
      $this->role_list = NULL;
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

    if( !is_null( $this->role_list ) )
    {
      $this->role_list->finish();
      $this->set_variable( 'role_list', $this->role_list->get_variables() );
    }
  }
  
  /**
   * Overrides the role list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  public function determine_role_count( $modifier = NULL )
  {
    $role_class_name = lib::get_class_name( 'database\role' );
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'tier', '<=', lib::create( 'business\session' )->get_role()->tier );
    return $role_class_name::count( $modifier );
  }

  /**
   * Overrides the role list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  public function determine_role_list( $modifier = NULL )
  {
    $role_class_name = lib::get_class_name( 'database\role' );
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'tier', '<=', lib::create( 'business\session' )->get_role()->tier );
    return $role_class_name::select( $modifier );
  }

  /**
   * The role list widget used to define the access type.
   * @var role_list
   * @access protected
   */
  protected $role_list = NULL;
}
?>
