<?php
/*
Plugin Name: Estrus Synchronization Planner
Description: Integrates the React Estrus Synchronization Planner app into WordPress with ACF-based data management.
Version: 1.5.0
Author: Beef Reproduction Task Force
Requires Plugins: advanced-custom-fields-pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class Estrus_Synchronization_Planner {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin functionality
        add_action('init', [$this, 'register_post_types']);
        add_action('acf/init', [$this, 'register_acf_fields']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_protocols_import']);
        add_action('admin_init', [$this, 'handle_rules_import']);
        add_action('admin_init', [$this, 'handle_parameters_import']);
        add_action('admin_init', [$this, 'handle_save_protocols']);
        add_action('admin_init', [$this, 'handle_save_rules']);
        add_action('admin_init', [$this, 'handle_save_parameters']);
        add_action('admin_init', [$this, 'handle_reset_protocols']);
        add_action('admin_init', [$this, 'handle_reset_rules']);
        add_action('admin_init', [$this, 'handle_reset_parameters']);

        // Dynamic ACF field choices
        add_filter('acf/load_field/key=field_rule_category', [$this, 'load_rule_category_choices']);

        // Frontend shortcode
        add_shortcode('estrus_planner', [$this, 'render_shortcode']);
    }

    // =========================================================================
    // POST TYPES
    // =========================================================================

    /**
     * Register Custom Post Types
     */
    public function register_post_types() {
        // Protocol Post Type
        register_post_type('protocol', [
            'labels' => [
                'name'               => 'Protocols',
                'singular_name'      => 'Protocol',
                'menu_name'          => 'Protocols',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Protocol',
                'edit_item'          => 'Edit Protocol',
                'new_item'           => 'New Protocol',
                'view_item'          => 'View Protocol',
                'search_items'       => 'Search Protocols',
                'not_found'          => 'No protocols found',
                'not_found_in_trash' => 'No protocols found in trash',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'esp-config',
            'supports'            => ['title'],
            'has_archive'         => false,
            'rewrite'             => false,
            'show_in_rest'        => true,
        ]);

        // Selection Rule Post Type
        register_post_type('selection_rule', [
            'labels' => [
                'name'               => 'Selection Rules',
                'singular_name'      => 'Selection Rule',
                'menu_name'          => 'Selection Rules',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Rule',
                'edit_item'          => 'Edit Rule',
                'new_item'           => 'New Rule',
                'view_item'          => 'View Rule',
                'search_items'       => 'Search Rules',
                'not_found'          => 'No rules found',
                'not_found_in_trash' => 'No rules found in trash',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'esp-config',
            'supports'            => ['title'],
            'has_archive'         => false,
            'rewrite'             => false,
            'show_in_rest'        => true,
        ]);

        // User Input Parameters Post Type
        register_post_type('app_parameter', [
            'labels' => [
                'name'               => 'User Input Options',
                'singular_name'      => 'User Input Option',
                'menu_name'          => 'User Input Options',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Option',
                'edit_item'          => 'Edit Option',
                'new_item'           => 'New Option',
                'view_item'          => 'View Option',
                'search_items'       => 'Search Options',
                'not_found'          => 'No options found',
                'not_found_in_trash' => 'No options found in trash',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'esp-config',
            'supports'            => ['title'],
            'has_archive'         => false,
            'rewrite'             => false,
            'show_in_rest'        => true,
        ]);
    }

    // =========================================================================
    // ACF FIELD GROUPS
    // =========================================================================

    /**
     * Register ACF Field Groups
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        // Protocol Field Group
        acf_add_local_field_group([
            'key'      => 'group_protocol_fields',
            'title'    => 'Protocol Details',
            'fields'   => [
                // Basic Info
                [
                    'key'   => 'field_protocol_id',
                    'label' => 'Protocol ID',
                    'name'  => 'protocol_id',
                    'type'  => 'number',
                    'required' => 1,
                ],
                [
                    'key'   => 'field_synchronization_system_title',
                    'label' => 'Synchronization System Title',
                    'name'  => 'synchronization_system_title',
                    'type'  => 'text',
                    'required' => 1,
                ],
                [
                    'key'   => 'field_not_verified',
                    'label' => 'Not Verified',
                    'name'  => 'not_verified',
                    'type'  => 'true_false',
                    'default_value' => 0,
                ],
                // Numeric Fields
                [
                    'key'   => 'field_number_of_pgf_shots_per_female',
                    'label' => 'Number of PGF Shots Per Female',
                    'name'  => 'number_of_pgf_shots_per_female',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_gnrh_shots_per_female',
                    'label' => 'Number of GnRH Shots Per Female',
                    'name'  => 'number_of_gnrh_shots_per_female',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_cidrs_per_female',
                    'label' => 'Number of CIDRs Per Female',
                    'name'  => 'number_of_cidrs_per_female',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_days_on_mga',
                    'label' => 'Number of Days on MGA',
                    'name'  => 'number_of_days_on_mga',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_days_drylot',
                    'label' => 'Number of Days Drylot',
                    'name'  => 'number_of_days_drylot',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_days_ai',
                    'label' => 'Number of Days AI',
                    'name'  => 'number_of_days_ai',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_tech_trips',
                    'label' => 'Number of Tech Trips',
                    'name'  => 'number_of_tech_trips',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_heat_checks',
                    'label' => 'Number of Heat Checks',
                    'name'  => 'number_of_heat_checks',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_number_of_chute_times',
                    'label' => 'Number of Chute Times',
                    'name'  => 'number_of_chute_times',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_response_start_percentage',
                    'label' => 'Response Start Percentage',
                    'name'  => 'response_start_percentage',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_conception_start_percentage',
                    'label' => 'Conception Start Percentage',
                    'name'  => 'conception_start_percentage',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_days_worked_for_labor_costs',
                    'label' => 'Days Worked for Labor Costs',
                    'name'  => 'days_worked_for_labor_costs',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_days_start_to_end',
                    'label' => 'Days Start to End',
                    'name'  => 'days_start_to_end',
                    'type'  => 'number',
                    'step'  => 'any',
                ],
                [
                    'key'   => 'field_pg_shot',
                    'label' => 'PG Shot',
                    'name'  => 'pg_shot',
                    'type'  => 'text',
                ],
                [
                    'key'   => 'field_pull_cidr',
                    'label' => 'Pull CIDR',
                    'name'  => 'pull_cidr',
                    'type'  => 'text',
                ],
                [
                    'key'   => 'field_gnrh',
                    'label' => 'GNRH',
                    'name'  => 'gnrh',
                    'type'  => 'text',
                ],
                // Instructions Repeater
                [
                    'key'   => 'field_instructions',
                    'label' => 'Instructions',
                    'name'  => 'instructions',
                    'type'  => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Instruction',
                    'sub_fields' => [
                        [
                            'key'   => 'field_date_adjustment',
                            'label' => 'Date Adjustment',
                            'name'  => 'date_adjustment',
                            'type'  => 'text',
                            'instructions' => 'Hours offset or special value like "BullTurnIn"',
                        ],
                        [
                            'key'   => 'field_date_adjustment_unit',
                            'label' => 'Date Adjustment Unit',
                            'name'  => 'date_adjustment_unit',
                            'type'  => 'select',
                            'choices' => [
                                'hours' => 'Hours',
                                'days'  => 'Days',
                            ],
                            'default_value' => 'hours',
                        ],
                        [
                            'key'   => 'field_is_pg',
                            'label' => 'Is PG',
                            'name'  => 'is_pg',
                            'type'  => 'true_false',
                        ],
                        [
                            'key'   => 'field_from_pg',
                            'label' => 'From PG',
                            'name'  => 'from_pg',
                            'type'  => 'true_false',
                        ],
                        // Lines nested repeater
                        [
                            'key'   => 'field_lines',
                            'label' => 'Lines',
                            'name'  => 'lines',
                            'type'  => 'repeater',
                            'layout' => 'table',
                            'button_label' => 'Add Line',
                            'sub_fields' => [
                                [
                                    'key'   => 'field_label',
                                    'label' => 'Label',
                                    'name'  => 'label',
                                    'type'  => 'textarea',
                                    'rows'  => 2,
                                ],
                                [
                                    'key'   => 'field_hour_adjustment',
                                    'label' => 'Hour Adjustment',
                                    'name'  => 'hour_adjustment',
                                    'type'  => 'number',
                                    'instructions' => 'Optional hour offset for this line',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'protocol',
                    ],
                ],
            ],
            'menu_order' => 0,
        ]);

        // Selection Rule Field Group
        // Note: choices for rule_category are loaded dynamically via acf/load_field filter
        acf_add_local_field_group([
            'key'      => 'group_selection_rule_fields',
            'title'    => 'Rule Details',
            'fields'   => [
                [
                    'key'   => 'field_rule_category',
                    'label' => 'Rule Category',
                    'name'  => 'rule_category',
                    'type'  => 'select',
                    'required' => 1,
                    'choices' => [], // Populated dynamically by load_rule_category_choices()
                ],
                [
                    'key'   => 'field_priority_level',
                    'label' => 'Priority Level',
                    'name'  => 'priority_level',
                    'type'  => 'number',
                    'required' => 1,
                    'min'   => 1,
                ],
                [
                    'key'   => 'field_event_type',
                    'label' => 'Event Type (Protocol ID)',
                    'name'  => 'event_type',
                    'type'  => 'text',
                    'required' => 1,
                    'instructions' => 'Protocol ID or header text',
                ],
                // Conditions Repeater
                [
                    'key'   => 'field_conditions',
                    'label' => 'Conditions',
                    'name'  => 'conditions',
                    'type'  => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Condition',
                    'sub_fields' => [
                        [
                            'key'   => 'field_fact',
                            'label' => 'Fact',
                            'name'  => 'fact',
                            'type'  => 'select',
                            'choices' => [
                                'BreedType'  => 'Breed Type',
                                'SystemType' => 'System Type',
                                'SemenType'  => 'Semen Type',
                            ],
                        ],
                        [
                            'key'   => 'field_operator',
                            'label' => 'Operator',
                            'name'  => 'operator',
                            'type'  => 'select',
                            'choices' => [
                                'equal'    => 'Equal',
                                'notEqual' => 'Not Equal',
                            ],
                            'default_value' => 'equal',
                        ],
                        [
                            'key'   => 'field_value',
                            'label' => 'Value',
                            'name'  => 'value',
                            'type'  => 'text',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'selection_rule',
                    ],
                ],
            ],
            'menu_order' => 0,
        ]);

        // App Parameter Field Group
        acf_add_local_field_group([
            'key'      => 'group_app_parameter_fields',
            'title'    => 'Parameter Details',
            'fields'   => [
                [
                    'key'   => 'field_parameter_key',
                    'label' => 'Parameter Key',
                    'name'  => 'parameter_key',
                    'type'  => 'select',
                    'required' => 1,
                    'choices' => [
                        'Cow or Heifer' => 'Cow or Heifer',
                        'Breed Type'    => 'Breed Type',
                        'Semen Type'    => 'Semen Type',
                        'System Type'   => 'System Type',
                        'GnRH'          => 'GnRH',
                        'PG'            => 'PG',
                    ],
                    'instructions' => 'The parameter category this belongs to',
                ],
                [
                    'key'   => 'field_parameter_options',
                    'label' => 'Options',
                    'name'  => 'parameter_options',
                    'type'  => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Option',
                    'sub_fields' => [
                        [
                            'key'   => 'field_option_value',
                            'label' => 'Option Value',
                            'name'  => 'option_value',
                            'type'  => 'text',
                            'required' => 1,
                        ],
                        [
                            'key'   => 'field_option_dosage',
                            'label' => 'Dosage',
                            'name'  => 'option_dosage',
                            'type'  => 'text',
                            'instructions' => 'For GnRH/PG only (e.g., "2cc", "5cc")',
                        ],
                        [
                            'key'   => 'field_option_header',
                            'label' => 'Display Header',
                            'name'  => 'option_header',
                            'type'  => 'text',
                            'instructions' => 'For System Type only (e.g., "Heat Detect & Breed")',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'app_parameter',
                    ],
                ],
            ],
            'menu_order' => 0,
        ]);
    }

    /**
     * Dynamically load rule category choices from "Cow or Heifer" parameter
     * This filter runs when the field is loaded, so it always has current data
     */
    public function load_rule_category_choices($field) {
        // Get animal types from database
        $animal_types = ['Cow', 'Heifer']; // Default fallback

        $parameters = get_posts([
            'post_type'      => 'app_parameter',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        foreach ($parameters as $param) {
            // Use get_post_meta directly since this runs early
            $param_key = get_post_meta($param->ID, 'parameter_key', true);
            if ($param_key === 'Cow or Heifer') {
                // Get the repeater field data directly from post meta
                $options_count = (int) get_post_meta($param->ID, 'parameter_options', true);
                if ($options_count > 0) {
                    $options = [];
                    for ($i = 0; $i < $options_count; $i++) {
                        $option_value = get_post_meta($param->ID, 'parameter_options_' . $i . '_option_value', true);
                        if ($option_value) {
                            $options[] = $option_value;
                        }
                    }
                    if (!empty($options)) {
                        $animal_types = $options;
                    }
                }
                break;
            }
        }

        // Build choices array
        $choices = [];
        foreach ($animal_types as $type) {
            $type_lower = strtolower(str_replace(' ', '_', $type));
            $choices[$type_lower . '_preferred'] = $type . ' Preferred Systems';
            $choices[$type_lower . '_less_preferred'] = $type . ' Less Preferred Systems';
        }
        $choices['system_type_header'] = 'Selected System Type Protocol Header';

        $field['choices'] = $choices;
        return $field;
    }

    // =========================================================================
    // ADMIN MENUS
    // =========================================================================

    /**
     * Add Admin Menus - Parent menu with all post types grouped
     */
    public function add_admin_menu() {
        // Parent Menu
        add_menu_page(
            'Estrus Planner',
            'Estrus Planner',
            'manage_options',
            'esp-config',
            [$this, 'render_dashboard_page'],
            'dashicons-calendar-alt',
            26
        );

        // Dashboard (first submenu replaces parent)
        add_submenu_page(
            'esp-config',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'esp-config',
            [$this, 'render_dashboard_page']
        );
    }

    /**
     * Render Dashboard Page
     */
    public function render_dashboard_page() {
        $protocols_count = wp_count_posts('protocol')->publish;
        $rules_count = wp_count_posts('selection_rule')->publish;
        $params_count = wp_count_posts('app_parameter')->publish;
        $acf_active = $this->is_acf_active();

        // Check which saved files exist
        $saved_dir = $this->get_saved_dir();
        $has_saved_protocols  = file_exists($saved_dir . 'protocols.json');
        $has_saved_rules      = file_exists($saved_dir . 'rules.json');
        $has_saved_parameters = file_exists($saved_dir . 'parameters.json');

        // Check if default files exist (backup or original shipped files)
        $backups_dir = $this->get_backups_dir();
        $has_default_protocols  = file_exists($backups_dir . 'Protocols.json') || $this->find_json_file('Protocols.json');
        $has_default_rules      = file_exists($backups_dir . 'test-rules.json') || $this->find_json_file('test-rules.json');
        $has_default_parameters = $has_default_protocols; // Parameters come from Protocols.json
        ?>
        <div class="wrap">
            <h1>Estrus Synchronization Planner</h1>

            <?php if (isset($_GET['imported'])): ?>
                <div class="notice notice-success is-dismissible" style="max-width: 800px; margin-top: 20px;">
                    <p><strong>Import completed!</strong> <?php echo intval($_GET['count']); ?> <?php echo esc_html($_GET['type'] ?? 'records'); ?> imported.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['saved'])): ?>
                <div class="notice notice-success is-dismissible" style="max-width: 800px; margin-top: 20px;">
                    <p><strong>Saved!</strong> <?php echo esc_html(ucfirst($_GET['type'] ?? 'Data')); ?> exported to file successfully.</p>
                </div>
            <?php endif; ?>

            <!-- Current Data Section -->
            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Current Data</h2>
                <table class="widefat" style="max-width: 700px;">
                    <tr>
                        <td><strong>Protocols</strong></td>
                        <td><?php echo $protocols_count; ?> records</td>
                        <td><a href="<?php echo admin_url('edit.php?post_type=protocol'); ?>">View</a></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('save_protocols', 'save_protocols_nonce'); ?>
                                <input type="submit" name="do_save_protocols" class="button button-small" value="Save to File" <?php echo (!$acf_active || $protocols_count == 0) ? 'disabled' : ''; ?>>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Selection Rules</strong></td>
                        <td><?php echo $rules_count; ?> records</td>
                        <td><a href="<?php echo admin_url('edit.php?post_type=selection_rule'); ?>">View</a></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('save_rules', 'save_rules_nonce'); ?>
                                <input type="submit" name="do_save_rules" class="button button-small" value="Save to File" <?php echo (!$acf_active || $rules_count == 0) ? 'disabled' : ''; ?>>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>User Input Options</strong></td>
                        <td><?php echo $params_count; ?> records</td>
                        <td><a href="<?php echo admin_url('edit.php?post_type=app_parameter'); ?>">View</a></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('save_parameters', 'save_parameters_nonce'); ?>
                                <input type="submit" name="do_save_parameters" class="button button-small" value="Save to File" <?php echo (!$acf_active || $params_count == 0) ? 'disabled' : ''; ?>>
                            </form>
                        </td>
                    </tr>
                </table>
                <p class="description" style="margin-top: 10px;">
                    "Save to File" exports current data so it can be re-imported later.
                    <?php if ($has_saved_protocols || $has_saved_rules || $has_saved_parameters): ?>
                        <br>Saved files:
                        <?php if ($has_saved_protocols): ?><code>protocols.json</code> <?php endif; ?>
                        <?php if ($has_saved_rules): ?><code>rules.json</code> <?php endif; ?>
                        <?php if ($has_saved_parameters): ?><code>parameters.json</code> <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Shortcode Usage Section -->
            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Shortcode Usage</h2>
                <p>Use the following shortcode to embed the Estrus Synchronization Planner on any page:</p>
                <code style="display: block; padding: 10px; background: #f0f0f0; margin: 10px 0;">[estrus_planner]</code>
            </div>

            <?php if (!$acf_active): ?>
                <div class="notice notice-error" style="max-width: 800px; margin-top: 20px;">
                    <p><strong>ACF Pro Required!</strong> Please install and activate Advanced Custom Fields Pro to enable import functionality.</p>
                </div>
            <?php endif; ?>

            <!-- Import Section -->
            <h2 style="margin-top: 30px;">Import Data</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; max-width: 1200px;">

                <!-- Import Protocols -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h3>Import Protocols</h3>
                    <p><strong>Current:</strong> <?php echo $protocols_count; ?> records</p>
                    <form method="post" style="margin-top: 15px;">
                        <?php wp_nonce_field('import_protocols', 'import_protocols_nonce'); ?>
                        <p>
                            <label>
                                <input type="checkbox" name="clear_existing" value="1" checked>
                                Clear existing protocols before import
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="from_saved" value="1" <?php echo !$has_saved_protocols ? 'disabled' : ''; ?>>
                                Import from saved file <?php echo !$has_saved_protocols ? '<em>(no saved file)</em>' : ''; ?>
                            </label>
                        </p>
                        <p style="display: flex; gap: 8px;">
                            <input type="submit" name="do_import_protocols" class="button button-primary" value="Import" <?php echo !$acf_active ? 'disabled' : ''; ?>>
                        </p>
                    </form>
                    <?php if ($has_default_protocols): ?>
                    <form method="post" style="margin-top: 8px;">
                        <?php wp_nonce_field('reset_protocols', 'reset_protocols_nonce'); ?>
                        <input type="submit" name="do_reset_protocols" class="button" value="Reset to Defaults" <?php echo !$acf_active ? 'disabled' : ''; ?> onclick="return confirm('This will clear all protocols and re-import the original defaults. Continue?');">
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Import Selection Rules -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h3>Import Selection Rules</h3>
                    <p><strong>Current:</strong> <?php echo $rules_count; ?> records</p>
                    <form method="post" style="margin-top: 15px;">
                        <?php wp_nonce_field('import_rules', 'import_rules_nonce'); ?>
                        <p>
                            <label>
                                <input type="checkbox" name="clear_existing" value="1" checked>
                                Clear existing rules before import
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="from_saved" value="1" <?php echo !$has_saved_rules ? 'disabled' : ''; ?>>
                                Import from saved file <?php echo !$has_saved_rules ? '<em>(no saved file)</em>' : ''; ?>
                            </label>
                        </p>
                        <p style="display: flex; gap: 8px;">
                            <input type="submit" name="do_import_rules" class="button button-primary" value="Import" <?php echo !$acf_active ? 'disabled' : ''; ?>>
                        </p>
                    </form>
                    <?php if ($has_default_rules): ?>
                    <form method="post" style="margin-top: 8px;">
                        <?php wp_nonce_field('reset_rules', 'reset_rules_nonce'); ?>
                        <input type="submit" name="do_reset_rules" class="button" value="Reset to Defaults" <?php echo !$acf_active ? 'disabled' : ''; ?> onclick="return confirm('This will clear all rules and re-import the original defaults. Continue?');">
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Import User Input Options -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h3>Import User Input Options</h3>
                    <p><strong>Current:</strong> <?php echo $params_count; ?> records</p>
                    <form method="post" style="margin-top: 15px;">
                        <?php wp_nonce_field('import_parameters', 'import_parameters_nonce'); ?>
                        <p>
                            <label>
                                <input type="checkbox" name="clear_existing" value="1" checked>
                                Clear existing options before import
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="from_saved" value="1" <?php echo !$has_saved_parameters ? 'disabled' : ''; ?>>
                                Import from saved file <?php echo !$has_saved_parameters ? '<em>(no saved file)</em>' : ''; ?>
                            </label>
                        </p>
                        <p style="display: flex; gap: 8px;">
                            <input type="submit" name="do_import_parameters" class="button button-primary" value="Import" <?php echo !$acf_active ? 'disabled' : ''; ?>>
                        </p>
                    </form>
                    <?php if ($has_default_parameters): ?>
                    <form method="post" style="margin-top: 8px;">
                        <?php wp_nonce_field('reset_parameters', 'reset_parameters_nonce'); ?>
                        <input type="submit" name="do_reset_parameters" class="button" value="Reset to Defaults" <?php echo !$acf_active ? 'disabled' : ''; ?> onclick="return confirm('This will clear all user input options and re-import the original defaults. Continue?');">
                    </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Check if ACF is active
     */
    private function is_acf_active() {
        return function_exists('update_field') && function_exists('get_field');
    }

    // =========================================================================
    // IMPORT HANDLERS
    // =========================================================================

    /**
     * Handle Protocols Import
     */
    public function handle_protocols_import() {
        if (!isset($_POST['do_import_protocols'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['import_protocols_nonce'], 'import_protocols')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        if (isset($_POST['clear_existing'])) {
            $this->clear_post_type('protocol');
        }

        $source_path = null;
        if (isset($_POST['from_saved'])) {
            $source_path = $this->get_saved_dir() . 'protocols.json';
        }
        $count = $this->import_protocols($source_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'protocols',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Rules Import
     */
    public function handle_rules_import() {
        if (!isset($_POST['do_import_rules'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['import_rules_nonce'], 'import_rules')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        if (isset($_POST['clear_existing'])) {
            $this->clear_post_type('selection_rule');
        }

        $source_path = null;
        if (isset($_POST['from_saved'])) {
            $source_path = $this->get_saved_dir() . 'rules.json';
        }
        $count = $this->import_rules($source_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'rules',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Parameters Import
     */
    public function handle_parameters_import() {
        if (!isset($_POST['do_import_parameters'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['import_parameters_nonce'], 'import_parameters')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        if (isset($_POST['clear_existing'])) {
            $this->clear_post_type('app_parameter');
        }

        $source_path = null;
        if (isset($_POST['from_saved'])) {
            $source_path = $this->get_saved_dir() . 'parameters.json';
        }
        $count = $this->import_parameters($source_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'user input options',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Clear all posts of a given type
     */
    private function clear_post_type($post_type) {
        $posts = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ]);

        foreach ($posts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }

    /**
     * Import Protocols from JSON
     */
    private function import_protocols($override_path = null) {
        $json_path = $override_path ?: $this->find_json_file('Protocols.json');

        if (!$json_path || !file_exists($json_path)) {
            return 0;
        }

        $json_content = file_get_contents($json_path);
        $data = json_decode($json_content, true);

        if (!$data || !isset($data['Protocols'][0])) {
            return 0;
        }

        $not_verified = $data['NotVerified'] ?? [];
        $count = 0;

        foreach ($data['Protocols'][0] as $key => $protocol) {
            // Skip placeholder
            if ($key === '0' || $key === 0) {
                continue;
            }

            $protocol_id = $protocol['id'] ?? $key;
            $protocol_title = $protocol['SynchronizationSystemTitle'] ?? 'Protocol ' . $key;
            $post_id = wp_insert_post([
                'post_type'   => 'protocol',
                'post_title'  => '(' . $protocol_id . ') ' . $protocol_title,
                'post_status' => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            // Update ACF fields
            update_field('protocol_id', (int) $protocol_id, $post_id);
            update_field('synchronization_system_title', $protocol['SynchronizationSystemTitle'] ?? '', $post_id);
            update_field('not_verified', in_array((int) $protocol_id, $not_verified) || !empty($protocol['notVerified']), $post_id);

            // Numeric fields
            update_field('number_of_pgf_shots_per_female', $this->to_float($protocol['NumberOfPGFShotsPerFemale'] ?? 0), $post_id);
            update_field('number_of_gnrh_shots_per_female', $this->to_float($protocol['NumberOfGnRHShotsPerFemale'] ?? 0), $post_id);
            update_field('number_of_cidrs_per_female', $this->to_float($protocol['NumberOfCIDRsPerFemale'] ?? 0), $post_id);
            update_field('number_of_days_on_mga', $this->to_float($protocol['NumberOfDaysOnMGA'] ?? 0), $post_id);
            update_field('number_of_days_drylot', $this->to_float($protocol['NumberOfDaysDrylot'] ?? 0), $post_id);
            update_field('number_of_days_ai', $this->to_float($protocol['NumberOfDaysAI'] ?? 0), $post_id);
            update_field('number_of_tech_trips', $this->to_float($protocol['NumberOfTechTrips'] ?? 0), $post_id);
            update_field('number_of_heat_checks', $this->to_float($protocol['NumberOfHeatChecks'] ?? 0), $post_id);
            update_field('number_of_chute_times', $this->to_float($protocol['NumberOfChuteTimes'] ?? 0), $post_id);
            update_field('response_start_percentage', $this->to_float($protocol['ResponseStartPercentage'] ?? 0), $post_id);
            update_field('conception_start_percentage', $this->to_float($protocol['ConcpeptionStartPercentage'] ?? 0), $post_id);
            update_field('days_worked_for_labor_costs', $this->to_float($protocol['DaysWorkedForLaborCosts'] ?? 0), $post_id);
            update_field('days_start_to_end', $this->to_float($protocol['DaysStartToEnd'] ?? 0), $post_id);

            // Text fields
            update_field('pg_shot', $protocol['PGShot'] ?? '', $post_id);
            update_field('pull_cidr', $protocol['PullCIDR'] ?? '', $post_id);
            update_field('gnrh', $protocol['GNRH'] ?? '', $post_id);

            // Instructions repeater
            $instructions = [];
            if (!empty($protocol['instructions'])) {
                foreach ($protocol['instructions'] as $instruction) {
                    $lines = [];
                    if (!empty($instruction['lines'])) {
                        foreach ($instruction['lines'] as $line) {
                            $label = $line['label'] ?? '';
                            // Replace hardcoded medications with placeholders for consistency
                            $label = str_replace('2cc Cystorelin (GnRH)', '<<gnrh_type>>', $label);
                            $label = str_replace('5cc Lutalyse (PG)', '<<pg_type>>', $label);
                            $label = str_replace('5cc Lutalyse (GnRH)', '<<pg_type>>', $label); // Fix Protocol 36 mislabel

                            $lines[] = [
                                'field_label' => $label,
                                'field_hour_adjustment' => $line['hourAdjustment'] ?? '',
                            ];
                        }
                    }

                    $instructions[] = [
                        'field_date_adjustment'      => (string) ($instruction['dateAdjustment'] ?? '0'),
                        'field_date_adjustment_unit' => $instruction['dateAdjustmentUnit'] ?? 'hours',
                        'field_is_pg'                => !empty($instruction['isPg']) ? 1 : 0,
                        'field_from_pg'              => !empty($instruction['fromPg']) ? 1 : 0,
                        'field_lines'                => $lines,
                    ];
                }
            }
            update_field('instructions', $instructions, $post_id);

            $count++;
        }

        return $count;
    }

    /**
     * Import Rules from JSON
     */
    private function import_rules($override_path = null) {
        $json_path = $override_path ?: $this->find_json_file('test-rules.json');

        if (!$json_path || !file_exists($json_path)) {
            return 0;
        }

        $json_content = file_get_contents($json_path);
        $data = json_decode($json_content, true);

        if (!$data) {
            return 0;
        }

        $animal_categories = $this->get_animal_type_categories();
        $category_map = $animal_categories['name_to_key'];

        $count = 0;

        foreach ($data as $category_name => $priorities) {
            $category_key = $category_map[$category_name] ?? null;
            if (!$category_key) {
                continue;
            }

            foreach ($priorities as $priority_level => $rules) {
                foreach ($rules as $rule) {
                    $event_type = $rule['event']['type'] ?? '';

                    $post_id = wp_insert_post([
                        'post_type'   => 'selection_rule',
                        'post_title'  => $category_name . ' - Priority ' . $priority_level . ' - Event ' . $event_type,
                        'post_status' => 'publish',
                    ]);

                    if (is_wp_error($post_id)) {
                        continue;
                    }

                    // Update ACF fields
                    update_field('rule_category', $category_key, $post_id);
                    update_field('priority_level', (int) $priority_level, $post_id);
                    update_field('event_type', (string) $event_type, $post_id);

                    // Conditions repeater
                    $conditions = [];
                    if (!empty($rule['conditions']['all'])) {
                        foreach ($rule['conditions']['all'] as $condition) {
                            $conditions[] = [
                                'field_fact'     => $condition['fact'] ?? '',
                                'field_operator' => $condition['operator'] ?? 'equal',
                                'field_value'    => $condition['value'] ?? '',
                            ];
                        }
                    }
                    update_field('conditions', $conditions, $post_id);

                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Import Parameters from JSON
     */
    private function import_parameters($override_path = null) {
        $json_path = $override_path ?: $this->find_json_file('Protocols.json');

        if (!$json_path || !file_exists($json_path)) {
            return 0;
        }

        $json_content = file_get_contents($json_path);
        $data = json_decode($json_content, true);

        if (!$data || !isset($data['Parameters'][0])) {
            return 0;
        }

        $parameters = $data['Parameters'][0];
        $params_meta = $data['ParametersMeta'] ?? [];
        $count = 0;

        // Default dosages for GnRH products (used when ParametersMeta not present)
        $gnrh_dosages = [
            'Cystorelin' => '2cc',
            'Factrel'    => '2cc',
            'Fertagyl'   => '2cc',
            'OvaCyst'    => '2cc',
            'GONAbreed'  => '1cc',
            'GnRH'       => '',
        ];

        // Default dosages for PG products
        $pg_dosages = [
            'Estrumate'      => '2cc',
            'EstroPLAN'      => '2cc',
            'InSynch'        => '5cc',
            'Lutalyse'       => '5cc',
            'ProstaMate'     => '5cc',
            'Lutalyse HighCon' => '2cc',
            'Synchsure'      => '2cc',
            'PG'             => '',
        ];

        // Default headers for System Types
        $system_headers = [
            'Estrus AI'              => 'Heat Detect & Breed',
            'Estrus AI + Clean-up AI' => 'Heat Detect & Clean-up AI',
            'Fixed-Time AI'          => 'Fixed-Time AI',
            'Split Time AI'          => 'Split Time AI',
        ];

        foreach ($parameters as $param_key => $options) {
            $post_id = wp_insert_post([
                'post_type'   => 'app_parameter',
                'post_title'  => $param_key,
                'post_status' => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            // Update ACF fields
            update_field('parameter_key', $param_key, $post_id);

            // Build options repeater with dosage/header
            // Use ParametersMeta from file when available, fall back to hardcoded defaults
            $options_data = [];
            foreach ($options as $option) {
                $option_entry = [
                    'field_option_value' => $option,
                    'field_option_dosage' => '',
                    'field_option_header' => '',
                ];

                // Check ParametersMeta first (from saved files)
                if (isset($params_meta[$param_key][$option])) {
                    $meta = $params_meta[$param_key][$option];
                    if (isset($meta['dosage'])) {
                        $option_entry['field_option_dosage'] = $meta['dosage'];
                    }
                    if (isset($meta['header'])) {
                        $option_entry['field_option_header'] = $meta['header'];
                    }
                } else {
                    // Fall back to hardcoded defaults
                    if ($param_key === 'GnRH' && isset($gnrh_dosages[$option])) {
                        $option_entry['field_option_dosage'] = $gnrh_dosages[$option];
                    }
                    if ($param_key === 'PG' && isset($pg_dosages[$option])) {
                        $option_entry['field_option_dosage'] = $pg_dosages[$option];
                    }
                    if ($param_key === 'System Type' && isset($system_headers[$option])) {
                        $option_entry['field_option_header'] = $system_headers[$option];
                    }
                }

                $options_data[] = $option_entry;
            }
            update_field('parameter_options', $options_data, $post_id);

            $count++;
        }

        return $count;
    }

    /**
     * Find JSON file in possible locations
     */
    private function find_json_file($filename) {
        $possible_paths = [
            // Plugin directory
            plugin_dir_path(__FILE__) . $filename,
            plugin_dir_path(__FILE__) . 'data/' . $filename,
            // Parent plugin directory
            dirname(plugin_dir_path(__FILE__)) . '/beef-app/' . $filename,
            // Theme directory
            get_template_directory() . '/beef-app/' . $filename,
            // Uploads directory
            wp_upload_dir()['basedir'] . '/beef-app/' . $filename,
        ];

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Convert value to float, handling strings
     */
    private function to_float($value) {
        if ($value === '' || $value === null || $value === 'x') {
            return 0;
        }
        return (float) $value;
    }

    // =========================================================================
    // FRONTEND - ACF DATA RETRIEVAL
    // =========================================================================

    /**
     * Get Parameters from ACF app_parameter post type
     */
    private function get_parameters_data() {
        // Check if ACF is active
        if (!function_exists('get_field') || !function_exists('have_rows')) {
            return null;
        }

        $parameters = get_posts([
            'post_type'      => 'app_parameter',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if (empty($parameters)) {
            return null; // Fallback to hardcoded defaults
        }

        $params_data = [];
        $params_meta = [];

        foreach ($parameters as $param) {
            $param_key = get_field('parameter_key', $param->ID);
            $options = [];
            $meta = [];

            if (have_rows('parameter_options', $param->ID)) {
                while (have_rows('parameter_options', $param->ID)) {
                    the_row();
                    $option_value = get_sub_field('option_value');
                    $option_dosage = get_sub_field('option_dosage');
                    $option_header = get_sub_field('option_header');

                    $options[] = $option_value;

                    // Store metadata if dosage or header exists
                    if ($option_dosage || $option_header) {
                        $meta[$option_value] = [];
                        if ($option_dosage) {
                            $meta[$option_value]['dosage'] = $option_dosage;
                        }
                        if ($option_header) {
                            $meta[$option_value]['header'] = $option_header;
                        }
                    }
                }
            }

            if ($param_key && !empty($options)) {
                $params_data[$param_key] = $options;
                if (!empty($meta)) {
                    $params_meta[$param_key] = $meta;
                }
            }
        }

        return !empty($params_data) ? [
            'values' => $params_data,
            'meta'   => $params_meta,
        ] : null;
    }

    /**
     * Get animal types from "Cow or Heifer" parameter and build category maps
     * Returns array with 'choices', 'name_to_key', 'key_to_name', and 'empty_data'
     */
    private function get_animal_type_categories() {
        $animal_types = ['Cow', 'Heifer']; // Default fallback

        // Try to get from database using get_post_meta directly for reliability
        $parameters = get_posts([
            'post_type'      => 'app_parameter',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        foreach ($parameters as $param) {
            $param_key = get_post_meta($param->ID, 'parameter_key', true);
            if ($param_key === 'Cow or Heifer') {
                // Get the repeater field data directly from post meta
                $options_count = (int) get_post_meta($param->ID, 'parameter_options', true);
                if ($options_count > 0) {
                    $options = [];
                    for ($i = 0; $i < $options_count; $i++) {
                        $option_value = get_post_meta($param->ID, 'parameter_options_' . $i . '_option_value', true);
                        if ($option_value) {
                            $options[] = $option_value;
                        }
                    }
                    if (!empty($options)) {
                        $animal_types = $options;
                    }
                }
                break;
            }
        }

        // Build category arrays
        $choices = [];      // For ACF select field: key => label
        $name_to_key = [];  // For import: "Cow Preferred Systems" => "cow_preferred"
        $key_to_name = [];  // For export: "cow_preferred" => "Cow Preferred Systems"
        $empty_data = [];   // For export: "Cow Preferred Systems" => []

        foreach ($animal_types as $type) {
            $type_lower = strtolower(str_replace(' ', '_', $type));

            // Preferred
            $pref_key = $type_lower . '_preferred';
            $pref_name = $type . ' Preferred Systems';
            $choices[$pref_key] = $pref_name;
            $name_to_key[$pref_name] = $pref_key;
            $key_to_name[$pref_key] = $pref_name;
            $empty_data[$pref_name] = [];

            // Less Preferred
            $less_key = $type_lower . '_less_preferred';
            $less_name = $type . ' Less Preferred Systems';
            $choices[$less_key] = $less_name;
            $name_to_key[$less_name] = $less_key;
            $key_to_name[$less_key] = $less_name;
            $empty_data[$less_name] = [];
        }

        // Always add system type header
        $choices['system_type_header'] = 'Selected System Type Protocol Header';
        $name_to_key['Selected System Type Protocol Header'] = 'system_type_header';
        $key_to_name['system_type_header'] = 'Selected System Type Protocol Header';
        $empty_data['Selected System Type Protocol Header'] = [];

        return [
            'choices'     => $choices,
            'name_to_key' => $name_to_key,
            'key_to_name' => $key_to_name,
            'empty_data'  => $empty_data,
        ];
    }

    /**
     * Build Protocols.json structure from ACF data
     */
    private function get_protocols_data() {
        // Check if ACF is active
        if (!function_exists('get_field') || !function_exists('have_rows')) {
            return null;
        }

        $protocols = get_posts([
            'post_type'      => 'protocol',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if (empty($protocols)) {
            return null; // Fallback to bundled JSON
        }

        // Get parameters from ACF or use defaults
        $params_result = $this->get_parameters_data();
        if ($params_result) {
            $params = $params_result['values'];
            $params_meta = $params_result['meta'];
        } else {
            // Fallback defaults
            $params = [
                'Cow or Heifer' => ['Cow', 'Heifer'],
                'Breed Type'    => ['Bos Taurus', 'Bos Indicus'],
                'Semen Type'    => ['Conventional', 'Conventional & Sexed'],
                'System Type'   => ['Estrus AI', 'Estrus AI + Clean-up AI', 'Fixed-Time AI', 'Split Time AI'],
                'GnRH'          => ['Cystorelin', 'Factrel', 'Fertagyl', 'OvaCyst', 'GONAbreed', 'GnRH'],
                'PG'            => ['Estrumate', 'EstroPLAN', 'InSynch', 'Lutalyse', 'ProstaMate', 'Lutalyse HighCon', 'Synchsure', 'PG'],
            ];
            // Fallback metadata
            $params_meta = [
                'GnRH' => [
                    'Cystorelin' => ['dosage' => '2cc'],
                    'Factrel'    => ['dosage' => '2cc'],
                    'Fertagyl'   => ['dosage' => '2cc'],
                    'OvaCyst'    => ['dosage' => '2cc'],
                    'GONAbreed'  => ['dosage' => '1cc'],
                ],
                'PG' => [
                    'Estrumate'        => ['dosage' => '2cc'],
                    'EstroPLAN'        => ['dosage' => '2cc'],
                    'InSynch'          => ['dosage' => '5cc'],
                    'Lutalyse'         => ['dosage' => '5cc'],
                    'ProstaMate'       => ['dosage' => '5cc'],
                    'Lutalyse HighCon' => ['dosage' => '2cc'],
                    'Synchsure'        => ['dosage' => '2cc'],
                ],
                'System Type' => [
                    'Estrus AI'               => ['header' => 'Heat Detect & Breed'],
                    'Estrus AI + Clean-up AI' => ['header' => 'Heat Detect & Clean-up AI'],
                    'Fixed-Time AI'           => ['header' => 'Fixed-Time AI'],
                    'Split Time AI'           => ['header' => 'Split Time AI'],
                ],
            ];
        }

        $data = [
            'Parameters'     => [$params],
            'ParametersMeta' => $params_meta,
            'NotVerified'    => [],
            'Done'           => [],
            'Protocols'      => [[]],
        ];

        // Add placeholder protocol 0
        $data['Protocols'][0]['0'] = [
            'id' => 0,
            'SynchronizationSystemTitle' => 'Select a Protocol',
        ];

        foreach ($protocols as $protocol) {
            $id = get_field('protocol_id', $protocol->ID);

            // Build instructions array
            $instructions = [];
            if (have_rows('instructions', $protocol->ID)) {
                while (have_rows('instructions', $protocol->ID)) {
                    the_row();
                    $lines = [];
                    if (have_rows('lines')) {
                        while (have_rows('lines')) {
                            the_row();
                            $line_data = ['label' => get_sub_field('label')];
                            $hour_adj = get_sub_field('hour_adjustment');
                            if ($hour_adj !== '' && $hour_adj !== null) {
                                $line_data['hourAdjustment'] = (int) $hour_adj;
                            }
                            $lines[] = $line_data;
                        }
                    }
                    $instructions[] = [
                        'dateAdjustment'     => get_sub_field('date_adjustment'),
                        'dateAdjustmentUnit' => get_sub_field('date_adjustment_unit') ?: 'hours',
                        'isPg'               => (bool) get_sub_field('is_pg'),
                        'fromPg'             => (bool) get_sub_field('from_pg'),
                        'lines'              => $lines,
                    ];
                }
            }

            $protocol_data = [
                'id'                           => (int) $id,
                'SynchronizationSystemTitle'   => get_field('synchronization_system_title', $protocol->ID),
                'NumberOfPGFShotsPerFemale'    => $this->to_float(get_field('number_of_pgf_shots_per_female', $protocol->ID)),
                'NumberOfGnRHShotsPerFemale'   => $this->to_float(get_field('number_of_gnrh_shots_per_female', $protocol->ID)),
                'NumberOfCIDRsPerFemale'       => $this->to_float(get_field('number_of_cidrs_per_female', $protocol->ID)),
                'NumberOfDaysOnMGA'            => $this->to_float(get_field('number_of_days_on_mga', $protocol->ID)),
                'NumberOfDaysDrylot'           => $this->to_float(get_field('number_of_days_drylot', $protocol->ID)),
                'NumberOfDaysAI'               => $this->to_float(get_field('number_of_days_ai', $protocol->ID)),
                'NumberOfTechTrips'            => $this->to_float(get_field('number_of_tech_trips', $protocol->ID)),
                'NumberOfHeatChecks'           => $this->to_float(get_field('number_of_heat_checks', $protocol->ID)),
                'NumberOfChuteTimes'           => $this->to_float(get_field('number_of_chute_times', $protocol->ID)),
                'ResponseStartPercentage'      => $this->to_float(get_field('response_start_percentage', $protocol->ID)),
                'ConcpeptionStartPercentage'   => $this->to_float(get_field('conception_start_percentage', $protocol->ID)),
                'DaysWorkedForLaborCosts'      => $this->to_float(get_field('days_worked_for_labor_costs', $protocol->ID)),
                'DaysStartToEnd'               => $this->to_float(get_field('days_start_to_end', $protocol->ID)),
                'PGShot'                       => get_field('pg_shot', $protocol->ID) ?: '',
                'PullCIDR'                     => get_field('pull_cidr', $protocol->ID) ?: '',
                'GNRH'                         => get_field('gnrh', $protocol->ID) ?: '',
                'calendar'                     => [],
                'instructions'                 => $instructions,
            ];

            $data['Protocols'][0][(string) $id] = $protocol_data;

            // Track verified/done status
            if (get_field('not_verified', $protocol->ID)) {
                $data['NotVerified'][] = (int) $id;
            } else {
                $data['Done'][] = (int) $id;
            }
        }

        return $data;
    }

    /**
     * Build test-rules.json structure from ACF data
     */
    private function get_rules_data() {
        // Check if ACF is active
        if (!function_exists('get_field') || !function_exists('have_rows')) {
            return null;
        }

        $rules = get_posts([
            'post_type'      => 'selection_rule',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if (empty($rules)) {
            return null; // Fallback to bundled JSON
        }

        $animal_categories = $this->get_animal_type_categories();
        $category_map = $animal_categories['key_to_name'];
        $data = $animal_categories['empty_data'];

        foreach ($rules as $rule) {
            $category_key = get_field('rule_category', $rule->ID);
            $category_name = $category_map[$category_key] ?? null;

            if (!$category_name) continue;

            $priority = (string) get_field('priority_level', $rule->ID);
            $event_type = get_field('event_type', $rule->ID);

            // Build conditions array
            $conditions = [];
            if (have_rows('conditions', $rule->ID)) {
                while (have_rows('conditions', $rule->ID)) {
                    the_row();
                    $conditions[] = [
                        'fact'     => get_sub_field('fact'),
                        'operator' => get_sub_field('operator'),
                        'value'    => get_sub_field('value'),
                    ];
                }
            }

            // Initialize priority array if needed
            if (!isset($data[$category_name][$priority])) {
                $data[$category_name][$priority] = [];
            }

            $data[$category_name][$priority][] = [
                'conditions' => ['all' => $conditions],
                'event'      => ['type' => $event_type],
            ];
        }

        return $data;
    }

    // =========================================================================
    // FRONTEND - SCRIPTS AND SHORTCODE
    // =========================================================================

    /**
     * Enqueue scripts and inject ACF data
     */
    private function enqueue_scripts() {
        $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Config script
        wp_register_script('esp-config', '', [], null, false);
        wp_enqueue_script('esp-config');
        wp_add_inline_script('esp-config', "window.BEEF_APP_BASENAME = '{$current_path}';");
        wp_add_inline_script('esp-config', "window.BEEF_APP_SHOW_HEADER = false;");
        wp_add_inline_script('esp-config', "window.BEEF_APP_SHOW_FOOTER = false;");
        wp_add_inline_script('esp-config', "window.BEEF_APP_SHOW_NAVBAR = false;");
        wp_add_inline_script('esp-config', "window.BEEF_APP_SHOW_PAGE_TITLE = false;");

        // ========== INJECT ACF DATA ==========
        $protocols_data = $this->get_protocols_data();
        $rules_data = $this->get_rules_data();

        if ($protocols_data) {
            wp_add_inline_script(
                'esp-config',
                'window.BEEF_APP_PROTOCOLS = ' . json_encode($protocols_data, JSON_UNESCAPED_SLASHES) . ';'
            );
        }

        if ($rules_data) {
            wp_add_inline_script(
                'esp-config',
                'window.BEEF_APP_RULES = ' . json_encode($rules_data, JSON_UNESCAPED_SLASHES) . ';'
            );
        }
        // =====================================

        // Manifest
        $manifest_url = 'https://beef-repro-task-force.github.io/Estrus-Synchronization-Planner-Staging/asset-manifest.json';
        $response = wp_remote_get($manifest_url);
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $manifest = json_decode($body, true);

            if (isset($manifest['files']['main.css'])) {
                wp_enqueue_style(
                    'esp-style',
                    'https://beef-repro-task-force.github.io' . $manifest['files']['main.css']
                );
            }

            if (isset($manifest['files']['main.js'])) {
                wp_enqueue_script(
                    'esp-script',
                    'https://beef-repro-task-force.github.io' . $manifest['files']['main.js'],
                    ['esp-config'],
                    null,
                    true
                );

                // Mount app
                wp_add_inline_script(
                    'esp-script',
                    "function mountBeefApp() {
                        if (document.getElementById('root') && typeof window.renderBeefApp === 'function') {
                            window.renderBeefApp();
                        }
                    }
                    document.addEventListener('DOMContentLoaded', mountBeefApp);"
                );

                // Capture errors
                wp_add_inline_script(
                    'esp-script',
                    "window.onerror = function(msg, url, line, col, error) {
                        console.error('React error:', msg, 'at', url+':'+line+':'+col, error);
                    };"
                );
            }
        }
    }

    /**
     * Render Shortcode
     */
    public function render_shortcode() {
        // Enqueue scripts *only when shortcode is used*
        $this->enqueue_scripts();

        return '<div id="root"></div>
        <script>
            if (typeof mountBeefApp === "function") mountBeefApp();
        </script>';
    }

    // =========================================================================
    // ACTIVATION — BACKUP DEFAULT FILES
    // =========================================================================

    /**
     * On plugin activation, create backup copies of the original data files.
     * These backups are only created once and never overwritten, so they
     * always represent the factory defaults for "Reset to Defaults".
     */
    public static function activate() {
        $data_dir    = plugin_dir_path(__FILE__) . 'data/';
        $backups_dir = $data_dir . 'backups/';
        $saved_dir   = $data_dir . 'saved/';

        // Create directories if they don't exist
        if (!is_dir($backups_dir)) {
            wp_mkdir_p($backups_dir);
        }
        if (!is_dir($saved_dir)) {
            wp_mkdir_p($saved_dir);
        }

        // Backup default files (only if backup doesn't already exist)
        $files_to_backup = [
            'Protocols.json',
            'test-rules.json',
        ];

        foreach ($files_to_backup as $file) {
            $source = $data_dir . $file;
            $dest   = $backups_dir . $file;
            if (file_exists($source) && !file_exists($dest)) {
                copy($source, $dest);
            }
        }
    }

    // =========================================================================
    // SAVE / EXPORT ACF DATA TO FILES
    // =========================================================================

    /**
     * Get the saved data directory path, creating it if needed.
     */
    private function get_saved_dir() {
        $dir = plugin_dir_path(__FILE__) . 'data/saved/';
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    /**
     * Get the backups directory path.
     */
    private function get_backups_dir() {
        return plugin_dir_path(__FILE__) . 'data/backups/';
    }

    /**
     * Handle Save Protocols — export current ACF protocols to saved file
     */
    public function handle_save_protocols() {
        if (!isset($_POST['do_save_protocols'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['save_protocols_nonce'], 'save_protocols')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $data = $this->get_protocols_data();
        if (!$data) {
            wp_die('No protocol data found to save.');
        }
        $path = $this->get_saved_dir() . 'protocols.json';
        $result = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($result === false) {
            wp_die('Failed to write file. Check directory permissions for: ' . esc_html(dirname($path)));
        }

        wp_redirect(add_query_arg([
            'page'  => 'esp-config',
            'saved' => 1,
            'type'  => 'protocols',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Save Rules — export current ACF rules to saved file
     */
    public function handle_save_rules() {
        if (!isset($_POST['do_save_rules'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['save_rules_nonce'], 'save_rules')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $data = $this->get_rules_data();
        if (!$data) {
            wp_die('No rules data found to save.');
        }
        $path = $this->get_saved_dir() . 'rules.json';
        $result = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($result === false) {
            wp_die('Failed to write file. Check directory permissions for: ' . esc_html(dirname($path)));
        }

        wp_redirect(add_query_arg([
            'page'  => 'esp-config',
            'saved' => 1,
            'type'  => 'rules',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Save Parameters — export current ACF parameters to saved file
     * Saves in the same Protocols.json structure so import_parameters() can read it.
     */
    public function handle_save_parameters() {
        if (!isset($_POST['do_save_parameters'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['save_parameters_nonce'], 'save_parameters')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $params_result = $this->get_parameters_data();
        if (!$params_result) {
            wp_die('No parameter data found to save.');
        }
        // Wrap in Protocols.json-compatible structure so import_parameters() works as-is
        $data = [
            'Parameters' => [$params_result['values']],
            'ParametersMeta' => $params_result['meta'],
        ];
        $path = $this->get_saved_dir() . 'parameters.json';
        $result = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($result === false) {
            wp_die('Failed to write file. Check directory permissions for: ' . esc_html(dirname($path)));
        }

        wp_redirect(add_query_arg([
            'page'  => 'esp-config',
            'saved' => 1,
            'type'  => 'user input options',
        ], admin_url('admin.php')));
        exit;
    }

    // =========================================================================
    // RESET TO DEFAULTS — RE-IMPORT FROM BACKUP FILES
    // =========================================================================

    /**
     * Handle Reset Protocols — re-import from backup files
     */
    public function handle_reset_protocols() {
        if (!isset($_POST['do_reset_protocols'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['reset_protocols_nonce'], 'reset_protocols')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        // Try backup first, fall back to original shipped file
        $backup_path = $this->get_backups_dir() . 'Protocols.json';
        if (!file_exists($backup_path)) {
            $backup_path = $this->find_json_file('Protocols.json');
        }
        if (!$backup_path || !file_exists($backup_path)) {
            wp_die('No default Protocols.json file found. Cannot reset.');
        }
        $this->clear_post_type('protocol');
        $count = $this->import_protocols($backup_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'protocols (reset to defaults)',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Reset Rules — re-import from backup files
     */
    public function handle_reset_rules() {
        if (!isset($_POST['do_reset_rules'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['reset_rules_nonce'], 'reset_rules')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        // Try backup first, fall back to original shipped file
        $backup_path = $this->get_backups_dir() . 'test-rules.json';
        if (!file_exists($backup_path)) {
            $backup_path = $this->find_json_file('test-rules.json');
        }
        if (!$backup_path || !file_exists($backup_path)) {
            wp_die('No default test-rules.json file found. Cannot reset.');
        }
        $this->clear_post_type('selection_rule');
        $count = $this->import_rules($backup_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'rules (reset to defaults)',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Reset Parameters — re-import from backup files
     */
    public function handle_reset_parameters() {
        if (!isset($_POST['do_reset_parameters'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['reset_parameters_nonce'], 'reset_parameters')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') || !$this->is_acf_active()) {
            wp_die('Unauthorized');
        }

        // Try backup first, fall back to original shipped file
        $backup_path = $this->get_backups_dir() . 'Protocols.json';
        if (!file_exists($backup_path)) {
            $backup_path = $this->find_json_file('Protocols.json');
        }
        if (!$backup_path || !file_exists($backup_path)) {
            wp_die('No default Protocols.json file found. Cannot reset.');
        }
        $this->clear_post_type('app_parameter');
        $count = $this->import_parameters($backup_path);

        wp_redirect(add_query_arg([
            'page'     => 'esp-config',
            'imported' => 1,
            'count'    => $count,
            'type'     => 'user input options (reset to defaults)',
        ], admin_url('admin.php')));
        exit;
    }
}

// Activation hook — create backup copies of default data files
register_activation_hook(__FILE__, ['Estrus_Synchronization_Planner', 'activate']);

// Initialize
Estrus_Synchronization_Planner::get_instance();
