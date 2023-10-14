<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.78'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );


// (0ld) Query mod for Events Archive
function events_filter_query( $query ) {
	$query->set( 'meta_key', 'start_date' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'ASC' );

	$meta_query = $query->get( 'meta_query' );
	if ( ! $meta_query ) { $meta_query = []; }
	$meta_query[] = [
		'key' => 'end_date',
		'value' => date( 'Ymd' ),
		'compare' => '>=',
	];
	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/events_filter', 'events_filter_query' );


// (Not in use) Query mod for Events Preview
function upcoming_events_filter_query( $query ) {
	$query->set( 'posts_per_page', 2 );
	$query->set( 'meta_key', '_EventStartDate' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'ASC' );

	$meta_query = $query->get( 'meta_query' );
	if ( ! $meta_query ) { $meta_query = []; }
	$meta_query[] = [
		'key' => '_EventEndDate',
		'value' => date( 'Y-m-d' ),
		'compare' => '>=',
	];
	$query->set( 'meta_query', $meta_query );
}
add_action( 'elementor/query/upcoming_events_filter', 'upcoming_events_filter_query' );


// Shortcode that displays upcoming events from The Events Calendar
function list_upcoming_events_shortcode($atts = []) {
	extract(shortcode_atts(array(
		'count' => '2',
	), $atts));
	$args = array(
		'post_type' => 'tribe_events',
		'post_status' => 'publish', 
		'meta_key' => '_EventStartDate',
		'meta_query' => array(
			array(
				'key' => '_EventEndDate',
				'value' => date('Y-m-d'),   
				'compare' => '>=', 
				'type' => 'DATE'
			)
		),
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'showposts' => $count
	);
	query_posts($args);
	global $post;
	$output = '<div class="upcoming_events">';
		while ( have_posts() ) { 
			the_post();
			$event_start_date = get_post_meta($post->ID,'_EventStartDate',true);
			$event_start_date = date("Y-m-d", strtotime($event_start_date));
			$event_start_month = date("F", strtotime($event_start_date));
			$event_start_day = date("j", strtotime($event_start_date));
			$event_start_year = date("Y", strtotime($event_start_date));
			$event_end_date = get_post_meta($post->ID,'_EventEndDate',true); 
			$event_end_date = date("Y-m-d", strtotime($event_end_date)); 
			$event_end_month = date("F", strtotime($event_end_date));
			$event_end_day = date("j", strtotime($event_end_date));
			$event_end_year = date("Y", strtotime($event_end_date));
			if ($event_start_date == $event_end_date){
				$event_date_display = date("F j, Y", strtotime($event_start_date));
			} else {
				if ($event_start_month == $event_end_month) {
					$event_date_display = $event_start_month . ' ' . $event_start_day . '-' . $event_end_day . ', ' . $event_start_year;
				} else if ($event_start_year != $event_end_year) {
					$event_date_display = $event_start_month . ' ' . $event_start_day . ', ' . $event_start_year . ' - ' . $event_end_month . ' ' . $event_end_day . ', ' . $event_end_year;
				} else {
					$event_date_display = $event_start_month . ' ' . $event_start_day . ' - ' . $event_end_month . ' ' . $event_end_day . ', ' . $event_end_year;
				}
			}
			$output .= '<div class="upcoming_events__event">';
				$output .= '<div class="upcoming_events__event--date">';
					$output .= '<p>' . $event_date_display . '</p>';
				$output .= '</div>';
				$output .= '<div class="upcoming_events__event--title">';
					$output .= '<h4><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h4>';
				$output .= '</div>';
				$event_venue_id = get_post_meta($post->ID,'_EventVenueID',true);
				$event_venue_name = get_the_title($event_venue_id);
				$event_venue_city = get_post_meta($event_venue_id,'_VenueCity',true);
				$event_venue_state = get_post_meta($event_venue_id,'_VenueState',true);
				$output .= '<div class="upcoming_events__event--location">';
					if ($event_venue_name) {
						$output .= '<p>' . $event_venue_name . '</p>';
					}
					if ($event_venue_city) {
						$output .= '<p>';
						if ($event_venue_state) {
							$output .= $event_venue_city . ', ' . $event_venue_state;
						} else {
							$output .= $event_venue_city;
						}
						$output .= '</p>';
					}
				$output .= '</div>';
			$output .= '</div>';
		}
	$output .= '</div>';
	wp_reset_postdata();
	return $output;
}
add_shortcode('list_upcoming_events', 'list_upcoming_events_shortcode');


// Shortcode that displays a taxonomy list w/links
function list_categories_shortcode($atts = []) {
    extract(shortcode_atts(array(
		'type' => 'category',
	 ), $atts));
	$terms = get_terms( array(
		'taxonomy' => $type,
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => true,
	) );
	$queried_term = get_queried_object();
	$list = '<ul class="category-list">';
	foreach ($terms as $term) {
		$class = "";
		if ( $queried_term->slug === $term->slug) {
			$class = "current";
		}
		$list .= '<li class="' . $class . '"><a href="' . esc_url( get_term_link( $term ) ) . '">' . $term->name . '</a></li>';
	}
	$list .= '</ul>';
	return $list;
}
add_shortcode('list_categories', 'list_categories_shortcode');


//Query mod to order Sites & Attractions Archive by alpha
function sa_order_filter( $query ) {
    if ( is_post_type_archive( 'sites-attractions' ) 
	|| is_tax( 'site_attraction_category' ) ) {
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
    }
}
add_action( 'pre_get_posts', 'sa_order_filter' );




//Divider Shortcode
function divider_shortcode() {
	return '<span class="divider"></span>';
}
add_shortcode('divider', 'divider_shortcode');


//Dropcap Shortcode
function dropcap_shortcode($atts, $content = "") {
	return '<span class="dropcap">' . $content . '</span>';;
}
add_shortcode('dropcap', 'dropcap_shortcode');

//List categories for POIs on Sites & Attractions page
function filterCategories($props, $postid, $poi) {
	$post = get_post( $post = $postid, $output = object, $filter = 'raw' );
	$postCategories = get_the_terms( $post, 'site_attraction_category' );
	$postCategory = wp_list_pluck( $postCategories, 'name' );
	$arrayToString = implode(", ",$postCategory);
    $props['message'] = $arrayToString;
return $props;
}
add_filter('mappress_poi_props', 'filterCategories', 10, 3);

/////////////////////////////////////////
//           FORMS BACKENDS            //
//             2023-06-18              //
//             Anh Nguyen              //
/////////////////////////////////////////

// add jQuery
function enqueue_jquery() {
	wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');

// Endpoint registration
add_action('rest_api_init', 'register_custom_endpoint');
function register_custom_endpoint() {
	// endpoint for timesheet data
	register_rest_route('timesheet/v1', '/data', array(
		'methods' => 'GET',
		'callback' => 'get_all_timesheet_data'
	));
	register_rest_route('timesheet_item/v1', '/update/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_timesheet_status'
    ));
	// endpoint for grant data
	register_rest_route('grantform/v1', '/data', array(
		'method' => 'GET',
		'callback' => 'get_all_grant_data'
	));
	register_rest_route('grant_item/v1', '/update/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_grant_status'
    ));	
	// endpoint for sponsorship data
	register_rest_route('sponsorship/v1', '/data', array(
		'method' => 'GET',
		'callback' => 'get_all_sponsorship_data'
	));
	register_rest_route('sponsorship_item/v1', '/update/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_sponsorship_status'
    ));	
	// endpoint for assistance data
	register_rest_route('assistance/v1', '/data', array(
		'method' => 'GET',
		'callback' => 'get_all_assistance_data'
	));
	register_rest_route('assistance_item/v1', '/update/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_assistance_status'
    ));	
	// endpoint for grant report data
	register_rest_route('grant_report/v1', '/grant_data', array(
		'method' => 'GET',
		'callback' => 'get_all_grant_report_data'
	));
	register_rest_route('grant_report_item/v1', '/update/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_grant_report_status'
    ));
};

