<?php
/**
 * The template for displaying the About page.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

<div class="row-fluid aboutBG our-goal"></div>
<div class="container">
 <div class="row-fluid" id="aboutUs">
  <div class="header span12">
    <h4><span class="show">Licensing.&nbsp;&nbsp;Production &amp; Development.&nbsp;&nbsp;Distribution.&nbsp;&nbsp;Marketing &amp; Publicity.&nbsp;&nbsp;Digital & Social Media.&nbsp;&nbsp;Creative Services.</span><span class="hide">Saban Brands</span></h4>
  </div>
  <div class="row-fluid">
      <div class="span2">
        <ul class="nav nav-pills nav-stacked ">
            <li class="active"><a href="#">Saban Brands</a></li>
            <li><a href="#">Power Rangers emPOWER</a></li>
            <li><a href="#">Paul Frank Arts</a></li>
        </ul>
     </div>
     <script>
      jQuery( document ).ready(function( $ ) {

        $( 'div.about-section' ).not( ':first' ).hide();

        var show_about_tabs = function( e ) {
           var $this, $parent, $index, $_index, $_hash;

           if ( e && $( this ).parents( 'ul' ).hasClass( 'nav-stacked' ) ) {

             e.preventDefault();
             $this = $( this );

           } else if ( window.location.hash ) {

              if ( e ) {
                 $_hash = $( this ).prop( 'hash' );
              } else {
                 $_hash = window.location.hash;
              }

              $_index = $( '.about-section.' + $_hash.substring(1) ).index();
              $this   = $( '.nav-stacked li' ).eq( $_index ).find( 'a' );
           }

          $parent = $this.parent(), $index = $parent.index();

          $( '.nav-stacked li' ).removeClass( 'active' );

          $parent.addClass( 'active' );

          $( '.about-section' ).hide();
          $( '.about-section' ).eq( $index ).show();

          $( '.aboutBG' ).removeClass().addClass( 'row-fluid aboutBG ' +  $( '.about-section' ).eq( $index ).attr( 'class' ).split(' ')[1] );

        }

         if ( $( 'body' ).hasClass( 'page-id-2' ) ) {
          $( 'li.first-menu-item ul.dropdown-menu a' ).click( show_about_tabs );
        }

        if ( window.location.hash ) {
          show_about_tabs();
        }

        $( '.nav-stacked li a' ).click( show_about_tabs );

      });
     </script>
    <?php while ( have_posts() ) : the_post(); ?>
      <div class="span10" id="description">
        <?php
          the_content();
        ?>
     </div>
      <?php endwhile; // end of the loop. ?>
    <div class="clearfix"></div>
</div>
 </div>
 <ul class="thumbnails">
        <?php
          $logo_src = isset( $brand_meta['brand-logo_url'] ) ? $brand_meta['brand-logo_url'] : '';

          $latest_posts = get_posts( array( 'numberposts' => 3, 'brands' => 'saban-brands', 'meta_query' => array( array( 'key' => '_thumbnail_id', 'value' => '', 'compare' => '!=' ) ) ) );
          foreach ( $latest_posts as $latest_post ) :
        ?>
          <li class="span4">
            <div class="thumbnail">
              <?php
                if ( has_post_thumbnail( $latest_post->ID ) )
                  echo get_the_post_thumbnail( $latest_post->ID, 'home-page-news' );

                  $link = get_permalink( $latest_post->ID );

                if ( in_category( 'press', $latest_post->ID ) ) {
                    $pdfs = get_posts( array( 'post_parent' => $latest_post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'application/pdf', 'numberposts' => 1 ) );
                    $pdf  = wp_get_attachment_url( $pdfs[0]->ID );

                    $link = $pdf ? $pdf : $link;
                }

              ?>
              <div class="copy">
                <h3><?php echo get_the_title( $latest_post->ID ); ?></h3>
                <p><?php echo wp_trim_words( $latest_post->post_content, 16 ); ?></p>
                <a class="btn" href="<?php echo esc_url( $link ); ?>">read Article</a></div>
            </div>
          </li>
        <?php endforeach; ?>

    </ul><p></p>

    <!--We Love it Here Row-->

  <div class="row-fluid" id="weLoveItHere">
    <div class="header span12">
      <h4>We Love it Here</h4>
    </div>

	<?php
		if ( is_active_sidebar( 'footer-content' ) ) :
			dynamic_sidebar( 'footer-content' );
		endif;
	 ?>
  </div>

     <!--End We Love it Here Row-->
</div>

<?php get_footer(); ?>