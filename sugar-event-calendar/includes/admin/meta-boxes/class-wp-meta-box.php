<?php
/**
 * Meta Box Class
 *
 * @package Plugins/Site/MetaBox
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main meta box class for interfacing with the events and eventmeta database
 * tables.
 *
 * @since 2.0.0
 */
class Meta_Box {

	/**
	 * Sections
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $sections = array();

	/**
	 * ID of the currently selected section
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $current_section = 'duration';

	/**
	 * The event for this meta box
	 *
	 * @since 2.0.0
	 *
	 * @var Event
	 */
	public $event = false;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_default_sections();
	}

	/**
	 * Setup the meta box for the current post
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post
	 */
	public function setup( $post = null ) {
		$this->event = $this->get_post_event_data( $post );
	}

	/**
	 * Setup default sections
	 *
	 * @since 2.0.0
	 */
	public function setup_default_sections() {

		// Duration
		$this->add_section( array(
			'id'       => 'duration',
			'label'    => esc_html__( 'Duration', 'sugar-calendar' ),
			'icon'     => 'clock',
			'callback' => array( $this, 'section_duration' )
		) );

		// Location
		$this->add_section( array(
			'id'       => 'location',
			'label'    => esc_html__( 'Location', 'sugar-calendar' ),
			'icon'     => 'location',
			'callback' => array( $this, 'section_location' )
		) );
	}

	/**
	 * Add a section
	 *
	 * @since 2.0.0
	 *
	 * @param array $section
	 */
	public function add_section( $section = array() ) {
		$this->sections[] = (object) wp_parse_args( $section, array(
			'id'       => '',
			'label'    => '',
			'icon'     => 'admin-settings',
			'callback' => ''
		) );
	}

	/**
	 * Get all sections, and filter them
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_all_sections() {
		return (array) apply_filters( 'sugar_calendar', $this->sections, $this );
	}

	/**
	 * Is a section the current section?
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_id
	 *
	 * @return bool
	 */
	private function is_current_section( $section_id = '' ) {
		return ( $section_id === $this->current_section );
	}

	/**
	 * Output the nonce field for the meta box
	 *
	 * @since 2.0.0
	 */
	private function nonce_field() {
		wp_nonce_field( 'sugar_calendar_nonce', 'sugar_calendar_meta_box_nonce', true );
	}

	/**
	 * Display links to all sections
	 *
	 * @since 2.0.0
	 */
	private function display_all_section_links( $tabs = array() ) {
		echo $this->get_all_section_links( $tabs );
	}

	/**
	 * Get event data for a post
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function get_post_event_data( $post = 0 ) {
		return sugar_calendar_get_event_by_object( $post->ID );
	}

	/**
	 * Display all section contents
	 *
	 * @since 2.0.0
	 */
	private function display_all_section_contents( $tabs = array() ) {
		echo $this->get_all_section_contents( $tabs );
	}

	/**
	 * Get the contents of all links as HTML
	 *
	 * @since 2.0.0
	 *
	 * @param array $sections
	 *
	 * @return string
	 */
	private function get_all_section_links( $sections = array() ) {
		ob_start();

		// Loop through sections
		foreach ( $sections as $section ) :

			// Special selected section
			$selected = $this->is_current_section( $section->id )
				? 'aria-selected="true"'
				: ''; ?>

			<li class="section-title" <?php echo $selected; ?>>
				<a href="#<?php echo esc_attr( $section->id ); ?>">
					<i class="dashicons dashicons-<?php echo esc_attr( $section->icon ); ?>"></i>
					<span class="label"><?php echo esc_attr( $section->label ); ?></span>
				</a>
			</li>

		<?php endforeach;

		// Return output buffer
		return ob_get_clean();
	}

	/**
	 * Get the contents of all sections as HTML
	 *
	 * @since 2.0.0
	 *
	 * @param array $sections
	 *
	 * @return string HTML for all section contents
	 */
	private function get_all_section_contents( $sections = array() ) {
		ob_start();

		// Loop through sections
		foreach ( $sections as $section ) :

			// Special selected section
			$selected = ! $this->is_current_section( $section->id )
				? 'style="display: none;"'
				: ''; ?>

			<div id="<?php echo esc_attr( $section->id ); ?>" class="section-content" <?php echo $selected; ?>><?php

				// Callback or action
				if ( ! empty( $section->callback ) && is_callable( $section->callback ) ) :
					call_user_func( $section->callback, $this->event );
				else :
					do_action( 'sugar_calendar_' . $section->id . 'meta_box_contents', $this );
				endif;

			?></div>

		<?php endforeach;

		// Return output buffer
		return ob_get_clean();
	}

	/**
	 * Output the meta-box contents
	 *
	 * @since 2.0.0
	 */
	public function display() {
		$sections = $this->get_all_sections();
		$event_id = $this->event->id;

		// Start an output buffer
		ob_start(); ?>

		<div class="sugar-calendar-wrap">
			<div class="sc-vertical-sections">
				<ul class="section-nav">
					<?php $this->display_all_section_links( $sections ); ?>
				</ul>

				<div class="section-wrap">
					<?php $this->display_all_section_contents( $sections ); ?>
				</div>
				<br class="clear">
			</div>
			<?php $this->nonce_field(); ?>
			<input type="hidden" name="wp-event-id" value="<?php echo esc_attr( $event_id ); ?>" />
		</div>

		<?php

		// Output buffer
		echo ob_get_clean();
	}

	/**
	 * Output the event duration meta-box
	 *
	 * @since  0.2.3
	 */
	public function section_duration( $event = null ) {
		$date = $hour = $minute = $am_pm = '';
		$end_date = $end_hour = $end_minute = $end_am_pm = '';

		/** All Day ***********************************************************/

		$all_day = ! empty( $event->all_day )
			? (bool) $event->all_day
			: false;

		$hidden = ( true === $all_day )
			? ' style="display: none;"'
			: '';

		/** Ends **************************************************************/

		// Get date_time
		$end_date_time = ! empty( $event->end )
			? strtotime( $event->end )
			: null;

		// Only if end isn't empty
		if ( ! empty( $end_date_time ) ) {

			// Date
			$end_date = date( 'Y-m-d', $end_date_time );

			// Only if not all-day
			if ( empty( $all_day ) ) {

				// Hour
				$end_hour = date( 'h', $end_date_time );
				if ( empty( $end_hour ) ) {
					$end_hour = '';
				}

				// Minute
				$end_minute = date( 'i', $end_date_time );
				if ( empty( $end_hour ) ) {
					$end_minute = '';
				}

				// Day/night
				$end_am_pm = date( 'a', $end_date_time );
			}
		}

		/** Starts ************************************************************/

		// Get date_time
		if ( ! empty( $_GET['start_day'] ) ) {
			$date_time = (int) $_GET['start_day'];
		} else {
			$date_time = ! empty( $event->start )
				? strtotime( $event->start )
				: null;
		}

		// Date
		if ( ! empty( $date_time ) ) {
			$date = date( 'Y-m-d', $date_time );

			// Only if not all-day
			if ( empty( $all_day ) ) {

				// Hour
				$hour = date( 'h', $date_time );
				if ( empty( $end_hour ) || empty( $hour ) ) {
					$hour = '';
				}

				// Minute
				$minute = date( 'i', $date_time );
				if ( empty( $hour ) && empty( $end_minute ) ) {
					$minute = '';
				}

				// Day/night
				$am_pm = date( 'a', $date_time );
			}
		}

		/** Let's Go! *********************************************************/

		// Start an output buffer
		ob_start(); ?>

		<table class="form-table rowfat">
			<tbody>
				<tr>
					<th>
						<label for="sugar_calendar_all_day" class="screen-reader-text"><?php esc_html_e( 'All Day', 'sugar-calendar' ); ?></label>
					</th>

					<td>
						<label>
							<input type="checkbox" name="sugar_calendar_all_day" id="sugar_calendar_all_day" value="1" <?php checked( $all_day ); ?> />
							<?php esc_html_e( 'All-day event', 'sugar-calendar' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th>
						<label for="sugar_calendar_date"><?php esc_html_e( 'Start', 'sugar-calendar'); ?></label>
					</th>

					<td>
						<input type="text" class="sugar_calendar_datepicker" name="sugar_calendar_date" id="sugar_calendar_date" value="<?php echo esc_attr( $date ); ?>" placeholder="yyyy-mm-dd" />
						<div class="event-time" <?php echo $hidden; ?>>
							<span class="sugar_calendar_time_separator"><?php esc_html_e( ' at ', 'wp-event-alendar' ); ?></span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '',
								'id'       => 'sugar_calendar_time_hour',
								'name'     => 'sugar_calendar_time_hour',
								'items'    => sugar_calendar_get_hours(),
								'selected' => $hour
							) ); ?>
							<span class="sugar_calendar_time_separator">:</span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '',
								'id'       => 'sugar_calendar_time_minute',
								'name'     => 'sugar_calendar_time_minute',
								'items'    => sugar_calendar_get_minutes(),
								'selected' => $minute
							) ); ?>
							<select name="sugar_calendar_time_am_pm">
								<option value="am" <?php selected( $am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select>
						</div>
					</td>

				</tr>

				<tr>
					<th>
						<label for="sugar_calendar_end_date"><?php esc_html_e( 'End', 'sugar-calendar'); ?></label>
					</th>

					<td>
						<input type="text" class="sugar_calendar_datepicker" name="sugar_calendar_end_date" id="sugar_calendar_end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="yyyy-mm-dd" />
						<div class="event-time" <?php echo $hidden; ?>>
							<span class="sugar_calendar_time_separator"><?php esc_html_e( ' at ', 'wp-event-alendar' ); ?></span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '',
								'id'       => 'sugar_calendar_end_time_hour',
								'name'     => 'sugar_calendar_end_time_hour',
								'items'    => sugar_calendar_get_hours(),
								'selected' => $end_hour
							) ); ?>
							<span class="sugar_calendar_time_separator">:</span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '',
								'id'       => 'sugar_calendar_end_time_minute',
								'name'     => 'sugar_calendar_end_time_minute',
								'items'    => sugar_calendar_get_minutes(),
								'selected' => $end_minute
							) ); ?>
							<select class="sugar_calendar_end_time_am_pm" name="sugar_calendar_end_time_am_pm">
								<option value="am" <?php selected( $end_am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $end_am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php

		echo ob_get_clean();
	}

	/**
	 * Output the event recurrence meta-box
	 *
	 * @since  0.2.3
	 *
	 * @param Event $event
	*/
	public function section_recurrence( $event = null ) {

		// Interval
		$interval = ! empty( $event->recurrence )
			? $event->recurrence
			: '';

		// Expiration
		$expire = ! empty( $event->recurrence_end ) && ( '0000-00-00 00:00:00' !== $event->recurrence_end )
			? date( 'Y-m-d', strtotime( $event->recurrence_end ) )
			: '';

		// Filter the intervals
		$options = sugar_calendar_get_recurrence_types();

		// Start an output buffer
		ob_start(); ?>

		<table class="form-table rowfat">
			<tbody>
				<tr>
					<th>
						<label for="sugar_calendar_repeat"><?php esc_html_e( 'Repeat', 'sugar-calendar' ); ?></label>
					</th>

					<td>
						<select name="sugar_calendar_repeat" class="sugar_calendar_repeat" id="sugar_calendar_repeat">
							<option value="0" <?php selected( 0, $interval ); ?>><?php echo esc_html__( 'Never', 'sugar-calendar' ); ?></option>

							<?php foreach ( $options as $key => $option ) : ?>

								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $interval ); ?>><?php echo esc_html( $option ); ?></option>

							<?php endforeach; ?>

						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table rowfat repeat-until">
			<tbody>
				<tr>
					<th>
						<label for="sugar_calendar_repeat"><?php esc_html_e( 'End Repeat', 'sugar-calendar' ); ?></label>
					</th>

					<td>
						<input type="text" class="sugar_calendar_datepicker" name="sugar_calendar_expire" id="sugar_calendar_expire" value="<?php echo esc_attr( $expire ); ?>" placeholder="Never" <?php disabled( empty( $interval ) ); ?> />
					</td>
				</tr>
			</tbody>
		</table>

		<?php

		echo ob_get_clean();
	}

	/**
	 * Output the event location meta-box
	 *
	 * @since  0.2.3
	 *
	 * @param Event $event The event
	*/
	public function section_location( $event = null ) {

		// Location
		$location = $event->location;

		// Start an output buffer
		ob_start(); ?>

		<table class="form-table rowfat">
			<tbody>

				<?php if ( apply_filters( 'sugar_calendar_location', true ) ) : ?>

					<tr>
						<th>
							<label for="sugar_calendar_location"><?php esc_html_e( 'Location', 'sugar-calendar' ); ?></label>
						</th>

						<td>
							<label>
								<textarea name="sugar_calendar_location" id="sugar_calendar_location" placeholder="<?php esc_html_e( '(Optional)', 'sugar-calendar' ); ?>"><?php echo esc_textarea( $location ); ?></textarea>
							</label>
						</td>
					</tr>

				<?php endif; ?>
			</tbody>
		</table>

		<?php

		// End & flush the output buffer
		echo ob_get_clean();
	}
}