// get timesheet data
function get_all_timesheet_data($request) {
	global $wpdb;
	$timesheet_table = $wpdb->prefix . 'timesheet';
	$sql = $wpdb->prepare("SELECT * FROM $timesheet_table ORDER BY date ASC;");
	$data = $wpdb->get_results($sql, ARRAY_A);
	$response = array(
		'status' => 'OK',
		'message' => 'Data retrieved successfully',
		'data' => $data
	);
	return rest_ensure_response($response);
};

// handle update status "Is Delete" for row in timesheet table
function update_timesheet_status(WP_REST_Request $request) {
	$id = $request->get_param('id');
	$status = $request->get_param('status');
	
	global $wpdb;
	$timesheet_table = $wpdb->prefix . 'timesheet';
	$data = $wpdb->get_results("UPDATE $timesheet_table SET `status` = 'Is Deleted' WHERE `id` = $id;", ARRAY_A);
	// show updated data after updating on response message 
	$updated_data = $wpdb->get_results("SELECT `id`, `employee_name`, `status` FROM $timesheet_table WHERE `id` = $id;", ARRAY_A);
	$response = array(
		'message' => 'Update successfully',
		'updated_data' => $updated_data
	);
	return rest_ensure_response($response);
};

// get grant form data
function get_all_grant_data($request) {
	global $wpdb;
	$grant_table = $wpdb->prefix . 'grant';
	$vendor_quotes_table = $wpdb->prefix . 'vendor_quotes';

	$sql = $wpdb->prepare("SELECT g.*, vq.* FROM $grant_table AS g LEFT JOIN $vendor_quotes_table AS vq ON g.grant_id = vq.grant_id ORDER BY g.start_date;");
	$data = $wpdb->get_results($sql, ARRAY_A);
	$response = array(
		'status' => 'OK',
		'message' => 'Data retrieved successfully',
		'data' => $data
	);
	if (empty($data)) {
		return new WP_REST_Response(array('message' => 'No data found.'), 404);
	} else {
		return rest_ensure_response($response);
	}
};

