<?php
/**
 * user_new.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\push;
use cenozo\lib, cenozo\log;

/**
 * push: user new
 *
 * Create a new user.
 * @package cenozo\ui
 */
class user_new extends base_new
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments.  This may include "ignore_existing" which will ignore any
   *                    errors caused by existing user conflicts.
   * @access public
   */
  public function __construct( $args )
  {
    // remove the role id from the columns and use it to create the user's initial role
    if( isset( $args['columns'] ) &&
        isset( $args['columns']['role_id'] ) && isset( $args['columns']['site_id'] ) )
    {
      $this->role_id = $args['columns']['role_id'];
      $this->site_id = $args['columns']['site_id'];
      unset( $args['columns']['role_id'] );
      unset( $args['columns']['site_id'] );
    }

    parent::__construct( 'user', $args );
  }

  /**
   * Executes the push.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   * @throws exception\notice
   */
  public function finish()
  {
    $columns = $this->get_argument( 'columns' );
    
    // make sure the name, first name and last name are not blank
    if( !array_key_exists( 'name', $columns ) || 0 == strlen( $columns['name'] ) )
      throw lib::create( 'exception\notice',
        'The user\'s user name cannot be left blank.', __METHOD__ );
    if( !array_key_exists( 'first_name', $columns ) || 0 == strlen( $columns['first_name'] ) )
      throw lib::create( 'exception\notice',
        'The user\'s first name cannot be left blank.', __METHOD__ );
    if( !array_key_exists( 'last_name', $columns ) || 0 == strlen( $columns['last_name'] ) )
      throw lib::create( 'exception\notice',
        'The user\'s last name cannot be left blank.', __METHOD__ );

    // add the user to ldap
    $ldap_manager = lib::create( 'business\ldap_manager' );
    try
    {
      $ldap_manager->new_user(
        $columns['name'], $columns['first_name'], $columns['last_name'], 'password' );
    }
    catch( \cenozo\exception\ldap $e )
    {
      // catch already exists exceptions, no need to report them
      if( !$e->is_already_exists() ) throw $e;
    }

    try
    {
      parent::finish();
    }
    catch( \cenozo\exception\notice $e )
    { // ignore unique error if "ignore_existing" argument is true
      $previous = $e->get_previous();
      if( is_null( $previous ) ||
          !$previous->is_duplicate_entry() ||
          !$this->get_argument( 'ignore_existing', false ) ) throw $e;
    }

    if( !is_null( $this->site_id ) && !is_null( $this->role_id ) )
    { // add the initial role to the new user
      $user_class_name = lib::get_class_name( 'database\user' );
      $db_user = $user_class_name::get_unique_record( 'name', $columns['name'] );
      $db_access = lib::create( 'database\access' );
      $db_access->user_id = $db_user->id;
      $db_access->site_id = $this->site_id;
      $db_access->role_id = $this->role_id;

      try
      {
        $db_access->save();
      }
      catch( \cenozo\exception\database $e )
      { // ignore unique error if "ignore_existing" argument is true
        if( !$e->is_duplicate_entry() ||
            !$this->get_argument( 'ignore_existing', false ) ) throw $e;
      }
    }
  }

  /**
   * The initial site to give the new user access to
   * @var int
   * @access protected
   */
  protected $site_id = NULL;

  /**
   * The initial role to give the new user
   * @var int
   * @access protected
   */
  protected $role_id = NULL;
}
?>
