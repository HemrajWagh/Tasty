<?php
namespace Bookly\Lib;

use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Payment;

/**
 * Class Routines
 * @package Bookly\Lib
 */
abstract class Routines
{
    /**
     * Init routines.
     */
    public static function init()
    {
        // Register daily routine.
        add_action( 'bookly_daily_routine', function () {
            // Daily info routine.
            Routines::handleDailyInfo();
            // Cloud routine.
            Routines::loadCloudInfo();
            // Statistics routine.
            Routines::sendDailyStatistics();
            // Calculate goal by number of customer appointments achieved
            Routines::calculateGoalOfCA();
            // Let add-ons do their daily routines.
            Proxy\Shared::doDailyRoutine();
        }, 10, 0 );

        // Register hourly routine.
        add_action( 'bookly_hourly_routine', function () {
            // Email and SMS notifications routine.
            Notifications\Routine::sendNotifications();
            // Handle outdated unpaid payments
            Routines::handleUnpaidPayments();
        }, 10, 0 );

        // Schedule daily routine.
        if ( ! wp_next_scheduled( 'bookly_daily_routine' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'daily', 'bookly_daily_routine' );
        }

        // Schedule hourly routine.
        if ( ! wp_next_scheduled( 'bookly_hourly_routine' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'bookly_hourly_routine' );
        }
    }

    /**
     * Handle outdated payments
     */
    public static function handleUnpaidPayments()
    {
        $payments = array();
        $timeout = (int) get_option( 'bookly_cloud_stripe_timeout' );
        if ( $timeout ) {
            // Get list of outdated unpaid Cloud Stripe payments
            $payments = Payment::query()
                ->where( 'type', Payment::TYPE_CLOUD_STRIPE )
                ->where( 'status', Payment::STATUS_PENDING )
                ->whereLt( 'created_at', date_create( current_time( 'mysql' ) )->modify( sprintf( '- %s seconds', $timeout ) )->format( 'Y-m-d H:i:s' ) )
                ->fetchCol( 'id' );
        }

        // Mark unpaid appointments as rejected.
        $payments = Proxy\Shared::prepareOutdatedUnpaidPayments( $payments );
        if ( ! empty( $payments ) ) {
            Payment::query()
                ->update()
                ->set( 'status', Payment::STATUS_REJECTED )
                ->whereIn( 'id', $payments )
                ->execute();
            CustomerAppointment::query()
                ->update()
                ->set( 'status', CustomerAppointment::STATUS_REJECTED )
                ->set( 'status_changed_at', current_time( 'mysql' ) )
                ->whereIn( 'payment_id', $payments )
                ->execute();
            // Reject recurring appointments when customer pay only for first one.
            $series = CustomerAppointment::query()
                ->whereIn( 'payment_id', $payments )
                ->whereNot( 'series_id', null )
                ->fetchCol( 'series_id' );
            if ( ! empty( $series ) ) {
                CustomerAppointment::query()
                    ->update()
                    ->set( 'status', CustomerAppointment::STATUS_REJECTED )
                    ->set( 'status_changed_at', current_time( 'mysql' ) )
                    ->whereIn( 'series_id', $series )
                    ->execute();
            }
        }
    }

    /**
     * Daily info routine.
     */
    public static function handleDailyInfo()
    {
        $data = API::getInfo();

        if ( is_array( $data ) ) {
            if ( isset ( $data['plugins'] ) ) {
                $seen = Entities\Shop::query()->count() ? 0 : 1;
                foreach ( $data['plugins'] as $plugin ) {
                    $shop = new Entities\Shop();
                    if ( $plugin['id'] && $plugin['envatoPrice'] ) {
                        $shop->loadBy( array( 'plugin_id' => $plugin['id'] ) );
                        $shop
                            ->setPluginId( $plugin['id'] )
                            ->setType( $plugin['type'] ? 'bundle' : 'plugin' )
                            ->setHighlighted( $plugin['highlighted'] ?: 0 )
                            ->setPriority( $plugin['priority'] ?: 0 )
                            ->setDemoUrl( $plugin['demoUrl'] )
                            ->setTitle( $plugin['title'] )
                            ->setSlug( $plugin['slug'] )
                            ->setDescription( $plugin['envatoDescription'] )
                            ->setUrl( $plugin['envatoUrl'] )
                            ->setIcon( $plugin['envatoIcon'] )
                            ->setPrice( $plugin['envatoPrice'] )
                            ->setSales( $plugin['envatoSales'] )
                            ->setRating( $plugin['envatoRating'] )
                            ->setReviews( $plugin['envatoReviews'] )
                            ->setPublished( isset ( $plugin['envatoPublishedAt']['date'] )
                                ? date_create( $plugin['envatoPublishedAt']['date'] )->format( 'Y-m-d H:i:s' )
                                : current_time( 'mysql' )
                            )
                            ->setCreatedAt( current_time( 'mysql' ) )
                            ->setSeen( $shop->isLoaded() ? $shop->getSeen() : $seen )
                            ->save();
                    }
                }
            }

            if ( isset( $data['messages'] ) ) {
                foreach ( $data['messages'] as $data ) {
                    $message = new Entities\Message();
                    $message->loadBy( array( 'message_id' => $data['message_id'] ) );
                    if ( ! $message->isLoaded() ) {
                        $message
                            ->setFields( $data )
                            ->setCreatedAt( current_time( 'mysql' ) )
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * Load Bookly Cloud products, promotions, etc.
     */
    public static function loadCloudInfo()
    {
        Cloud\API::getInstance()->general->loadInfo();
    }

    /**
     * Statistics routine.
     */
    public static function sendDailyStatistics()
    {
        if ( get_option( 'bookly_gen_collect_stats' ) ) {
            API::sendStats();
        }
    }

    public static function calculateGoalOfCA()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $ca_count = get_option( 'bookly_сa_count' );
        $log10 = (int) log10( Entities\CustomerAppointment::query()->count() );
        $current = $log10 > 0 ? pow( 10, $log10 ) : 0;

        if ( $ca_count != $current ) {
            // New goal by number of customer appointments achieved,
            // corresponding hide until values are reset to show call to rate Bookly on WP
            $wpdb->query( $wpdb->prepare(
                'UPDATE `' . $wpdb->usermeta . '` SET `meta_value` = %d WHERE `meta_key` = \'bookly_notice_rate_on_wp_hide_until\' AND meta_value != 0',
                time()
            ) );

            update_option( 'bookly_сa_count', $current );
        }
    }
}