<?php
/**
 * Evento individual: leva para a agenda completa.
 */
wp_safe_redirect( ( get_post_type_archive_link( 'evento' ) ?: home_url( '/agenda/' ) ) . '#' . get_post_field( 'post_name', get_queried_object_id() ), 301 );
exit;
