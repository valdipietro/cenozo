<?php
/**
 * base_report.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\ui
 * @filesource
 */

namespace cenozo\ui\pull;
use cenozo\lib, cenozo\log;

/**
 * Base class for all reports.
 * 
 * Reports are built by gathering all data for the report in the constructor and building
 * the report from that data in the {@link finish} method.
 * 
 * @abstract
 * @package cenozo\ui
 */
abstract class base_report extends \cenozo\ui\pull
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject to retrieve the primary information from.
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $subject, $args )
  {
    parent::__construct( $subject, 'report', $args );
    $this->report = lib::create( 'business\report' );
  }

  /**
   * Returns the report's name (always the same as the report's full name)
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_file_name()
  {
    return $this->get_full_name();
  }
  
  /**
   * Returns the report type (xls, xlsx, html, pdf or csv)
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_data_type()
  {
    return $this->get_argument( 'format' );
  }
  
  /**
   * Adds a title to the report.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $title
   * @access public
   */
  protected function add_title( $title )
  {
    array_push( $this->report_titles, $title );
  }

  /**
   * Adds a table to the report.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $title The title of the report.
   * @param array $header The header row naming each column.
   * @param array $contents The contents of the table.
   * @param array $footer The footer of the table (for each column).
   * @param array $blanks Which rows to skip, leaving them blank
   * @access public
   */
  protected function add_table(
    $title = NULL, $header = array(), $contents = array(), $footer = array(), $blanks = array() )
  {
    array_push( $this->report_tables,
      array( 'title' => $title,
             'header' => $header,
             'contents' => $contents,
             'footer' => $footer,
             'blanks' => $blanks ) );
  }

  /**
   * Builds the report based on the tables built by child classes.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return associative array
   * @access public
   */
  public function finish()
  {
    $util_class_name = lib::get_class_name( 'util' );

    // determine the widest table size
    $max = 1;
    foreach( $this->report_tables as $table )
    {
      if( is_array( $table['header'] ) )
      {
        $width = max(
          count( $table['header'] ),
          is_array( $table['contents'] ) ? count( $table['contents'][0] ) : 0,
          count( $table['footer'] ) );
        if( $max < $width ) $max = $width;
      }
    }
    
    // add in the title(s)
    $row = 1;
    $max_col = 1 < $max ? chr( 64 + $max ) : false;

    $main_title = $this->get_heading();
    if( 'true' == $this->get_argument( 'has_restrict_site' ) )
    {
      $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
      if( $restrict_site_id )
      {
        $db_site = lib::create( 'database\site', $restrict_site_id );
        $main_title = $main_title.' for '.$db_site->name;
      }
      else
      {
        $main_title = $main_title.' for All Sites';
      }
    }
      
    $this->report->set_size( 16 );
    $this->report->set_bold( true );
    $this->report->set_horizontal_alignment( 'center' );
    if( $max_col ) $this->report->merge_cells( 'A'.$row.':'.$max_col.$row );
    $this->report->set_cell( 'A'.$row, $main_title );

    $row++;

    $now_datetime_obj = $util_class_name::get_datetime_object();
    $time_title = 'Generated on '.$now_datetime_obj->format( 'Y-m-d' ).
                   ' at '.$now_datetime_obj->format( 'H:i T' );
    $this->report->set_size( 14 );
    $this->report->set_bold( false );
    if( $max_col ) $this->report->merge_cells( 'A'.$row.':'.$max_col.$row );
    $this->report->set_cell( 'A'.$row, $time_title );

    $row++;

    if( 'true' == $this->get_argument( 'has_restrict_dates' ) )
    {
      $restrict_start_date = $this->get_argument( 'restrict_start_date' );
      $restrict_end_date = $this->get_argument( 'restrict_end_date' );
      $now_datetime_obj = $util_class_name::get_datetime_object();
      if( $restrict_start_date )
      {
        $start_datetime_obj = $util_class_name::get_datetime_object( $restrict_start_date );
        if( $start_datetime_obj > $now_datetime_obj )
        {
          $start_datetime_obj = clone $now_datetime_obj;
        }
      }
      if( $restrict_end_date )
      {
        $end_datetime_obj = $util_class_name::get_datetime_object( $restrict_end_date );
        if( $end_datetime_obj > $now_datetime_obj )
        {
          $end_datetime_obj = clone $now_datetime_obj;
        }
      }

      $date_title = '';
      if( $restrict_start_date && $restrict_end_date )
      {
        if( $end_datetime_obj < $start_datetime_obj )
        {
          $start_datetime_obj = $util_class_name::get_datetime_object( $restrict_end_date );
          $end_datetime_obj = $util_class_name::get_datetime_object( $restrict_start_date );
        }
        if( $start_datetime_obj == $end_datetime_obj ) 
        {
          $date_title = 'Dated for '.$start_datetime_obj->format( 'Y-m-d' );
        }
        else
        {
          $date_title = 'Dated from '.$start_datetime_obj->format( 'Y-m-d' ).' to '.
                   $end_datetime_obj->format( 'Y-m-d' );
        }       
      }
      else if( $restrict_start_date && !$restrict_end_date ) 
      {
        if( $start_datetime_obj == $now_datetime_obj )
        {
          $date_title = 'Dated for '.$start_datetime_obj->format( 'Y-m-d' );
        }
        else
        {
          $date_title = 'Dated from '.$start_datetime_obj->format( 'Y-m-d' ).' to '.
            $now_datetime_obj->format( 'Y-m-d' );
        }    
      }
      else if( !$restrict_start_date && $restrict_end_date )
      {
        $date_title = 'Dated up to '.$end_datetime_obj->format( 'Y-m-d' );
      }
      else
      {
        $date_title = 'No date restriction';
      }
      if( $max_col ) $this->report->merge_cells( 'A'.$row.':'.$max_col.$row );
      $this->report->set_cell( 'A'.$row, $date_title );
      $row++;
    }

    $this->report->set_size( 14 );
    $this->report->set_bold( false );

    foreach( $this->report_titles as $title )
    {
      if( $max_col ) $this->report->merge_cells( 'A'.$row.':'.$max_col.$row );
      $this->report->set_cell( 'A'.$row, $title );
      $row++;
    }

    $this->report->set_size( NULL );
    
    // add in each table
    foreach( $this->report_tables as $table )
    {
      print '<h1>'.$table['title'].'</h1><br>';
      $width = max(
        count( $table['header'] ),
        count( $table['contents'] ),
        count( $table['footer'] ) );
      $max_col = 1 < $max ? chr( 64 + $width ) : false;

      // always skip a row before each table
      $row++;

      $this->report->set_horizontal_alignment( 'center' );
      $this->report->set_bold( true );

      // put in the table title
      if( !is_null( $table['title'] ) )
      {
        if( $max_col ) $this->report->merge_cells( 'A'.$row.':'.$max_col.$row );
        $this->report->set_cell( 'A'.$row, $table['title'] );
        $row++;
      }

      // put in the table header
      if( count( $table['header'] ) )
      {
        $this->report->set_background_color( 'CCCCCC' );
        $col = 'A';
        foreach( $table['header'] as $header )
        {
          $this->report->set_horizontal_alignment( 'A' == $col ? 'left' : 'center' );
          $this->report->set_cell( $col.$row, $header );
          $col++;
        }
        $row++;
      }

      $this->report->set_bold( false );
      $this->report->set_background_color( NULL );
      
      $first_content_row = $row;

      // put in the table contents
      $contents_are_numeric = array();
      if( count( $table['contents'] ) )
      {
        $content_row = 0;
        $insert_row = count( $table['blanks'] ) > 0 ? true : false;
        foreach( $table['contents'] as $contents )
        {
          $col = 'A';
          foreach( $contents as $content )
          {
            $this->report->set_horizontal_alignment( 'A' == $col ? 'left' : 'center' );
            $this->report->set_cell( $col.$row, $content );
            if( !array_key_exists( $col, $contents_are_numeric ) )
              $contents_are_numeric[$col] = false;
            $contents_are_numeric[$col] = $contents_are_numeric[$col] || is_numeric( $content );
            $col++;
          }
          
          if( $insert_row && in_array( $content_row, $table['blanks'] ) ) $row++;    
                    
          $content_row++;
          $row++;
        }
      }
      $last_content_row = $row - 1;
      
      $this->report->set_bold( true );

      // put in the table footer
      if( count( $table['footer'] ) )
      {
        $col = 'A';
        foreach( $table['footer'] as $footer )
        {
          // the footer may be a function, convert if necessary
          if( preg_match( '/[0-9a-zA-Z_]+\(\)/', $footer ) )
          {
            if( $first_content_row == $last_content_row + 1 || !$contents_are_numeric[ $col ] )
            {
              $footer = 'N/A';
            }
            else
            {
              $coordinate = sprintf( '%s%s:%s%s',
                                     $col,
                                     $first_content_row,
                                     $col,
                                     $last_content_row );
              $footer = '='.preg_replace( '/\(\)/', '('.$coordinate.')', $footer );
            }
          }

          $this->report->set_horizontal_alignment( 'A' == $col ? 'left' : 'center' );
          $this->report->set_cell( $col.$row, $footer );
          $col++;
        }
        $row++;
      }
    }

    return $this->report->get_file( $this->get_argument( 'format' ) );
  }

  /**
   * An array of all titles to put in the report.
   * @var array $report_titles
   * @access private
   */
  private $report_titles = array();

  /**
   * An associative array of all reports to put in the report.
   * @var array $report_titles
   * @access private
   */
  private $report_tables = array();

  /**
   * An instance of the PHPExcel class used to create the report.
   * @var array $report_titles
   * @access private
   */
  protected $report;
}
?>