// handle update status "Is Delete" for row in grant table
function update_grant_status(WP_REST_Request $request) {
	$id = $request->get_param('id');
	$status = $request->get_param('status');
	
	global $wpdb;
	$grant_table = $wpdb->prefix . 'grant';
	$data = $wpdb->get_results("UPDATE $grant_table SET `status` = 'Is Deleted' WHERE `grant_id` = $id;", ARRAY_A);
	// show updated data after updating on response message 
	$updated_data = $wpdb->get_results("SELECT `grant_id`, `organization_name`, `status` FROM $grant_table WHERE `id` = $id;", ARRAY_A);
	$response = array(
		'message' => 'Update successfully',
		'updated_data' => $updated_data
	);
	return rest_ensure_response($response);
};

function get_all_sponsorship_data($request) {
	global $wpdb;
	$sponsorship_table = $wpdb->prefix . 'sponsorship';
	$supporting_docs_table = $wpdb->prefix . 'supporting_docs';

	$sql = $wpdb->prepare("SELECT s.*, sd.* FROM $sponsorship_table AS s LEFT JOIN $supporting_docs_table AS sd ON s.sponsorship_id = sd.sponsorship_id;");
	$data = $wpdb->get_results($sql, ARRAY_A);
	$response = array(
		'status' => 'OK',
		'message' => 'Data retrieved successfully',
		'data' => $data
	);
	if (empty($data)) {
		return new WP_REST_Response(array('message' => 'No data found.'), 404);
	} else {
		return rest_ensure_response($response);
	}
}
// handle update status "Is Delete" for row in grant table
function update_sponsorship_status(WP_REST_Request $request) {
	$id = $request->get_param('id');
	$status = $request->get_param('status');
	
	global $wpdb;
	$sponsorship_table = $wpdb->prefix . 'sponsorship';
	$data = $wpdb->get_results("UPDATE $sponsorship_table SET `status` = 'Is Deleted' WHERE `sponsorship_id` = $id;", ARRAY_A);
	// show updated data after updating on response message 
	$updated_data = $wpdb->get_results("SELECT `sponsorship_id`, `org_name`, `status` FROM $sponsorship_table WHERE `sponsorship_id` = $id;", ARRAY_A);
	$response = array(
		'message' => 'Update successfully',
		'updated_data' => $updated_data
	);
	return rest_ensure_response($response);
};

