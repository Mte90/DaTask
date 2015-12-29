<?php
/**
 * The Template for displaying the user profile page
 *
 * This template can be overridden by copying it to yourtheme/datask/user-profile.php.
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = DaTask::get_instance();
$user_id = get_user_by( 'login', get_user_of_profile() );
$user_id = $user_id->data->ID;
$tasks_user = get_tasks_by_user( $user_id );
if ( !empty( $tasks_user ) ) {
	$tasks_user = array_reverse( $tasks_user, true );
}
$task_implode = array_keys( $tasks_user );
$posts = query_posts( array(
    'post_type' => 'task',
    'post__in' => $task_implode,
    'orderby' => 'post__in',
    'posts_per_page' => -1 ) );
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
     <?php do_action( 'rss2_ns' ); ?>>
    <channel>
        <title><?php bloginfo_rss( 'name' ); ?> - Feed</title>
        <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
        <link><?php bloginfo_rss( 'url' ) ?></link>
        <description><?php bloginfo_rss( 'description' ) ?></description>
        <lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
        <language><?php echo get_option( 'rss_language' ); ?></language>
        <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
        <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action( 'rss2_head' ); ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<item>
		    <title><?php the_title_rss(); ?></title>
		    <link><?php the_permalink_rss(); ?></link>
		    <?php
		    $date = '';
		    if ( strlen( $tasks_user[ get_the_ID() ] ) > 2 ) {
			    ?>
			    <pubDate><?php echo date( 'Y-m-d H:i:s', $tasks_user[ get_the_ID() ] )/* get_post_time( 'Y-m-d H:i:s', true ) */; ?></pubDate>
			    <?php
		    }
		    ?>
		    <dc:creator><?php the_author(); ?></dc:creator>
		    <guid isPermaLink="false"><?php the_guid(); ?></guid>
		    <description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
		    <content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
		    <?php rss_enclosure(); ?>
		    <?php do_action( 'rss2_item' ); ?>
		</item>
	<?php endwhile; ?>
    </channel>
</rss>