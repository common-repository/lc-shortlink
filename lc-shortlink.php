<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
   Plugin Name:  LC Shortlink
   Plugin URL: 
   Description: This is a user-friendly, lightweight plugin for Shortlinks. It is designed to provide outstanding performance without affecting your site's speed. Additionally, it is the most efficient way to redirect functions.
   Version: 1.0.2
   Author: Loordumary B
   Author URI:
*/

/**
 * LC SHORTLINK GENERATION MAIN CLASS
 */
class lc_shortlink_gen {

   function __construct() {
      register_activation_hook( __FILE__, array( $this, 'lc_shortlink_install' ) );
      register_deactivation_hook( __FILE__, array( $this, 'lc_shortlink_uninstall' ) );

      add_action( 'init', array( $this, 'lc_shortlink_post_creation' ) );
      add_action( 'template_redirect', array( $this, 'lc_shortlink_redirect_to_short_link' ) );
   }

   public function lc_shortlink_install() {

   }

   public function lc_shortlink_uninstall() {

   }

   public function lc_shortlink_post_creation() {

      $labels = array(
         'name'                => __( 'All Links'),
         'singular_name'       => __( 'LC Shortlink'),
         'add_new'             => __( 'Add New Link'),
         'add_new_item'        => __( 'Add New Link'),
         'edit_item'           => __( 'Edit Link'),
         'new_item'            => __( 'New Link'),
         'all_items'           => __( 'All Links'),
         'view_item'           => __( 'View Link'),
         'search_items'        => __( 'Search Link'),
         'not_found'           => __( 'No reviews found'),
         'not_found_in_trash'  => __( 'No reviews found in Trash'),
         'menu_name'           => __( 'LC Shortlinks')
      );

      $supports = array( 'title' );

      $args = array(
         'labels'              => $labels,
         'public'              => true,
         'show_ui'             => true,
         'show_in_menu'        => true,
         'rewrite'             => array( 'slug' => 'lcshortlink' ),
         'capability_type'     => 'post',
         'has_archive'         => false,
         'hierarchical'        => false,
         'menu_position'       => null,
         'supports'            => $supports,
         'menu_icon'           => 'dashicons-admin-links',

         'exclude_from_search' => true,
         'publicly_queryable'  => false,
         'query_var'           => false
      );

      register_post_type( 'lcshortlink', $args );

      add_action( 'add_meta_boxes', array( $this, 'lc_shortlink_meta_boxes' ) );
      add_action( 'save_post', array( $this, 'lc_shortlink_meta_box_save' ) );
      add_filter( 'manage_lcshortlink_posts_columns', array( $this, 'lc_shortlink_custom_columns_list' ) );
      add_action( 'manage_lcshortlink_posts_custom_column', array( $this, 'lc_shortlink_custom_column_values' ), 10, 2);

      add_filter( 'post_row_actions', array( $this, 'lc_shortlink_remove_row_actions' ), 10, 1 );
      add_filter( 'posts_join', array( $this, 'lc_shortlink_search_join' ), 10, 1 );
      add_filter( 'posts_where', array( $this, 'lc_shortlink_search_where' ), 10, 1 );
   }

   public function lc_shortlink_meta_boxes() {

      add_meta_box( 'lc-slink-info', 'Short Link Information', array( $this, 'lc_shortlink_information' ), 'lcshortlink', 'normal', 'high' );
   }

