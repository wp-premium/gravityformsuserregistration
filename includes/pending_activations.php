<?php

class GFUserPendingActiviations {
    
    static $message;
    static $errors;
    
    public static function display_page() {
        ?>
        
        <style type="text/css">
            .nav-tab-wrapper { margin: 0 0 10px !important; }
            .message { margin: 5px 0 15px; padding: 0.5em 0.6em; border: 1px solid #E6DB55; }
            .message.updated { background-color: #FFFFE0;  }
            .message.error { background-color: #FFEBE8; border-color: #CC0000; }
            .gfur-form-select { float: right; margin-top: -30px; }
        </style>
        
        <div class="wrap">

            <?php $form = rgget('form_id') ? RGFormsModel::get_form( rgget('form_id') ) : false; ?>

            <div style="background: url('<?php echo GFUser::get_base_url() ?>/images/user-registration-icon-32.png') no-repeat;" id="icon-edit" class="icon32 icon32-posts-post"><br></div>
            <h2>
                <?php if( !$form ) {
                    _e("Pending Activations", "gravityformsuserregistration");
                } else {
                    $form_link = '"<a href="' . admin_url( 'admin.php?page=gf_edit_forms&id=' . $form->id ) . '">' . $form->title . '</a>"';
                    printf( __('Pending Activations for %s'), $form_link);
                } ?>
            </h2>

            <div class="gfur-form-select">

                <?php
                $pending_activation_forms = GFUser::get_pending_activation_forms();
                $pending_activation_url = admin_url('admin.php?page=gf_user_registration&view=pending_activations&form_id=');
                ?>
                <select onchange="document.location = '<?php echo $pending_activation_url; ?>' + this.value;">
                    <option value=""><?php _e('Select a Form', 'gravityformsuserregistration'); ?></option>
                    <option value="all"><?php _e('View All Pending Activations', 'gravityformsuserregistration'); ?>
                    <optgroup label="<?php _e('Forms', 'gravityformsuserregistration'); ?>">
                        <?php foreach( $pending_activation_forms as $form_obj ) { ?>
                            <option value="<?php echo $form_obj->id; ?>"><?php echo $form_obj->title; ?></option>
                        <?php } ?>
                    </optgroup></option>
                </select>

            </div>

            <?php
            
            if(rgpost('is_submit')) {
                self::handle_submission();
                if(self::$errors) { ?>
                    <div class="message error"><p><?php echo self::$errors; ?></p></div>
                <?php } else { ?>
                    <div class="message updated"><p><?php echo self::$message; ?></p></div>
                <?php }
            }
            
            ?>
            
            <form id="list_form" method="post" action="">
            
                <?php
                $table = new GFUserPendingActivationsList();
                $table->prepare_items();
                $table->display();
                ?>
                
                <input type="hidden" name="is_submit" value="1" />
                <input type="hidden" id="single_action" name="single_action" value="" />
                <input type="hidden" id="item" name="item" value="" />
                
                <?php wp_nonce_field('action', 'action_nonce'); ?>
                
            </form>
            
        </div>
        
        <script type="text/javascript">
        
        function singleItemAction(action, activationKey) {
            jQuery('#item').val(activationKey);
            jQuery('#single_action').val(action);
            jQuery('#list_form')[0].submit();
        }
        
        </script>
        
        <?php
    }
    
    public static function get_pending_activations($form_id, $args = array()) {
        global $wpdb;
        
        if ($form_id == "all")
        	$form_id = "";
        
        extract(wp_parse_args($args, array(
            'order' => 'DESC',
            'order_by' => 'registered',
            'page' => 1,
            'per_page' => 10,
            'get_total' => false,
            'lead_id' => false
        )));
        
        if(!is_multisite()) {
            require_once(GFUser::get_base_path() . '/includes/signups.php');
            GFUserSignups::prep_signups_functionality();
        }
        
        $where = array();
        
        if($form_id)
            $where[] = $wpdb->prepare("l.form_id = %d", $form_id);
        
        if($lead_id)
            $where[] = $wpdb->prepare("l.id = %d", $lead_id);
        
        $where[] = "s.active = 0";
        $where = 'WHERE ' . implode(' AND ', $where);
        
        $order = "ORDER BY {$order_by} {$order}";
        $offset = ($page * $per_page) - $per_page;
        $limit_offset = $get_total ? '' : "LIMIT $per_page OFFSET $offset";
        $method = $get_total ? 'get_var' : 'get_results';
        
        if($form_id) {
            
            $select = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*' ;
            $sql = "
                $select FROM {$wpdb->prefix}rg_lead_meta lm
                INNER JOIN {$wpdb->signups} s ON s.activation_key = lm.meta_value AND lm.meta_key = 'activation_key'
                INNER JOIN {$wpdb->prefix}rg_lead l ON l.id = lm.lead_id
                $where
                $order
                $limit_offset";
            $results = $wpdb->$method($sql);
                
        } else {
            
            $select = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*' ;
            $results = $wpdb->$method("
                $select FROM $wpdb->signups s
                $where
                $order
                $limit_offset"
                );
            
        }
        
        return $results;
    }
    
    private static function process_bulk_action() {
        
    }
    
    public static function handle_submission() {
        
        if(!wp_verify_nonce(rgpost('action_nonce'), 'action') && !check_admin_referer('action_nonce', 'action_nonce'))
            die('You have failed...');
        
        require_once(GFUser::get_base_path() . '/includes/signups.php');
        GFUserSignups::prep_signups_functionality();
        
        self::$errors = '';
        self::$message = '';
        
        $action = rgpost('single_action');
        $action = !$action ? rgpost('action') != -1 ? rgpost('action') : rgpost('action2') : $action;
        
        $items = rgpost('item') ? array(rgpost('item')) : rgpost('items');
        
        foreach($items as $key) {
        
            switch($action) {
            case 'delete':
                $success = GFUserSignups::delete_signup($key);
                if($success) {
                    self::$message = _n('Item deleted.', 'Items deleted.', count($items), 'graivtyformsuserregistration');
                } else {
                    self::$errors = _n('There was an issue deleting this item.', 'There was an issue deleting one or more selected items.', count($items), 'graivtyformsuserregistration');
                }
                break;
            case 'activate':
                $userdata = GFUserSignups::activate_signup($key);
                if(!is_wp_error($userdata) && rgar($userdata, 'user_id')) {
                    self::$message = _n('Item activated.', 'Items activated.', count($items), 'graivtyformsuserregistration');
                } else {
                    self::$errors = _n('There was an issue activating this item', 'There was an issue activating one or more selected items', count($items), 'graivtyformsuserregistration');
                    if(is_wp_error($userdata)) {
                        $errors = reset($userdata->errors);
                        self::$errors .= ": " . $errors[0];
                    }
                }
                break;
            }
        
        }
        
    }
    
}

require_once(ABSPATH . '/wp-admin/includes/class-wp-list-table.php');

class GFUserPendingActivationsList extends WP_List_Table {
    
    var $_column_headers;
    
    function __construct() {
        
        $this->items = array();
        $this->_column_headers = array(
            array(
                'cb' => '<input type="checkbox" />',
                'form' => 'Form',
                'user_login' => 'Username',
                'email' => 'Email',
                'date' => 'Sign Up Date',
                ),
            array(),
            array()
        );
        
        parent::__construct();
        
    }
    
    function prepare_items() {
        
        $items = array();
        $forms = array();
        $per_page = 10;
        $page = rgget('paged') ? rgget('paged') : 1;
        $pending_activations = GFUserPendingActiviations::get_pending_activations(rgget('form_id'), array('per_page' => $per_page, 'page' => $page));
        $total_pending = GFUserPendingActiviations::get_pending_activations(rgget('form_id'), array('per_page' => $per_page, 'page' => $page, 'get_total' => true));
        
        foreach($pending_activations as $pending_activation) {
            $signup_meta = unserialize($pending_activation->meta);
            
            $lead = RGFormsModel::get_lead(rgar($signup_meta, 'lead_id'));
            
            //if(!$lead)
                //continue;
            
            $form_id = $lead['form_id'];
            $form = rgar($forms, $form_id) ? rgar($forms, $form_id) : RGFormsModel::get_form_meta($form_id);
            $forms[$form_id] = $form;
            
            $item = array();
            $item['form'] = $form['title'];
            $item['user_login'] = rgar($signup_meta, 'user_login');
            $item['email'] = rgar($signup_meta, 'email');
            $item['date'] = $lead['date_created'];
            
            // non-columns
            $item['lead_id'] = $lead['id'];
            $item['form_id'] = $form_id;
            $item['activation_key'] = $pending_activation->activation_key;
            
            array_push($this->items, $item);
               
        }
        
        $this->set_pagination_args(array(
            'total_items' => $total_pending,
            'per_page' => $per_page,
        ));
        
    }
    
    function column_default($item, $column_name) {
        return rgar($item, $column_name);
    }
    
    function column_cb($item) {
        return '<input type="checkbox" name="items[]" value="' . $item['activation_key'] . '" />';
    }
    
    function column_form($item) {
        $str = '<strong>' . rgar($item, 'form') . '</strong>';
        $str .= '
            <div class="row-actions">
                <span class="inline hide-if-no-js">
                    <a title="Activate this sign up" href="javascript: if(confirm(\'' . __('Activate this sign up? ', 'gravityformsuserregistration') . __("\'Cancel\' to stop, \'OK\' to activate.", "gravityformsuserregistration") . '\')) { singleItemAction(\'activate\',\'' . $item['activation_key'] . '\'); } ">Activate</a> | 
                </span>
                <span class="inline hide-if-no-js">
                    <a title="View the entry associated with this sign up" href="' . admin_url("admin.php?page=gf_entries&view=entry&id={$item['form_id']}&lid={$item['lead_id']}") . '">View Entry</a> | 
                </span>
                <span class="inline hide-if-no-js">
                    <a title="Delete this sign up?" href="javascript: if(confirm(\'' . __('Delete this sign up? ', 'gravityformsuserregistration') . __("\'Cancel\' to stop, \'OK\' to delete.", "gravityformsuserregistration") . '\')) { singleItemAction(\'delete\',\'' . $item['activation_key'] . '\'); } ">Delete</a>
                </span>
            </div>';
        
        return $str;
    }
    
    function column_date($item) {
        return GFCommon::format_date(rgar($item, 'date'), false);
    } 
    
    function get_bulk_actions() {
        $actions = array(
            'activate' => __('Activate', 'gravityformsuserregistration'),
            'delete' => __('Delete', 'gravityformsuserregistration')
            );

        return $actions;
    }
    
}

?>