function get_all_assistance_data($request) {
	global $wpdb;
	$assistance_table = $wpdb->prefix . 'pj_assistance';
	$sql = $wpdb->prepare("SELECT * FROM $assistance_table;");
	$data = $wpdb->get_results($sql, ARRAY_A);
	$response = array(
		'status' => 'OK',
		'message' => 'Data retrieved successfully',
		'data' => $data
	);
	return rest_ensure_response($response);
};
// handle update status "Is Delete" for row in assistance table
function update_assistance_status(WP_REST_Request $request) {
	$id = $request->get_param('id');
	$status = $request->get_param('status');
	
	global $wpdb;
	$assistance_table = $wpdb->prefix . 'pj_assistance';
	$data = $wpdb->get_results("UPDATE $assistance_table SET `status` = 'Is Deleted' WHERE `pj_id` = $id;", ARRAY_A);
	// show updated data after updating on response message 
	$updated_data = $wpdb->get_results("SELECT `pj_id`, `org_name`, `status` FROM $assistance_table WHERE `pj_id` = $id;", ARRAY_A);
	$response = array(
		'message' => 'Update successfully',
		'updated_data' => $updated_data
	);
	return rest_ensure_response($response);
};

function get_all_grant_report_data($request) {
	global $wpdb;
	$grant_report_table = $wpdb->prefix . 'grant_report';	
	$pictures_table = $wpdb->prefix . "pictures";
	$docs_table = $wpdb->prefix . "docs";
	$backup_docs_table = $wpdb->prefix . "backup_docs";
	$sql = $wpdb->prepare("SELECT DISTINCT gr.*, p.path AS picture_path, d.path AS doc_path, bd.path AS backup_doc_path 
			FROM $grant_report_table AS gr 
			LEFT JOIN $pictures_table AS p ON gr.report_id = p.report_id 
			LEFT JOIN $docs_table AS d ON gr.report_id = d.report_id 
			LEFT JOIN $backup_docs_table AS bd ON gr.report_id = bd.report_id
			;");
	$data = $wpdb->get_results($sql, ARRAY_A);
	$response = array(
		'status' => 'OK',
		'message' => 'Data retrieved successfully',
		'data' => $data
	);
	return rest_ensure_response($response);
};
// handle update status "Is Delete" for row in grant report table
function update_grant_report_status(WP_REST_Request $request) {
	$id = $request->get_param('id');
	$status = $request->get_param('status');
	
	global $wpdb;
	$grant_report_table = $wpdb->prefix . 'grant_report';
	$data = $wpdb->get_results("UPDATE $grant_report_table SET `status` = 'Is Deleted' WHERE `report_id` = $id;", ARRAY_A);
	// show updated data after updating on response message 
	$updated_data = $wpdb->get_results("SELECT `report_id`, `org_name`, `status` FROM $grant_report_table WHERE `report_id` = $id;", ARRAY_A);
	$response = array(
		'message' => 'Update successfully',
		'updated_data' => $updated_data
	);
	return rest_ensure_response($response);
};

// write JS 
add_action('wp_head', 'wpcom_javascript');
function wpcom_javascript() {
	// Function for Timesheet Table
	if (is_page ('7823')) {
	?>
		<script>
		// Get All Data for Timesheet Table
		function get_all_data() {
			fetch('https://msnha.una.edu/wp-json/timesheet/v1/data')
			.then(function(response) {
				return response.json();
			})
			.then(function(data) {
				if (data.status === 'OK') {
					console.log(data.message);
					var container = document.getElementById('data-container');
					var retrievedData = data.data;
					var j = 0;
					for (var i = 0; i < retrievedData.length; i++) {
						let element = retrievedData[i];
						// only show row where status is NULL or empty
						if ((element.status == null) || (element.status == "")) {
							container.innerHTML += `
							<tr>
								<td>${j+1}</td>
								<td>${element.employee_name}</td>
								<td>${element.email}</td>
								<td>${element.position}</td>
								<td>$${element.rate}</td>
								<td>${element.date[5]+element.date[6]}/
									${element.date[8]+element.date[9]}/
									${element.date[0]+element.date[1]+element.date[2]+element.date[3]}</td>
								<td>${element.time_length}</td>
								<td>${element.project_name}</td>
								<td>${element.desc}</td>
								<td>${element.agenda_img}</td>
								<td>${element.miles}</td>
								<td>${element.mile_img}</td>
								<td>${element.timestamp}</td>
								<td>
								<button type="button" class="btn btn-danger" onclick="deleteRow(${element.id})">
									<i class="fa-solid fa-x"></i>
								</button>
								</td>
							</tr>`;
							j++;
						}           
					}
					// use Datatables.js to make table sortable			
					jQuery(document).ready(function() {
						jQuery('#timesheet-table').DataTable({
							dom: 'Bfrtip',
							buttons: [
								'copy', 
								{
									extend: 'excelHtml5',
									exportOptions: {
										format: {
											body: function(data, column, row) {
												if (typeof data === 'string' || data instanceof String) {
													data = data.replace(/<br\s*\/?>/ig, "\r\n");
												}
												return data;
											}
										}
									}
								}
							]
						});
					});		
				} else {
					console.error("We did something wrong...");
				}})
			.catch(function(error) {
				console.log(error);
			});
		};
		get_all_data();		

		// mark the row "Is Deleted" 
		function deleteRow(el) {				
			console.log(el);				
			fetch('https://msnha.una.edu/wp-json/timesheet_item/v1/update/' + el, {
				method: "PUT",
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('HTTP error, status = ' + response.status);
				}
				return response.json();
				})
			.then(function(data) {
				// document.getElementById('data-container').innerHTML = "";
				// get_all_data();
				location.reload();
			})
			.catch(function(error) {
				console.error(error);
			});
		}
		</script>
	<?php
	// Function for Grant Table
	} else if (is_page ('8411')) {
		?>
		<script>
			// get all data for grant table
			function get_all_data() {
				fetch('https://msnha.una.edu/wp-json/grantform/v1/data')
				.then(function(response) {
					return response.json();
				})
				.then(function(data) {
					if (data.status === 'OK') {
						console.log(data.message);
						var container = document.getElementById('data-container');
						var retrievedData = data.data;
						var j = 0;
						for (var i = 0; i < retrievedData.length; i++) {
							let element = retrievedData[i];
							if ((element.status == null) || (element.status == "")) {
								let existingRow = jQuery(`#data-container tr[data-id="${element.grant_id}"]`);
								if (!existingRow.length) {
									container.innerHTML += `
									<tr data-id="${element.grant_id}">
										<td>${j+1}</td>
										<td>${element.organization_name}</td>
										<td>${element.contact_name}</td>
										<td>${element.contact_email}</td>
										<td>${element.contact_phone}</td>
										<td>${element.contact_address}</td>
										<td>${element.org_type}</td>
										<td>${element.org_type_note}</td>
										<td>${element.project_type}</td>
										<td>${element.project_theme}</td>
										<td>${element.project_theme_note}</td>
										<td>${element.project_des}</td>
										<td>${element.start_date[5]+element.start_date[6]}/
											${element.start_date[8]+element.start_date[9]}/
											${element.start_date[0]+element.start_date[1]+element.start_date[2]+element.start_date[3]}</td>
										<td>${element.end_date[5]+element.end_date[6]}/
											${element.end_date[8]+element.end_date[9]}/
											${element.end_date[0]+element.end_date[1]+element.end_date[2]+element.end_date[3]}</td>								
										<td>${element.budget}</td>
										<td>${element.irs}</td>
										<td>${element.disclosure}</td>
										<td>${element.tax_exempt}</td>
										<td>${element.verified_letter}</td>
										<td data-column="path">${element.path}</td>
										<td>${element.timestamp}</td>
										<td>
											<button type="button" class="btn btn-danger" onclick="deleteRow(${element.grant_id})">
												<i class="fa-solid fa-x"></i>
											</button>
										</td>
									</tr>`;
									j++;
								} else {
									const existingValueCell = existingRow.find(`td[data-column="path"]`);
									existingValueCell.html(`${existingValueCell.text()}<br/><br/>${element.path}`);
								}	
							}						         
						}
						// use Datatables.js to make table sortable			
						jQuery(document).ready(function() {
							jQuery('#grant-table').DataTable({
								dom: 'Bfrtip',
								buttons: [
									'copy', 
									{
										extend: 'excelHtml5',
										exportOptions: {
											format: {
												body: function(data, column, row) {
													if (typeof data === 'string' || data instanceof String) {
														data = data.replace(/<br\s*\/?>/ig, "\r\n");
													}
													return data;
												}
											}
										}
									}
								]
							});
						});			
					} else {
						console.error("We did something wrong...");
					}})
				.catch(function(error) {
					console.log(error);
				});
			};
			get_all_data();

			// mark the row "Is Deleted" 
			function deleteRow(el) {				
				console.log(el);				
				fetch('https://msnha.una.edu/wp-json/grant_item/v1/update/' + el, {
					method: "PUT",
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(function(response) {
					if (!response.ok) {
						throw new Error('HTTP error, status = ' + response.status);
					}
					return response.json();
					})
				.then(function(data) {
					// document.getElementById('data-container').innerHTML = "";
					// get_all_data();
					location.reload();
				})
				.catch(function(error) {
					console.error(error);
				});
			}
		</script>
		<?php
	} else if (is_page ('8476')) {
		?>
		<script>	
			// get all data for sponsorship table
			function get_all_data() {
				fetch('https://msnha.una.edu/wp-json/sponsorship/v1/data')
				.then(function(response) {
					return response.json();
				})
				.then(function(data) {
					if (data.status === 'OK') {
						console.log(data.message);
						var container = document.getElementById('data-container');
						var retrievedData = data.data;
						var j = 0;
						for (var i = 0; i < retrievedData.length; i++) {
							let element = retrievedData[i];
							if ((element.status == null) || (element.status == "")) {
								let existingRow = jQuery(`#data-container tr[data-id="${element.sponsorship_id}"]`);
								if (!existingRow.length) {
									container.innerHTML += `
									<tr data-id="${element.sponsorship_id}">
										<td>${j+1}</td>
										<td>${element.org_name}</td>
										<td>${element.contact_name}</td>
										<td>${element.email}</td>
										<td>${element.phone}</td>
										<td>${element.web}</td>
										<td>${element.event_name}</td>
										<td>${element.event_date[5]+element.event_date[6]}/
											${element.event_date[8]+element.event_date[9]}/
											${element.event_date[0]+element.event_date[1]+element.event_date[2]+element.event_date[3]}</td>
										<td>${element.location}</td>
										<td>${element.desc}</td>
										<td>$${element.amount}</td>
										<td data-column="path">${element.path}</td>
										<td>${element.timestamp}</td>
										<td>
											<button type="button" class="btn btn-danger" onclick="deleteRow(${element.sponsorship_id})">
												<i class="fa-solid fa-x"></i>
											</button>
										</td>
									</tr>`;
									j++;
								} else {
									const existingValueCell = existingRow.find(`td[data-column="path"]`);
									existingValueCell.html(`${existingValueCell.text()}<br/><br/>${element.path}`);
								}		
							}					         
						}
						// use Datatables.js to make table sortable			
						jQuery(document).ready(function() {
							jQuery('#sponsorship-table').DataTable({
								dom: 'Bfrtip',
								buttons: [
									'copy', 
									{
										extend: 'excelHtml5',
										exportOptions: {
											format: {
												body: function(data, column, row) {
													if (typeof data === 'string' || data instanceof String) {
														data = data.replace(/<br\s*\/?>/ig, "\r\n");
													}
													return data;
												}
											}
										}
									}
								]
							});
						});			
					} else {
						console.error("We did something wrong...");
					}})
				.catch(function(error) {
					console.log(error);
				});
			};
			get_all_data();

			// mark the row "Is Deleted" 
			function deleteRow(el) {				
				console.log(el);				
				fetch('https://msnha.una.edu/wp-json/sponsorship_item/v1/update/' + el, {
					method: "PUT",
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(function(response) {
					if (!response.ok) {
						throw new Error('HTTP error, status = ' + response.status);
					}
					return response.json();
					})
				.then(function(data) {
					location.reload();
				})
				.catch(function(error) {
					console.error(error);
				});
			}
		</script>
		<?php
	} else if (is_page ('8488')) {
		?>
		<script>
			// get all data for assistance table
			function get_all_data() {
				fetch('https://msnha.una.edu/wp-json/assistance/v1/data')
				.then(function(response) {
					return response.json();
				})
				.then(function(data) {
					if (data.status === 'OK') {
						console.log(data.message);
						var container = document.getElementById('data-container');
						var retrievedData = data.data;
						var j = 0;
						for (var i = 0; i < retrievedData.length; i++) {
							let element = retrievedData[i];
							if ((element.status == null) || (element.status == "")) {
								container.innerHTML += `
								<tr>
									<td>${j+1}</td>
									<td>${element.org_name}</td>
									<td>${element.contact_name}</td>
									<td>${element.email}</td>
									<td>${element.phone}</td>
									<td>${element.web}</td>
									<td>${element.location}</td>
									<td>${element.desc}</td>
									<td>${element.assistance}</td>
									<td>${element.timeline}</td>
									<td>${element.work}</td>
									<td>${element.resources}</td>
									<td>${element.staff}</td>
									<td>${element.timestamp}</td>
									<td>
										<button type="button" class="btn btn-danger" onclick="deleteRow(${element.pj_id})">
											<i class="fa-solid fa-x"></i>
										</button>
									</td>
								</tr>`;
								j++;
							}							         
						}
						// use Datatables.js to make table sortable			
						jQuery(document).ready(function() {
							jQuery('#assistance-table').DataTable({
								dom: 'Bfrtip',
								buttons: [
									'copy', 
									{
										extend: 'excelHtml5',
										exportOptions: {
											format: {
												body: function(data, column, row) {
													if (typeof data === 'string' || data instanceof String) {
														data = data.replace(/<br\s*\/?>/ig, "\r\n");
													}
													return data;
												}
											}
										}
									}
								]
							});
						});			
					} else {
						console.error("We did something wrong...");
					}})
				.catch(function(error) {
					console.log(error);
				});
			};
			get_all_data();

			// mark the row "Is Deleted" 
			function deleteRow(el) {				
				console.log(el);				
				fetch('https://msnha.una.edu/wp-json/assistance_item/v1/update/' + el, {
					method: "PUT",
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(function(response) {
					if (!response.ok) {
						throw new Error('HTTP error, status = ' + response.status);
					}
					return response.json();
					})
				.then(function(data) {
					location.reload();
				})
				.catch(function(error) {
					console.error(error);
				});
			}
		</script>
		<?php
	} else if (is_page ('8651')) {
		?>
		<script>
			// get all data for grant report table
			function get_all_data() {
				fetch('https://msnha.una.edu/wp-json/grant_report/v1/grant_data')
				.then(function(response) {
					return response.json();
				})
				.then(function(data) {
					if (data.status === 'OK') {
						console.log(data.message);
						var container = document.getElementById('data-container');
						var retrievedData = data.data;
						var j = 0;						
						let picture_path_array = [];
						let doc_path_array = [];
						let backup_doc_path_array = [];
						for (var i = 0; i < retrievedData.length; i++) {	
							let element = retrievedData[i];
							if ((element.status == null) || (element.status == "")) {
								let existingRow = jQuery(`#data-container tr[data-id="${element.report_id}"]`);
								if (!existingRow.length) {
									container.innerHTML += `
									<tr data-id="${element.report_id}">
										<td>${j+1}</td>
										<td>${element.org_name}</td>
										<td>${element.contact_name}</td>
										<td>${element.email}</td>
										<td>${element.phone}</td>
										<td>${element.address}</td>
										<td>${element.addendum}</td>
										<td>${element.desc}</td>
										<td data-column="picture_path">${element.picture_path}</td>
										<td>${element.video}</td>
										<td data-column="doc_path">${element.doc_path}</td>
										<td>${element.budget}</td>
										<td data-column="backup_doc_path">${element.backup_doc_path}</td>
										<td>${element.invoice}</td>
										<td>${element.grant_received}</td>
										<td>${element.partnership}</td>
										<td>${element.letter1}<br><br>${element.letter2}</td>
										<td>${element.timestamp}</td>
										<td>
											<button type="button" class="btn btn-danger" onclick="deleteRow(${element.report_id})">
												<i class="fa-solid fa-x"></i>
											</button>
										</td>
									</tr>`;
									j++;
									// push url to empty array
									picture_path_array.push(`${element.picture_path}`);
									doc_path_array.push(`${element.doc_path}`);
									backup_doc_path_array.push(`${element.backup_doc_path}`);
								} else {
									// check if url appear in array, yes => pass, no => add to cell
									if (!picture_path_array.includes(`${element.picture_path}`)) {
										const picture_cell = existingRow.find(`td[data-column="picture_path"]`);
										picture_cell.append(`<br/><br/>${element.picture_path}`);
										picture_path_array.push(`${element.picture_path}`);
									}
									if (!doc_path_array.includes(`${element.doc_path}`)) {
										const doc_cell = existingRow.find(`td[data-column="doc_path"]`);
										doc_cell.append(`<br/><br/>${element.doc_path}`);
										doc_path_array.push(`${element.doc_path}`);
									}
									if (!backup_doc_path_array.includes(`${element.backup_doc_path}`)) {
										const backup_doc_cell = existingRow.find(`td[data-column="backup_doc_path"]`);
										backup_doc_cell.append(`<br/><br/>${element.backup_doc_path}`);
										backup_doc_path_array.push(`${element.backup_doc_path}`);
									}
								}
							}
						};					         
						// use Datatables.js to make table sortable			
						jQuery(document).ready(function() {
							jQuery('#grant-report-table').DataTable({
								dom: 'Bfrtip',
								buttons: [
									'copy', 
									{
										extend: 'excelHtml5',
										exportOptions: {
											format: {
												body: function(data, column, row) {
													if (typeof data === 'string' || data instanceof String) {
														data = data.replace(/<br\s*\/?>/ig, "\r\n");
													}
													return data;
												}
											}
										}
									}
								]
							});
						});			
					} else {
						console.error("We did something wrong...");
					}})
				.catch(function(error) {
					console.log(error);
				});
			};
			get_all_data();

			// mark the row "Is Deleted" 
			function deleteRow(el) {				
				console.log(el);				
				fetch('https://msnha.una.edu/wp-json/grant_report_item/v1/update/' + el, {
					method: "PUT",
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(function(response) {
					if (!response.ok) {
						throw new Error('HTTP error, status = ' + response.status);
					}
					return response.json();
					})
				.then(function(data) {
					location.reload();
				})
				.catch(function(error) {
					console.error(error);
				});
			}
		</script>
		<?php
	} else {

	}
}
////////////////////END//////////////////////////////