   public function lc_shortlink_information( $post ) {
      $post_id = $post->ID;
      
      $is_short_link = get_post_meta( $post_id, 'is_short_link', true );
      $src_link = get_post_meta( $post_id, 'src_link', true );
      $sort_link = get_post_meta( $post_id, 'sort_link', true );
      $internal_link = get_post_meta( $post_id, 'sort_link', true );
      $query_param = get_post_meta( $post_id, 'query_param', true );

      $is_short_link = ( $is_short_link != "" )?$is_short_link:"shortlink";
      $site_url = site_url()."/";
      $src_link = str_replace( $site_url, "", $src_link );      
      $internal_link = str_replace( $site_url, "", $internal_link );     
      ?>
         <style>
            #post-body-content { display: none!important; }
            .lcregular-text-label { padding-bottom: 5px; display: block; }
            .type_shortlink, .type_internal_redirection, .type_external_redirection { display: none; }
            .type_shortlink.lcvisible, .type_internal_redirection.lcvisible, .type_external_redirection.lcvisible { display: block; }
         </style>
         <div>
            <table class="form-table">
               <tbody>
                  <tr>
                     <th>
                        <label for="is_short_link">Would you like this as a redirection or a short link?</label>
                     </th>
                     <td>
                        <select id="is_short_link" name="is_short_link" class="regular-text" onchange="lclink_usage(this.value)">
                           <option value="shortlink" <?php echo ( $is_short_link == "shortlink" )? "selected": ""; ?> >Short link</option>
                           <option value="internal_redirection" <?php echo ( $is_short_link == "internal_redirection" )? "selected": ""; ?> >Internal Redirection</option>
                           <option value="external_redirection" <?php echo ( $is_short_link == "external_redirection" )? "selected": ""; ?> >External Redirection</option>
                        </select>
                     </td>
                  </tr>
                  <tr>
                     <th>
                        <label for="src_link">Source URL</label>
                     </th>
                     <td>
                        <span class="lcregular-text-label"><?php echo site_url(); ?>/</span>
                        <input type="text" name="src_link" id="src_link" value="<?php echo esc_attr($src_link); ?>" class="regular-text" placeholder="Enter the rest of the URL excluding the above." required>
                     </td>
                  </tr>
                  <tr>
                     <th>
                        <label for="sort_link" class="type_shortlink <?php echo ( $is_short_link == 'shortlink' )? 'lcvisible': ''; ?>">Short link</label>
                        <label for="internal_link" class="type_internal_redirection type_external_redirection <?php echo ( ($is_short_link == 'internal_redirection') || ($is_short_link == 'external_redirection') )? 'lcvisible': ''; ?>">Redirect To</label>
                     </th>
                     <td>
                        <span class="lcregular-text-label type_internal_redirection <?php echo ( $is_short_link == 'internal_redirection' )? 'lcvisible': ''; ?>"><?php echo site_url(); ?>/</span>
                        <input type="text" name="internal_link" id="internal_link" value="<?php echo esc_attr($internal_link); ?>" class="regular-text type_internal_redirection <?php echo ( $is_short_link == 'internal_redirection' )? 'lcvisible': ''; ?>" placeholder="Enter the rest of the URL excluding the above.">
                        <input type="text" name="sort_link" id="sort_link" value="<?php echo esc_url($sort_link); ?>" class="regular-text type_shortlink type_external_redirection <?php echo ( ($is_short_link == 'shortlink') || ($is_short_link == 'external_redirection') )? 'lcvisible': ''; ?>" placeholder="Enter the full URL here.">
                     </td>
                  </tr>
                  <tr>
                     <th>
                        <label for="query_param">Query Parameters</label>
                     </th>
                     <td>
                        <select id="query_param" name="query_param" class="regular-text">
                           <option value="exact_match" <?php echo ( $query_param == "exact_match" )? "selected": ""; ?>>Exact match in any order</option>
                           <option value="ignore_param" <?php echo ( $query_param == "ignore_param" )? "selected": ""; ?>>Ignore all parameters</option>
                           <option value="pass_param" <?php echo ( $query_param == "pass_param" )? "selected": ""; ?>>Pass all parameters</option>
                        </select>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
         <script>
            function lclink_usage( is_short_link ) {
               for ( let lcel of document.querySelectorAll( '.lcvisible' ) ) {
                  lcel.classList.remove( 'lcvisible' );
               }
               for ( let lcel of document.querySelectorAll( '.type_'+is_short_link ) ) {
                  lcel.classList.add( 'lcvisible' );
               }
            }
         </script>
      <?php
   }

