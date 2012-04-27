<?php
/**
 * push.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui;
use cenozo\lib, cenozo\log;

/**
 * The base class of all push operations
 * 
 * @package cenozo\ui
 */
abstract class push extends operation
{
  /**
   * Constructor
   * 
   * Defines all variables available in push operations
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject of the operation.
   * @param string $name The name of the operation.
   * @param array $args An associative array of arguments to be processed by the push operation.
   * @access public
   */
  public function __construct( $subject, $name, $args )
  {
    // don't pass the arguments to the parent since we need to convert noid parameters
    parent::__construct( 'push', $subject, $name, NULL );
    $this->arguments = $this->convert_from_noid( $args );

    // by default all push operations use transactions
    lib::create( 'business\session' )->set_use_transaction( true );
  }

  // TODO: document
  public function finish()
  {
    // if this operation was not received by machine then send a machine request
    if( !$this->machine_request_received && $this->machine_request_enabled )
      $this->send_machine_request();
  }

  // TODO: document
  protected function convert_to_noid( $args )
  {
    foreach( $args as $arg_key => $arg_value )
    {
      if( 'columns' == $arg_key )
      { // columns array may contain foreign keys
        foreach( $arg_value as $column_name => $column_value )
        {
          if( '_id' == substr( $column_name, -3 ) )
          {
            $subject = substr( $column_name, 0, -3 );
            $class_name = lib::get_class_name( 'database\\'.$subject );
            $args['noid']['columns'][$subject] =
              $class_name::get_unique_from_primary_key( $column_value );
            unset( $args['columns'][$column_name] );
          }
        }
      }
      else if( 'id' == $arg_key || '_id' == substr( $arg_key, -3 ) )
      { // convert the primary key and foreign keys
        $column_name = $arg_key;
        $column_value = $arg_value;
        $subject = 'id' == $column_name ? $this->get_subject() : substr( $column_name, 0, -3 );
        $class_name = lib::get_class_name( 'database\\'.$subject );
        $args['noid'][$subject] =
          $class_name::get_unique_from_primary_key( $column_value );
        unset( $args[$column_name] );
      }
    }

    return $args;
  }

  // TODO: document
  protected function convert_from_noid( $args )
  {
    if( array_key_exists( 'noid', $args ) )
    {
      $this->machine_request_received = true;
      
      // remove the noid argument
      $noid = $args['noid'];
      unset( $args['noid'] );
      if( !is_array( $noid ) )
        throw lib::create( 'exception\runtime', 'Argument noid must be an array.', __METHOD__ );

      foreach( $noid as $noid_key => $noid_value )
      {
        if( 'columns' == $noid_key )
        { // foreign key found in columns array
          foreach( $noid_value as $subject => $unique_key )
          {
            $class_name = lib::get_class_name( 'database\\'.$subject );
            $args['columns'][$subject.'_id'] = 
              $class_name::get_primary_from_unique_key( $unique_key );
          }
        }
        else // primary and foreign keys
        {
          $subject = $noid_key;
          $unique_key = $noid_value;
          $arg_key = $this->get_subject() == $subject ? 'id' : $subject.'_id';
          $class_name = lib::get_class_name( 'database\\'.$subject );
          $args[$arg_key] = 
            $class_name::get_primary_from_unique_key( $unique_key );
        }
      }
    }

    return $args;
  }
      
  // TODO: document
  public function set_machine_request_enabled( $enabled )
  {
    $this->machine_request_enabled = $enabled;
  }

  // TODO: document
  public function set_machine_request_url( $url )
  {
    $this->machine_request_url = $url;
  }

  // TODO: document
  protected function send_machine_request()
  {
    // make sure the url is set
    if( is_null( $this->machine_request_url ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Tried to send %s %s machine request without setting URL',
          $this->get_subject(),
          $this->get_name() ),
        __METHOD__ );

    // we'll need the arguments to send to Sabretooth/Beartooth
    $args = $this->convert_to_noid( $this->arguments );

    try
    {
      $cenozo_manager = lib::create( 'business\cenozo_manager', $this->machine_request_url );
      $cenozo_manager->push( $this->get_subject(), $this->get_name(), $args );
    }
    catch( \cenozo\exception\base_exception $e )
    {
      // log the error, but otherwise ignore it
      log::crit( $e->to_string() );
    }
  }

  // TODO: document
  private $machine_request_url = NULL;

  // TODO: document
  private $machine_request_enabled = false;

  // TODO: document
  private $machine_request_received = false;
}
?>