   public function lc_shortlink_meta_box_save( $post_id ) {

      if( isset( $_POST['is_short_link'] ) ) {
         $shortlink = sanitize_text_field( $_POST['is_short_link'] );
         update_post_meta( $post_id, 'is_short_link', $shortlink );
         
         if( isset( $_POST['src_link'] ) )  {
            $src_link = site_url()."/".trim( sanitize_text_field( $_POST['src_link'] ), '/\\' );
            update_post_meta( $post_id, 'src_link', $src_link );
         }

         if( isset( $_POST['sort_link'] ) && ( $shortlink == 'shortlink' || $shortlink == 'external_redirection' ) )  {
            $sort_link = rtrim( sanitize_url( sanitize_text_field( $_POST['sort_link'] ) ), '/\\' );
            update_post_meta( $post_id, 'sort_link', $sort_link );
         }

         if( isset( $_POST['internal_link'] ) && ( $shortlink == 'internal_redirection' ) )  {
            $internal_link = site_url()."/".trim( sanitize_text_field( $_POST['internal_link'] ), '/\\' );
            update_post_meta( $post_id, 'sort_link', $internal_link );
         }

         if( isset( $_POST['query_param'] ) )  {
            $query_param = sanitize_text_field( $_POST['query_param'] );
            update_post_meta( $post_id, 'query_param', $query_param );
         }
      }
   }

   public function lc_shortlink_custom_columns_list( $columns ) {

      $custom_col_order = array(
         'cb' => $columns['cb'],
         'source_old_url' => 'Source URL',
         'shortlink_redto' => 'Shortlink/Redirect To',
         'date' => $columns['date']
      );
      return $custom_col_order;
   }

   public function lc_shortlink_custom_column_values( $column, $post_id ) {

      if( $column == "source_old_url" ) {
         echo esc_url( get_post_meta( $post_id, 'src_link', true ) );
      }
      if( $column == "shortlink_redto" ) {
         echo esc_url( get_post_meta( $post_id, 'sort_link', true ) );
      }
   }

   public function lc_shortlink_remove_row_actions( $actions ) {
      if( get_post_type() === 'lcshortlink' ) {
            unset( $actions['inline hide-if-no-js'] );
      }
      return $actions;
   }

   public function lc_shortlink_search_join ( $join ) {
       global $pagenow, $wpdb;

       if ( is_admin() && 'edit.php' === $pagenow && 'lcshortlink' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {    
           $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
       }
       return $join;
   }

   public function lc_shortlink_search_where( $where ) {
       global $pagenow, $wpdb;

       if ( is_admin() && 'edit.php' === $pagenow && 'lcshortlink' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
           $where = preg_replace(
               "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
               "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
           $where.= " GROUP BY {$wpdb->posts}.id"; // Solves duplicated results
       }
       return $where;
   }

   public function lc_shortlink_redirect_to_short_link() {
       global $wp;

       if ( strlen( $wp->request ) ) {
            global $wpdb;

         $query_string = sanitize_text_field( $_SERVER['QUERY_STRING'] );
         $current_page = home_url( $wp->request );
         $current_page_with_param = add_query_arg( $query_string, '', home_url( $wp->request ) );
         $current_url = home_url( $wp->request );

         $redirect_to = $this->redirect_url_finder( $current_page_with_param, true );
         if( empty( $redirect_to) ) {
            $redirect_to = $this->redirect_url_finder( $current_page );
         }

         if( !empty( $redirect_to ) ) {
            $post_id = 0;
            foreach( $redirect_to as $val ) {
                 $post_id = $val->ID;
            }
            $review_link = get_post_meta( $post_id, 'sort_link', true );
            $query_param = get_post_meta( $post_id, 'query_param', true );
            if ( $review_link != ""  ) {
               if( $query_param == "pass_param" ) {
                  $review_link = add_query_arg( $query_string, '', $review_link );
                  wp_redirect( $review_link, 301 );
               } else {
                  wp_redirect( $review_link, 301 );
               }
               exit;
            }
         }
       } else {
         return;
       }
   }

   public function redirect_url_finder( $curl, $is_strict = false ) {
      $args = array( 'meta_query' => array( array( 'key' => 'src_link', 'value' => $curl ) ), 'post_type' => 'lcshortlink', 'posts_per_page' => 1 );
      if( $is_strict === true ) {
         $args = array( 'meta_query' => array( 'relation' => 'AND', array( 'key' => 'src_link', 'value' => $curl ), array( 'key' => 'query_param', 'value' => 'exact_match' ) ), 'post_type' => 'lcshortlink', 'posts_per_page' => 1 );
      }
      $redirects_to = get_posts($args);

      return $redirects_to;
   }

}

$getStartHere = new lc_shortlink_gen();