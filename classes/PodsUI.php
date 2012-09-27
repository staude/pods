<?php
/**
 * @package Pods
 */
class PodsUI {

    // internal
    /**
     * @var bool|PodsData
     */
    private $pods_data = false;

    /**
     * @var array
     */
    private $actions = array(
        'manage',
        'add',
        'edit',
        'duplicate',
        'save',
        'view',
        'delete',
        'reorder',
        'export'
    );

    /**
     * @var array
     */
    private $ui_page = array();

    /**
     * @var bool
     */
    private $unique_identifier = false;

    // base
    public $x = array();

    /**
     * @var array|bool|mixed|null|Pods
     */
    public $pod = false;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $num = ''; // allows multiple co-existing PodsUI instances with separate functionality in URL
    /**
     * @var array
     */
    static $excluded = array(
        'do',
        'id',
        'pg',
        'search',
        'orderby',
        'orderby_dir',
        'limit',
        'action',
        'export',
        'export_type',
        'export_delimiter',
        'remove_export',
        'updated',
        'duplicate'
    ); // used in var_update

    // ui
    /**
     * @var bool
     */
    public $item = false; // to be set with localized string
    /**
     * @var bool
     */
    public $items = false; // to be set with localized string
    /**
     * @var bool
     */
    public $heading = false; // to be set with localized string array
    /**
     * @var bool
     */
    public $label = false; // to be set with localized string array
    /**
     * @var bool
     */
    public $icon = false;

    /**
     * @var bool
     */
    public $css = false; // set to a URL of stylesheet to include
    /**
     * @var bool
     */
    public $wpcss = false; // set to true to include WP Admin stylesheets
    /**
     * @var array
     */
    public $fields = array(
        'manage' => array(),
        'search' => array(),
        'form' => array(),
        'add' => array(),
        'edit' => array(),
        'duplicate' => array(),
        'view' => array(),
        'reorder' => array(),
        'export' => array()
    );

    /**
     * @var bool
     */
    public $searchable = true;

    /**
     * @var bool
     */
    public $sortable = true;

    /**
     * @var bool
     */
    public $pagination = true;

    /**
     * @var bool
     */
    public $pagination_total = true;

    /**
     * @var array
     */
    public $export = array(
        'on' => false,
        'formats' => array(
            'csv' => ',',
            'tsv' => "\t",
            'xml' => false,
            'json' => false
        ),
        'url' => false,
        'type' => false
    );

    /**
     * @var array
     */
    public $reorder = array(
        'on' => false,
        'limit' => 250,
        'orderby' => false,
        'orderby_dir' => 'ASC',
        'sql' => null
    );

    /**
     * @var array
     */
    public $screen_options = array(); // set to 'page' => 'Text'; false hides link
    /**
     * @var array
     */
    public $help = array(); // set to 'page' => 'Text'; 'page' => array('link' => 'yourhelplink'); false hides link

    // data
    /**
     * @var bool
     */
    public $search = false;

    /**
     * @var array
     */
    public $filters = array();

    /**
     * @var bool
     */
    public $search_across = true;

    /**
     * @var bool
     */
    public $search_across_picks = false;

    /**
     * @var bool
     */
    public $default_none = false;

    /**
     * @var array
     */
    public $where = array(
        'manage' => null,
        'edit' => null,
        'duplicate' => null,
        'delete' => null,
        'reorder' => null
    );

    /**
     * @var bool
     */
    public $orderby = false;

    /**
     * @var string
     */
    public $orderby_dir = 'DESC';

    /**
     * @var int
     */
    public $limit = 25;

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var int
     */
    public $total_found = 0;

    /**
     * @var array
     */
    public $session = array(
        'search',
        'filters'
    ); // allowed: search, filters, show_per_page, orderby (priority over usermeta)
    /**
     * @var array
     */
    public $user = array(
        'show_per_page',
        'orderby'
    ); // allowed: search, filters, show_per_page, orderby (priority under session)

    // advanced data
    /**
     * @var array
     */
    public $sql = array(
        'table' => null,
        'field_id' => 'id',
        'field_index' => 'name',
        'select' => null,
        'sql' => null
    );

    /**
     * @var bool|array
     */
    public $data = false;

    /**
     * @var array
     */
    public $data_keys = array();

    /**
     * @var array
     */
    public $row = array();

    // actions
    /**
     * @var string
     */
    public $action = 'manage';

    /**
     * @var array
     */
    public $action_after = array(
        'add' => 'edit',
        'edit' => 'edit',
        'duplicate' => 'edit'
    ); // set action to 'manage'
    /**
     * @var bool
     */
    public $do = false;

    /**
     * @var array
     */
    public $action_links = array(
        'add' => null,
        'edit' => null,
        'duplicate' => null,
        'view' => null,
        'delete' => null,
        'reorder' => null,
        'export' => null
    ); // custom links (callable allowed)
    /**
     * @var array
     */
    public $actions_disabled = array(
        'view',
        'export'
    ); // disable actions
    /**
     * @var array
     */
    public $actions_hidden = array(); // hide actions to not show them but allow them
    /**
     * @var array
     */
    public $actions_custom = array(); // overwrite existing actions or add your own

    /**
     * @var bool
     */
    public $save = false; // Allow custom save handling for tables that aren't Pod-based

    /**
     * Generate UI for Data Management
     *
     * @param mixed $options Object, Array, or String containing Pod or Options to be used
     * @param bool $deprecated Set to true to support old options array from Pods UI plugin
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct ( $options, $deprecated = false ) {
        $object = null;

        if ( is_object( $options ) ) {
            $object = $options;
            $options = array();

            if ( isset( $object->ui ) ) {
                $options = $object->ui;

                unset( $object->ui );
            }

            if ( is_object( $object ) && ( 'Pods' == get_class( $object ) || 'Pod' == get_class( $object ) ) )
                $this->pod = &$object;
        }

        if ( !is_array( $options ) ) {
            parse_str( $options, $options );
            // need to come back to this and allow for multi-dimensional strings
            // like: option=value&option2=value2&option3=key[val],key2[val2]&option4=this,that,another
        }

        if ( !is_object( $object ) && isset( $options[ 'pod' ] ) ) {
            if ( is_object( $options[ 'pod' ] ) )
                $this->pod = $options[ 'pod' ];
            elseif ( isset( $options[ 'id' ] ) )
                $this->pod =& pods( $options[ 'pod' ], $options[ 'id' ] );
            else
                $this->pod =& pods( $options[ 'pod' ] );

            unset( $options[ 'pod' ] );
        }
        elseif ( is_object( $object ) )
            $this->pod = $object;

        if ( false !== $deprecated || ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) ) )
            $options = $this->setup_deprecated( $options );

        if ( is_object( $this->pod ) && 'Pod' == get_class( $this->pod ) && is_object( $this->pod->_data ) )
            $this->pods_data =& $this->pod->_data;
        elseif ( is_object( $this->pod ) && 'Pods' == get_class( $this->pod ) && is_object( $this->pod->data ) )
            $this->pods_data =& $this->pod->data;
        elseif ( is_object( $this->pod ) )
            $this->pods_data =& pods_data( $this->pod->pod );
        elseif ( !is_object( $this->pod ) )
            $this->pods_data =& pods_data( $this->pod );

        $options = $this->do_hook( 'pre_init', $options );
        $this->setup( $options );

        if ( is_object( $this->pods_data ) && is_object( $this->pod ) && 0 < $this->id && $this->id != $this->pods_data->id )
            $this->pods_data->fetch( $this->id );

        if ( ( !is_object( $this->pod ) || 'Pods' != get_class( $this->pod ) ) && false === $this->sql[ 'table' ] && false === $this->data ) {
            echo $this->error( __( '<strong>Error:</strong> Pods UI needs a Pods object or a Table definition to run from, see the User Guide for more information.', 'pods' ) );

            return false;
        }

        $this->go();
    }

    /**
     * @param $deprecated_options
     *
     * @return array
     */
    public function setup_deprecated ( $deprecated_options ) {
        $options = array();
        if ( isset( $deprecated_options[ 'id' ] ) )
            $options[ 'id' ] = $deprecated_options[ 'id' ];
        if ( isset( $deprecated_options[ 'action' ] ) )
            $options[ 'action' ] = $deprecated_options[ 'action' ];
        if ( isset( $deprecated_options[ 'num' ] ) )
            $options[ 'num' ] = $deprecated_options[ 'num' ];

        if ( isset( $deprecated_options[ 'title' ] ) )
            $options[ 'items' ] = $deprecated_options[ 'title' ];
        if ( isset( $deprecated_options[ 'item' ] ) )
            $options[ 'item' ] = $deprecated_options[ 'item' ];

        if ( isset( $deprecated_options[ 'label' ] ) )
            $options[ 'label' ] = array(
                'add' => $deprecated_options[ 'label' ],
                'edit' => $deprecated_options[ 'label' ],
                'duplicate' => $deprecated_options[ 'label' ]
            );
        if ( isset( $deprecated_options[ 'label_add' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'add' ] = $deprecated_options[ 'label_add' ];
            else
                $options[ 'label' ] = array( 'add' => $deprecated_options[ 'label_add' ] );
        }
        if ( isset( $deprecated_options[ 'label_edit' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'edit' ] = $deprecated_options[ 'label_edit' ];
            else
                $options[ 'label' ] = array( 'edit' => $deprecated_options[ 'label_edit' ] );
        }
        if ( isset( $deprecated_options[ 'label_duplicate' ] ) ) {
            if ( isset( $options[ 'label' ] ) )
                $options[ 'label' ][ 'duplicate' ] = $deprecated_options[ 'label_duplicate' ];
            else
                $options[ 'label' ] = array( 'duplicate' => $deprecated_options[ 'label_duplicate' ] );
        }

        if ( isset( $deprecated_options[ 'icon' ] ) )
            $options[ 'icon' ] = $deprecated_options[ 'icon' ];

        if ( isset( $deprecated_options[ 'columns' ] ) )
            $options[ 'fields' ] = array( 'manage' => $deprecated_options[ 'columns' ] );
        if ( isset( $deprecated_options[ 'reorder_columns' ] ) ) {
            if ( isset( $options[ 'fields' ] ) )
                $options[ 'fields' ][ 'reorder' ] = $deprecated_options[ 'reorder_columns' ];
            else
                $options[ 'fields' ] = array( 'reorder' => $deprecated_options[ 'reorder_columns' ] );
        }
        if ( isset( $deprecated_options[ 'add_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) ) {
                if ( !isset( $options[ 'fields' ][ 'add' ] ) )
                    $options[ 'fields' ][ 'add' ] = $deprecated_options[ 'add_fields' ];
                if ( !isset( $options[ 'fields' ][ 'edit' ] ) )
                    $options[ 'fields' ][ 'edit' ] = $deprecated_options[ 'add_fields' ];
                if ( !isset( $options[ 'fields' ][ 'duplicate' ] ) )
                    $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'add_fields' ];
            }
            else
                $options[ 'fields' ] = array(
                    'add' => $deprecated_options[ 'add_fields' ],
                    'edit' => $deprecated_options[ 'add_fields' ],
                    'duplicate' => $deprecated_options[ 'add_fields' ]
                );
        }
        if ( isset( $deprecated_options[ 'edit_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) ) {
                if ( !isset( $options[ 'fields' ][ 'add' ] ) )
                    $options[ 'fields' ][ 'add' ] = $deprecated_options[ 'edit_fields' ];
                if ( !isset( $options[ 'fields' ][ 'edit' ] ) )
                    $options[ 'fields' ][ 'edit' ] = $deprecated_options[ 'edit_fields' ];
                if ( !isset( $options[ 'fields' ][ 'duplicate' ] ) )
                    $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'edit_fields' ];
            }
            else
                $options[ 'fields' ] = array(
                    'add' => $deprecated_options[ 'edit_fields' ],
                    'edit' => $deprecated_options[ 'edit_fields' ],
                    'duplicate' => $deprecated_options[ 'edit_fields' ]
                );
        }
        if ( isset( $deprecated_options[ 'duplicate_fields' ] ) ) {
            if ( isset( $options[ 'fields' ] ) )
                $options[ 'fields' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_fields' ];
            else
                $options[ 'fields' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_fields' ] );
        }

        if ( isset( $deprecated_options[ 'session_filters' ] ) && false === $deprecated_options[ 'session_filters' ] )
            $options[ 'session' ] = false;
        if ( isset( $deprecated_options[ 'user_per_page' ] ) ) {
            if ( isset( $options[ 'user' ] ) && !empty( $options[ 'user' ] ) )
                $options[ 'user' ] = array( 'orderby' );
            else
                $options[ 'user' ] = false;
        }
        if ( isset( $deprecated_options[ 'user_sort' ] ) ) {
            if ( isset( $options[ 'user' ] ) && !empty( $options[ 'user' ] ) )
                $options[ 'user' ] = array( 'show_per_page' );
            else
                $options[ 'user' ] = false;
        }

        if ( isset( $deprecated_options[ 'custom_list' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'manage' ] = $deprecated_options[ 'custom_list' ];
            else
                $options[ 'actions_custom' ] = array( 'manage' => $deprecated_options[ 'custom_list' ] );
        }
        if ( isset( $deprecated_options[ 'custom_reorder' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'reorder' ] = $deprecated_options[ 'custom_reorder' ];
            else
                $options[ 'actions_custom' ] = array( 'reorder' => $deprecated_options[ 'custom_reorder' ] );
        }
        if ( isset( $deprecated_options[ 'custom_add' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'add' ] = $deprecated_options[ 'custom_add' ];
            else
                $options[ 'actions_custom' ] = array( 'add' => $deprecated_options[ 'custom_add' ] );
        }
        if ( isset( $deprecated_options[ 'custom_edit' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'edit' ] = $deprecated_options[ 'custom_edit' ];
            else
                $options[ 'actions_custom' ] = array( 'edit' => $deprecated_options[ 'custom_edit' ] );
        }
        if ( isset( $deprecated_options[ 'custom_duplicate' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'duplicate' ] = $deprecated_options[ 'custom_duplicate' ];
            else
                $options[ 'actions_custom' ] = array( 'duplicate' => $deprecated_options[ 'custom_duplicate' ] );
        }
        if ( isset( $deprecated_options[ 'custom_delete' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'delete' ] = $deprecated_options[ 'custom_delete' ];
            else
                $options[ 'actions_custom' ] = array( 'delete' => $deprecated_options[ 'custom_delete' ] );
        }
        if ( isset( $deprecated_options[ 'custom_save' ] ) ) {
            if ( isset( $options[ 'actions_custom' ] ) )
                $options[ 'actions_custom' ][ 'save' ] = $deprecated_options[ 'custom_save' ];
            else
                $options[ 'actions_custom' ] = array( 'save' => $deprecated_options[ 'custom_save' ] );
        }

        if ( isset( $deprecated_options[ 'custom_actions' ] ) )
            $options[ 'actions_custom' ] = $deprecated_options[ 'custom_actions' ];
        if ( isset( $deprecated_options[ 'action_after_save' ] ) )
            $options[ 'action_after' ] = array(
                'add' => $deprecated_options[ 'action_after_save' ],
                'edit' => $deprecated_options[ 'action_after_save' ],
                'duplicate' => $deprecated_options[ 'action_after_save' ]
            );
        if ( isset( $deprecated_options[ 'edit_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'edit' ] = $deprecated_options[ 'edit_link' ];
            else
                $options[ 'action_links' ] = array( 'edit' => $deprecated_options[ 'edit_link' ] );
        }
        if ( isset( $deprecated_options[ 'view_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'view' ] = $deprecated_options[ 'view_link' ];
            else
                $options[ 'action_links' ] = array( 'view' => $deprecated_options[ 'view_link' ] );
        }
        if ( isset( $deprecated_options[ 'duplicate_link' ] ) ) {
            if ( isset( $options[ 'action_links' ] ) )
                $options[ 'action_links' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_link' ];
            else
                $options[ 'action_links' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_link' ] );
        }

        if ( isset( $deprecated_options[ 'reorder' ] ) )
            $options[ 'reorder' ] = array(
                'on' => $deprecated_options[ 'reorder' ],
                'orderby' => $deprecated_options[ 'reorder' ]
            );
        if ( isset( $deprecated_options[ 'reorder_sort' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'orderby' ] = $deprecated_options[ 'reorder_sort' ];
        if ( isset( $deprecated_options[ 'reorder_limit' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'limit' ] = $deprecated_options[ 'reorder_limit' ];
        if ( isset( $deprecated_options[ 'reorder_sql' ] ) && isset( $options[ 'reorder' ] ) )
            $options[ 'reorder' ][ 'sql' ] = $deprecated_options[ 'reorder_sql' ];

        if ( isset( $deprecated_options[ 'sort' ] ) )
            $options[ 'orderby' ] = $deprecated_options[ 'sort' ];
        if ( isset( $deprecated_options[ 'sortable' ] ) )
            $options[ 'sortable' ] = $deprecated_options[ 'sortable' ];
        if ( isset( $deprecated_options[ 'limit' ] ) )
            $options[ 'limit' ] = $deprecated_options[ 'limit' ];

        if ( isset( $deprecated_options[ 'where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'manage' ] = $deprecated_options[ 'where' ];
            else
                $options[ 'where' ] = array( 'manage' => $deprecated_options[ 'where' ] );
        }
        if ( isset( $deprecated_options[ 'edit_where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'edit' ] = $deprecated_options[ 'edit_where' ];
            else
                $options[ 'where' ] = array( 'edit' => $deprecated_options[ 'edit_where' ] );
        }
        if ( isset( $deprecated_options[ 'duplicate_where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'duplicate' ] = $deprecated_options[ 'duplicate_where' ];
            else
                $options[ 'where' ] = array( 'duplicate' => $deprecated_options[ 'duplicate_where' ] );
        }
        if ( isset( $deprecated_options[ 'delete_where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'delete' ] = $deprecated_options[ 'delete_where' ];
            else
                $options[ 'where' ] = array( 'delete' => $deprecated_options[ 'delete_where' ] );
        }
        if ( isset( $deprecated_options[ 'reorder_where' ] ) ) {
            if ( isset( $options[ 'where' ] ) )
                $options[ 'where' ][ 'reorder' ] = $deprecated_options[ 'reorder_where' ];
            else
                $options[ 'where' ] = array( 'reorder' => $deprecated_options[ 'reorder_where' ] );
        }

        if ( isset( $deprecated_options[ 'sql' ] ) )
            $options[ 'sql' ] = array( 'sql' => $deprecated_options[ 'sql' ] );

        if ( isset( $deprecated_options[ 'search' ] ) )
            $options[ 'searchable' ] = $deprecated_options[ 'search' ];
        if ( isset( $deprecated_options[ 'search_across' ] ) )
            $options[ 'search_across' ] = $deprecated_options[ 'search_across' ];
        if ( isset( $deprecated_options[ 'search_across_picks' ] ) )
            $options[ 'search_across_picks' ] = $deprecated_options[ 'search_across_picks' ];
        if ( isset( $deprecated_options[ 'filters' ] ) )
            $options[ 'filters' ] = $deprecated_options[ 'filters' ];
        if ( isset( $deprecated_options[ 'custom_filters' ] ) ) {
            if ( is_callable( $deprecated_options[ 'custom_filters' ] ) )
                add_filter( 'pods_ui_filters', $deprecated_options[ 'custom_filters' ] );
            else {
                global $pods_ui_custom_filters;
                $pods_ui_custom_filters = $deprecated_options[ 'custom_filters' ];
                add_filter( 'pods_ui_filters', array( $this, 'deprecated_filters' ) );
            }
        }

        if ( isset( $deprecated_options[ 'disable_actions' ] ) )
            $options[ 'actions_disabled' ] = $deprecated_options[ 'disable_actions' ];
        if ( isset( $deprecated_options[ 'hide_actions' ] ) )
            $options[ 'actions_hidden' ] = $deprecated_options[ 'hide_actions' ];

        if ( isset( $deprecated_options[ 'wpcss' ] ) )
            $options[ 'wpcss' ] = $deprecated_options[ 'wpcss' ];

        return $options;
    }

    /**
     *
     */
    public function deprecated_filters () {
        global $pods_ui_custom_filters;
        echo $pods_ui_custom_filters;
    }

    /**
     * @param $options
     *
     * @return array|bool|mixed|null|PodsArray
     */
    public function setup ( $options ) {
        $options = pods_array( $options );

        $options->validate( 'num', '', 'absint' );

        if ( empty( $options->num ) )
            $options->num = '';

        $options->validate( 'id', pods_var( 'id' . $options->num, 'get', $this->id ) );

        $options->validate( 'do', pods_var( 'do' . $options->num, 'get', $this->do ), 'in_array', array(
            'save',
            'create'
        ) );

        $options->validate( 'action', pods_var( 'action' . $options->num, 'get', $this->action ), 'in_array', $this->actions );

        $options->validate( 'searchable', $this->searchable, 'boolean' );
        $options->validate( 'search', pods_var( 'search' . $options->num, 'get' ) );
        $options->validate( 'search_across', $this->search_across, 'boolean' );
        $options->validate( 'search_across_picks', $this->search_across_picks, 'boolean' );
        $options->validate( 'filters', $this->filters, 'array' );
        $options->validate( 'where', $this->where, 'array_merge' );

        $options->validate( 'pagination', $this->pagination, 'boolean' );
        $options->validate( 'page', pods_var( 'pg' . $options->num, 'get', $this->page ), 'absint' );
        $options->validate( 'limit', pods_var( 'limit' . $options->num, 'get', $this->limit ), 'int' );

        if ( isset( $this->pods_data ) && is_object( $this->pods_data ) ) {
            $this->sql = array(
                'table' => $this->pods_data->table,
                'field_id' => $this->pods_data->field_id,
                'field_index' => $this->pods_data->field_index
            );
        }
        $options->validate( 'sql', $this->sql, 'array_merge' );

        $options->validate( 'sortable', $this->sortable, 'boolean' );

        // Handle Sorting
        $orderby_dir = pods_var_raw( 'orderby_dir' . $options->num, 'get', $this->orderby_dir, null, true );

        if ( 'asc' == strtolower( $orderby_dir ) )
            $orderby_dir = 'ASC';
        else
            $orderby_dir = 'DESC';
        $options->orderby_dir = $orderby_dir;

        $orderby = pods_var_raw( 'orderby', 'get', $this->orderby, null, true );

        if ( !empty( $orderby ) ) {
            $orderby = array(
                'default' => $orderby
            );

            if ( !empty( $options->orderby ) )
                $orderby = array_merge( $orderby, (array) $options->orderby );
        }
        else
            $orderby = (array) $options->orderby;

        $options->orderby = $orderby;

        $options->validate( 'item', __( 'Item', 'pods' ) );
        $options->validate( 'items', __( 'Items', 'pods' ) );

        $options->validate( 'heading', array(
            'manage' => __( 'Manage', 'pods' ),
            'add' => __( 'Add New', 'pods' ),
            'edit' => __( 'Edit', 'pods' ),
            'duplicate' => __( 'Duplicate', 'pods' ),
            'view' => __( 'View', 'pods' ),
            'reorder' => __( 'Reorder', 'pods' )
        ), 'array_merge' );

        $options->validate( 'label', array(
            'add' => __( 'Add New', 'pods' ) . " {$this->item}",
            'edit' => __( 'Edit', 'pods' ) . " {$this->item}",
            'duplicate' => __( 'Duplicate', 'pods' ) . " {$this->item}",
            'delete' => __( 'Delete this', 'pods' ) . " {$this->item}",
            'view' => __( 'View', 'pods' ) . " {$this->item}",
            'reorder' => __( 'Reorder', 'pods' ) . " {$this->items}"
        ), 'array_merge' );

        $options->validate( 'fields', array(
            'manage' => array(
                $options->sql[ 'field_index' ] => array( 'label' => __( 'Name', 'pods' ) )
            )
        ), 'array' );

        $options->validate( 'export', $this->export, 'array_merge' );
        $options->validate( 'reorder', $this->reorder, 'array_merge' );
        $options->validate( 'screen_options', $this->screen_options, 'array_merge' );

        $options->validate( 'session', $this->session, 'in_array', array(
            'search',
            'filters',
            'show_per_page',
            'orderby'
        ) );
        $options->validate( 'user', $this->user, 'in_array', array(
            'search',
            'filters',
            'show_per_page',
            'orderby'
        ) );

        $options->validate( 'action_after', $this->action_after, 'array_merge' );
        $options->validate( 'action_links', $this->action_links, 'array_merge' );
        $options->validate( 'actions_disabled', $this->actions_disabled, 'array' );
        $options->validate( 'actions_hidden', $this->actions_hidden, 'array_merge' );
        $options->validate( 'actions_custom', $this->actions_custom, 'array_merge' );

        $options->validate( 'icon', $this->icon );
        $options->validate( 'css', $this->css );
        $options->validate( 'wpcss', $this->wpcss, 'boolean' );

        if ( true === $options[ 'wpcss' ] ) {
            global $user_ID;
            get_currentuserinfo();

            $color = get_user_meta( $user_ID, 'admin_color', true );
            if ( strlen( $color ) < 1 )
                $color = 'fresh';

            $this->wpcss = "colors-{$color}";
        }

        $options = $options->dump();

        $options = $this->do_hook( 'setup_options', $options );

        if ( false !== $options && !empty( $options ) ) {
            foreach ( $options as $option => $value ) {
                if ( isset( $this->{$option} ) )
                    $this->{$option} = $value;
                else
                    $this->x[ $option ] = $value;
            }
        }

        $unique_identifier = pods_var( 'page', 'get' ); // wp-admin page
        if ( is_object( $this->pod ) && isset( $this->pod->pod ) )
            $unique_identifier = '_' . $this->pod->pod;
        elseif ( 0 < strlen( $this->sql[ 'table' ] ) )
            $unique_identifier = '_' . $this->sql[ 'table' ];

        $unique_identifier .= '_' . $this->page;
        if ( 0 < strlen( $this->num ) )
            $unique_identifier .= '_' . $this->num;

        $this->unique_identifier = 'pods_ui_' . md5( $unique_identifier );

        $this->setup_fields();

        return $options;
    }

    /**
     * @param null $fields
     * @param string $which
     *
     * @return array|bool|mixed|null
     */
    public function setup_fields ( $fields = null, $which = 'fields' ) {
        $init = false;
        if ( null === $fields ) {
            if ( isset( $this->fields[ $which ] ) )
                $fields = (array) $this->fields[ $which ];
            elseif ( isset( $this->fields[ 'manage' ] ) )
                $fields = (array) $this->fields[ 'manage' ];
            else
                $fields = array();
            if ( 'fields' == $which )
                $init = true;
        }
        if ( !empty( $fields ) ) {
            // Available Attributes
            // type = field type
            // type = date (data validation as date)
            // type = time (data validation as time)
            // type = datetime (data validation as datetime)
            // date_touch = use current timestamp when saving (even if readonly, if type is date-related)
            // date_touch_on_create = use current timestamp when saving ONLY on create (even if readonly, if type is date-related)
            // date_ongoing = use this additional field to search between as if the first is the "start" and the date_ongoing is the "end" for filter
            // type = text / other (single line text box)
            // type = desc (textarea)
            // type = number (data validation as int float)
            // type = decimal (data validation as decimal)
            // type = password (single line password box)
            // type = bool (checkbox)
            // type = related (select box)
            // related = table to relate to (if type=related) OR custom array of (key => label or comma separated values) items
            // related_field = field name on table to show (if type=related) - default "name"
            // related_multiple = true (ability to select multiple values if type=related)
            // related_sql = custom where / order by SQL (if type=related)
            // readonly = true (shows as text)
            // display = false (doesn't show on form, but can be saved)
            // search = this field is searchable
            // filter = this field will be independently searchable (by default, searchable fields are searched by the primary search box)
            // comments = comments to show for field
            // comments_top = true (shows comments above field instead of below)
            // real_name = the real name of the field (if using an alias for 'name')
            // group_related = true (uses HAVING instead of WHERE for filtering field)
            $new_fields = array();
            $filterable = false;
            if ( empty( $this->filters ) && ( empty( $this->fields[ 'search' ] ) || 'search' == $which ) && false !== $this->searchable ) {
                $filterable = true;
                $this->filters = array();
            }

            foreach ( $fields as $field => $attributes ) {
                if ( !is_array( $attributes ) ) {
                    if ( is_int( $field ) ) {
                        $field = $attributes;
                        $attributes = array();
                    }
                    else
                        $attributes = array( 'label' => $attributes );
                }

                if ( !isset( $attributes[ 'real_name' ] ) )
                    $attributes[ 'real_name' ] = pods_var( 'name', $attributes, $field );

                if ( is_object( $this->pod ) && isset( $this->pod->fields ) && isset( $this->pod->fields[ $attributes[ 'real_name' ] ] ) )
                    $attributes = array_merge( $this->pod->fields[ $attributes[ 'real_name' ] ], $attributes );

                if ( !isset( $attributes[ 'id' ] ) )
                    $attributes[ 'id' ] = '';
                if ( !isset( $attributes[ 'label' ] ) )
                    $attributes[ 'label' ] = ucwords( str_replace( '_', ' ', $field ) );
                if ( !isset( $attributes[ 'type' ] ) )
                    $attributes[ 'type' ] = 'text';
                if ( !isset( $attributes[ 'options' ][ 'date_format_type' ] ) )
                    $attributes[ 'options' ][ 'date_format_type' ] = 'date';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related' ] ) )
                    $attributes[ 'related' ] = false;
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_id' ] ) )
                    $attributes[ 'related_id' ] = 'id';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_field' ] ) )
                    $attributes[ 'related_field' ] = 'name';
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_multiple' ] ) )
                    $attributes[ 'related_multiple' ] = false;
                if ( 'related' != $attributes[ 'type' ] || !isset( $attributes[ 'related_sql' ] ) )
                    $attributes[ 'related_sql' ] = false;
                if ( 'related' == $attributes[ 'type' ] && ( is_array( $attributes[ 'related' ] ) || strpos( $attributes[ 'related' ], ',' ) ) ) {
                    if ( !is_array( $attributes[ 'related' ] ) ) {
                        $attributes[ 'related' ] = @explode( ',', $attributes[ 'related' ] );
                        $related_items = array();
                        foreach ( $attributes[ 'related' ] as $key => $label ) {
                            if ( is_numeric( $key ) ) {
                                $key = $label;
                                $label = ucwords( str_replace( '_', ' ', $label ) );
                            }
                            $related_items[ $key ] = $label;
                        }
                        $attributes[ 'related' ] = $related_items;
                    }
                    if ( empty( $attributes[ 'related' ] ) )
                        $attributes[ 'related' ] = false;
                }
                if ( !isset( $attributes[ 'readonly' ] ) )
                    $attributes[ 'readonly' ] = false;
                if ( !isset( $attributes[ 'date_touch' ] ) || 'date' != $attributes[ 'type' ] )
                    $attributes[ 'date_touch' ] = false;
                if ( !isset( $attributes[ 'date_touch_on_create' ] ) || 'date' != $attributes[ 'type' ] )
                    $attributes[ 'date_touch_on_create' ] = false;
                if ( !isset( $attributes[ 'display' ] ) )
                    $attributes[ 'display' ] = true;
                if ( !isset( $attributes[ 'hidden' ] ) )
                    $attributes[ 'hidden' ] = false;
                if ( !isset( $attributes[ 'sortable' ] ) || false === $this->sortable )
                    $attributes[ 'sortable' ] = ( false !== $this->sortable ) ? true : false;
                if ( !isset( $attributes[ 'options' ] [ 'search' ] ) || false === $this->searchable )
                    $attributes[ 'options' ] [ 'search' ] = ( false !== $this->searchable ) ? true : false;
                if ( !isset( $attributes[ 'filter' ] ) || false === $this->searchable )
                    $attributes[ 'filter' ] = false;
                if ( false !== $attributes[ 'filter' ] && false !== $filterable )
                    $this->filters[] = $field;
                if ( false === $attributes[ 'filter' ] || !isset( $attributes[ 'filter_label' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'filter_label' ] = $attributes[ 'label' ];
                if ( false === $attributes[ 'filter' ] || !isset( $attributes[ 'filter_default' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'filter_default' ] = false;
                if ( false === $attributes[ 'filter' ] || !isset( $attributes[ 'date_ongoing' ] ) || 'date' != $attributes[ 'type' ] || !in_array( $field, $this->filters ) )
                    $attributes[ 'date_ongoing' ] = false;
                if ( false === $attributes[ 'filter' ] || !isset( $attributes[ 'date_ongoing' ] ) || 'date' != $attributes[ 'type' ] || !isset( $attributes[ 'date_ongoing_default' ] ) || !in_array( $field, $this->filters ) )
                    $attributes[ 'date_ongoing_default' ] = false;
                if ( !isset( $attributes[ 'export' ] ) )
                    $attributes[ 'export' ] = true;
                if ( !isset( $attributes[ 'group_related' ] ) )
                    $attributes[ 'group_related' ] = false;
                if ( !isset( $attributes[ 'comments' ] ) )
                    $attributes[ 'comments' ] = '';
                if ( !isset( $attributes[ 'comments_top' ] ) )
                    $attributes[ 'comments_top' ] = false;
                if ( !isset( $attributes[ 'custom_view' ] ) )
                    $attributes[ 'custom_view' ] = false;
                if ( !isset( $attributes[ 'custom_input' ] ) )
                    $attributes[ 'custom_input' ] = false;
                if ( isset( $attributes[ 'display_helper' ] ) ) // pods ui backward compatibility
                    $attributes[ 'custom_display' ] = $attributes[ 'display_helper' ];
                if ( !isset( $attributes[ 'custom_display' ] ) )
                    $attributes[ 'custom_display' ] = false;
                if ( !isset( $attributes[ 'custom_relate' ] ) )
                    $attributes[ 'custom_relate' ] = false;
                if ( !isset( $attributes[ 'custom_form_display' ] ) )
                    $attributes[ 'custom_form_display' ] = false;
                if ( 'search_columns' == $which && !$attributes[ 'options' ][ 'search' ] )
                    continue;

                $attributes = PodsForm::field_setup( $attributes, null, $attributes[ 'type' ] );

                $new_fields[ $field ] = $attributes;
            }
            $fields = $new_fields;
        }
        if ( false !== $init ) {
            if ( 'fields' != $which && !empty( $this->fields ) )
                $this->fields = $this->setup_fields( $this->fields, 'fields' );
            else
                $this->fields[ 'manage' ] = $fields;

            if ( !in_array( 'add', $this->actions_disabled ) || !in_array( 'edit', $this->actions_disabled ) || !in_array( 'duplicate', $this->actions_disabled ) ) {
                if ( 'form' != $which && isset( $this->fields[ 'form' ] ) && is_array( $this->fields[ 'form' ] ) )
                    $this->fields[ 'form' ] = $this->setup_fields( $this->fields[ 'form' ], 'form' );
                else
                    $this->fields[ 'form' ] = $fields;

                if ( !in_array( 'add', $this->actions_disabled ) ) {
                    if ( 'add' != $which && isset( $this->fields[ 'add' ] ) && is_array( $this->fields[ 'add' ] ) )
                        $this->fields[ 'add' ] = $this->setup_fields( $this->fields[ 'add' ], 'add' );
                    else
                        $this->fields[ 'add' ] = $fields;
                }
                if ( !in_array( 'edit', $this->actions_disabled ) ) {
                    if ( 'edit' != $which && isset( $this->fields[ 'edit' ] ) &&is_array( $this->fields[ 'edit' ] ) )
                        $this->fields[ 'edit' ] = $this->setup_fields( $this->fields[ 'edit' ], 'edit' );
                    else
                        $this->fields[ 'edit' ] = $fields;
                }
                if ( !in_array( 'duplicate', $this->actions_disabled ) ) {
                    if ( 'duplicate' != $which && isset( $this->fields[ 'duplicate' ] ) &&is_array( $this->fields[ 'duplicate' ] ) )
                        $this->fields[ 'duplicate' ] = $this->setup_fields( $this->fields[ 'duplicate' ], 'duplicate' );
                    else
                        $this->fields[ 'duplicate' ] = $fields;
                }
            }

            if ( false !== $this->searchable ) {
                if ( 'search' != $which && isset( $this->fields[ 'search' ] ) &&!empty( $this->fields[ 'search' ] ) )
                    $this->fields[ 'search' ] = $this->setup_fields( $this->fields[ 'search' ], 'search' );
                else
                    $this->fields[ 'search' ] = $fields;
            }
            else
                $this->fields[ 'search' ] = false;

            if ( !in_array( 'export', $this->actions_disabled ) ) {
                if ( 'export' != $which && isset( $this->fields[ 'export' ] ) &&!empty( $this->fields[ 'export' ] ) )
                    $this->fields[ 'export' ] = $this->setup_fields( $this->fields[ 'export' ], 'export' );
                else
                    $this->fields[ 'export' ] = $fields;
            }

            if ( !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
                if ( 'reorder' != $which && isset( $this->fields[ 'reorder' ] ) &&!empty( $this->fields[ 'reorder' ] ) )
                    $this->fields[ 'reorder' ] = $this->setup_fields( $this->fields[ 'reorder' ], 'reorder' );
                else
                    $this->fields[ 'reorder' ] = $fields;
            }
        }
        return $this->do_hook( 'setup_fields', $fields, $which, $init );
    }

    /**
     * @param $msg
     * @param bool $error
     */
    public function message ( $msg, $error = false ) {
        $msg = $this->do_hook( ( $error ) ? 'error' : 'message', $msg );
        ?>
    <div id="message" class="<?php echo ( $error ) ? 'error' : 'updated'; ?> fade"><p><?php echo $msg; ?></p></div>
    <?php
    }

    /**
     * @param $msg
     *
     * @return bool
     */
    public function error ( $msg ) {
        $this->message( $msg, true );

        return false;
    }

    /**
     * @return mixed
     */
    public function go () {
        $this->do_hook( 'go' );
        $_GET = pods_unsanitize( $_GET ); // fix wp sanitization
        $_POST = pods_unsanitize( $_POST ); // fix wp sanitization

        if ( false !== $this->css ) {
            ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $this->css; ?>" />
        <?php
        }
        if ( false !== $this->wpcss ) {
            $stylesheets = array( 'global', 'wp-admin', $this->wpcss );
            foreach ( $stylesheets as $style ) {
                if ( !wp_style_is( $style, 'queue' ) && !wp_style_is( $style, 'to_do' ) && !wp_style_is( $style, 'done' ) )
                    wp_enqueue_style( $style );
            }
        }

        $this->ui_page = array( $this->action );
        if ( 'add' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) {
            $this->ui_page[] = 'form';
            if ( 'create' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
                $this->ui_page[] = $this->do;
                $this->save( true );
                $this->manage();
            }
            else
                $this->add();
        }
        elseif ( ( 'edit' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) || ( 'duplicate' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) ) {
            $this->ui_page[] = 'form';
            if ( 'save' == $this->do && $this->save && !empty( $_POST ) )
                $this->save();
            $this->edit( ( 'duplicate' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) ? 1 : 0 );
        }
        elseif ( 'delete' == $this->action && !in_array( $this->action, $this->actions_disabled ) ) {
            $this->delete( $this->id );
            $this->manage();
        }
        elseif ( 'reorder' == $this->action && !in_array( $this->action, $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            if ( 'save' == $this->do ) {
                $this->ui_page[] = $this->do;
                $this->reorder();
            }
            $this->manage( true );
        }
        elseif ( 'save' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
            $this->ui_page[] = $this->do;
            $this->save();
            $this->manage();
        }
        elseif ( 'create' == $this->do && $this->save && !in_array( $this->do, $this->actions_disabled ) && !empty( $_POST ) ) {
            $this->ui_page[] = $this->do;
            $this->save( true );
            $this->manage();
        }
        elseif ( 'view' == $this->action && !in_array( $this->action, $this->actions_disabled ) )
            $this->view();
        elseif ( isset( $this->actions_custom[ $this->action ] ) && is_callable( $this->actions_custom[ $this->action ] ) )
            return call_user_func_array( $this->actions_custom[ $this->action ], array( &$this ) );
        elseif ( isset( $this->actions_custom[ $this->action ] ) && ( is_array( $this->actions_custom[ $this->action ] ) && isset( $this->actions_custom[ $this->action ][ 'callback' ] ) && is_callable( $this->actions_custom[ $this->action ][ 'callback' ] ) ) )
            return call_user_func_array( $this->actions_custom[ $this->action ][ 'callback' ], array( &$this ) );
        elseif ( !in_array( 'manage', $this->actions_disabled ) )
            $this->manage();

        // handle session / user persistent settings for show_per_page, orderby, search, and filters
        $methods = array( 'session', 'user' );
        foreach ( $methods as $method ) {
            foreach ( $this->$method as $setting ) {
                if ( 'show_per_page' == $setting )
                    $value = $this->limit;
                elseif ( 'orderby' == $setting ) {
                    if ( empty( $this->orderby ) )
                        $value = '';
                    elseif ( isset( $this->orderby[ 'default' ] ) ) // save this if we have a default index set
                        $value = $this->orderby[ 'default' ] . ' '
                             . ( false === strpos( $this->orderby[ 'default' ], ' ' ) ? $this->orderby_dir : '' );
                    else
                        $value = '';
                }
                else
                    $value = $this->$setting;

                pods_var_set( $value, $setting, $method );
            }
        }
    }

    /**
     * @return mixed
     */
    public function add () {
        $this->do_hook( 'add' );
        if ( isset( $this->actions_custom[ 'add' ] ) && is_callable( $this->actions_custom[ 'add' ] ) )
            return call_user_func_array( $this->actions_custom[ 'add' ], array( &$this ) );
        ?>
    <div class="wrap">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-image:url(<?php echo $this->icon; ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            echo $this->heading[ 'add' ] . ' ' . $this->item;

            $link = pods_var_update( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), array( 'page' ), $this->exclusion() );
            ?>
            <a href="<?php echo $link; ?>" class="add-new-h2">&laquo; <?php _e( 'Back to', 'pods' ); ?> <?php echo $this->heading[ 'manage' ]; ?></a>
        </h2>

        <?php $this->form( true ); ?>
    </div>
    <?php
    }

    /**
     * @param bool $duplicate
     *
     * @return mixed
     */
    public function edit ( $duplicate = false ) {
        if ( !in_array( 'duplicate', $this->actions_disabled ) )
            $duplicate = false;
        if ( empty( $this->row ) )
            $this->get_row();
        $this->do_hook( 'edit', $duplicate );
        if ( isset( $this->actions_custom[ 'edit' ] ) && is_callable( $this->actions_custom[ 'edit' ] ) )
            return call_user_func_array( $this->actions_custom[ 'edit' ], array( $duplicate, &$this ) );
        ?>
    <div class="wrap">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-image:url(<?php echo $this->icon; ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            echo ( true === $duplicate ) ? $this->heading[ 'duplicate' ] : $this->heading[ 'edit' ] . ' ' . $this->item;

            if ( !in_array( 'add', $this->actions_disabled ) ) {
                $link = pods_var_update( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), array( 'page' ), $this->exclusion() );
                ?>
                <a href="<?php echo $link; ?>" class="add-new-h2">&laquo; <?php _e( 'Back to', 'pods' ); ?> <?php echo $this->heading[ 'manage' ]; ?></a>
                <?php
            }
            else {
                $link = pods_var_update( array( 'action' . $this->num => 'add', 'id' . $this->num => '' ), array( 'page' ), $this->exclusion() );
                ?>
                <a href="<?php echo $link; ?>" class="add-new-h2"><?php echo $this->heading[ 'add' ]; ?></a>
                <?php
            }
            ?>
        </h2>

        <?php $this->form( false, $duplicate ); ?>
    </div>
    <?php
    }

    /**
     * @param bool $create
     * @param bool $duplicate
     *
     * @return bool|mixed
     */
    public function form ( $create = false, $duplicate = false ) {
        $this->do_hook( 'form' );

        if ( isset( $this->actions_custom[ 'form' ] ) && is_callable( $this->actions_custom[ 'form' ] ) )
            return call_user_func_array( $this->actions_custom[ 'form' ], array( &$this ) );

        $label = $this->label[ 'add' ];
        $id = null;
        $vars = array(
            'action' . $this->num => $this->action_after[ 'add' ],
            'do' . $this->num => 'create',
            'id' . $this->num => 'X_ID_X'
        );

        $alt_vars = $vars;
        $alt_vars[ 'action' ] = 'manage';
        unset( $alt_vars[ 'id' ] );

        if ( false === $create ) {
            if ( empty( $this->row ) )
                $this->get_row();

            if ( empty( $this->row ) )
                return $this->error( sprintf( __( '<strong>Error:</strong> %s not found.', 'pods' ), $this->item ) );

            $label = $this->label[ 'edit' ];
            $id = $this->row[ $this->sql[ 'field_id' ] ];
            $vars = array(
                'action' . $this->num => $this->action_after[ 'edit' ],
                'do' . $this->num => 'save',
                'id' . $this->num => $id
            );

            $alt_vars = $vars;
            $alt_vars[ 'action' ] = 'manage';
            unset( $alt_vars[ 'id' ] );

            if ( 1 == $duplicate ) {
                $label = $this->label[ 'duplicate' ];
                $id = null;
                $vars = array(
                    'action' . $this->num => $this->action_after[ 'duplicate' ],
                    'do' . $this->num => 'create',
                    'id' . $this->num => 'X_ID_X'
                );

                $alt_vars = $vars;
                $alt_vars[ 'action' ] = 'manage';
                unset( $alt_vars[ 'id' ] );
            }
        }

        $fields =& $this->fields[ $this->action ];
        $pod =& $this->pod;
        $thank_you = pods_var_update( $vars, array( 'page' ), $this->exclusion() );
        $thank_you_alt = pods_var_update( $alt_vars, array( 'page' ), $this->exclusion() );
        $obj =& $this;
        $singular_label = $this->item;
        $plural_label = $this->items;
        $label = sprintf( __( 'Save %s', 'pods' ), $this->item );

        pods_view( PODS_DIR . 'ui/admin/form.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * @return bool|mixed
     */
    public function view () {
        $this->do_hook( 'view' );
        if ( isset( $this->actions_custom[ 'view' ] ) && is_callable( $this->actions_custom[ 'view' ] ) )
            return call_user_func_array( $this->actions_custom[ 'view' ], array( &$this ) );
        if ( empty( $this->row ) )
            $this->get_row();
        if ( empty( $this->row ) )
            return $this->error( sprintf( __( '<strong>Error:</strong> %s not found.', 'pods' ), $this->item ) );
        $id = $this->row[ $this->sql[ 'field_id' ] ];
        // HOOK INTO FORM CLASS HERE FOR VIEW
    }

    /**
     * Reorder data
     */
    public function reorder () {
        // loop through order
        $order = (array) pods_var_raw( 'order', 'post', array(), null, true );

        $params = array(
            'pod' => $this->pod->pod,
            'field' => $this->reorder[ 'on' ],
            'order' => $order
        );

        return pods_api()->reorder_pod_item( $params );
    }

    /**
     * @param bool $insert
     *
     * @return mixed
     */
    public function save ( $insert = false ) {
        $this->do_hook( 'pre_save', $insert );
        if ( isset( $this->actions_custom[ 'save' ] ) && is_callable( $this->actions_custom[ 'save' ] ) )
            return call_user_func_array( $this->actions_custom[ 'save' ], array( $insert, &$this ) );
        global $wpdb;
        $action = __( 'saved', 'pods' );
        if ( true === $insert )
            $action = __( 'created', 'pods' );
        $field_sql = array();
        $values = array();
        $data = array();
        foreach ( $this->fields[ 'form' ] as $field => $attributes ) {
            $vartype = '%s';
            if ( 'bool' == $attributes[ 'type' ] )
                $selected = ( 1 == pods_var( $field, 'post', 0 ) ) ? 1 : 0;
            elseif ( '' == pods_var( $field, 'post', '' ) )
                continue;
            if ( false === $attributes[ 'display' ] || false !== $attributes[ 'readonly' ] ) {
                if ( !in_array( $attributes[ 'type' ], array( 'date', 'time', 'datetime' ) ) )
                    continue;
                if ( false === $attributes[ 'date_touch' ] && ( false === $attributes[ 'date_touch_on_create' ] || false === $insert || 0 < $this->id ) )
                    continue;
            }
            if ( in_array( $attributes[ 'type' ], array( 'date', 'time', 'datetime' ) ) ) {
                $format = "Y-m-d H:i:s";
                if ( 'date' == $attributes[ 'type' ] )
                    $format = "Y-m-d";
                if ( 'time' == $attributes[ 'type' ] )
                    $format = "H:i:s";
                if ( false !== $attributes[ 'date_touch' ] || ( false !== $attributes[ 'date_touch_on_create' ] && true === $insert && $this->id < 1 ) )
                    $value = date_i18n( $format );
                else
                    $value = date_i18n( $format, strtotime( ( 'time' == $attributes[ 'type' ] ) ? date_i18n( 'Y-m-d ' ) : pods_var( $field, 'post', '' ) ) );
            }
            else {
                if ( 'bool' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = 0;
                    if ( '' != pods_var( $field, 'post', '' ) )
                        $value = 1;
                }
                elseif ( 'number' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = number_format( pods_var( $field, 'post', 0 ), 0, '', '' );
                }
                elseif ( 'decimal' == $attributes[ 'type' ] ) {
                    $vartype = '%d';
                    $value = number_format( pods_var( $field, 'post', 0 ), 2, '.', '' );
                }
                elseif ( 'related' == $attributes[ 'type' ] ) {
                    if ( is_array( pods_var( $field, 'post', '' ) ) )
                        $value = implode( ',', pods_var( $field, 'post', '' ) );
                    else
                        $value = pods_var( $field, 'post', '' );
                }
                else
                    $value = pods_var( $field, 'post', '' );
            }
            if ( isset( $this->actions_custom[ 'save' ] ) && is_callable( $this->actions_custom[ 'save' ] ) )
                return call_user_func_array( $this->actions_custom[ 'save' ], array( $insert, &$this ) );
            if ( isset( $attributes[ 'custom_save' ] ) && false !== $attributes[ 'custom_save' ] && is_callable( $attributes[ 'custom_save' ] ) )
                $value = call_user_func_array( $attributes[ 'custom_save' ], array( $value, $field, $attributes, &$this ) );
            $field_sql[] = "`$field`=$vartype";
            $values[] = $value;
            $data[ $field ] = $value;
        }
        $field_sql = implode( ',', $field_sql );
        if ( false === $insert && 0 < $this->id ) {
            $this->insert_id = $this->id;
            $values[] = $this->id;
            $check = $wpdb->query( $wpdb->prepare( "UPDATE $this->sql['table'] SET $field_sql WHERE id=%d", $values ) );
        }
        else
            $check = $wpdb->query( $wpdb->prepare( "INSERT INTO $this->sql['table'] SET $field_sql", $values ) );
        if ( $check ) {
            if ( 0 == $this->insert_id )
                $this->insert_id = $wpdb->insert_id;
            $this->message( __( "<strong>Success!</strong> {$this->item} {$action} successfully.", 'pods' ) );
        }
        else
            $this->error( __( "<strong>Error:</strong> {$this->item} has not been {$action}.", 'pods' ) );
        $this->do_hook( 'post_save', $this->insert_id, $data, $insert );
    }

    /**
     * @param null $id
     *
     * @return bool|mixed
     */
    public function delete ( $id = null ) {
        $this->do_hook( 'pre_delete', $id );

        if ( isset( $this->actions_custom[ 'delete' ] ) && is_callable( $this->actions_custom[ 'delete' ] ) )
            return call_user_func_array( $this->actions_custom[ 'delete' ], array( $id, &$this ) );

        $id = pods_absint( $id );

        if ( empty( $id ) )
            $id = pods_absint( $this->id );

        if ( $id < 1 )
            return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "id" definition.', 'pods' ) );

        if ( false === $id )
            $id = $this->id;

        if ( is_object( $this->pod ) )
            $check = $this->pod->delete( $id );
        else
            $check = $this->pods_data->delete( $this->table, array( $this->data->field_id => $id ) );

        if ( $check )
            $this->message( __( "<strong>Deleted:</strong> {$this->item} has been deleted.", 'pods' ) );
        else
            $this->error( __( "<strong>Error:</strong> {$this->item} has not been deleted.", 'pods' ) );
        $this->do_hook( 'post_delete', $id );
    }

    /**
     * @param $field
     *
     * @return array|bool|mixed|null
     */
    public function get_field ( $field ) {
        $value = null;

        // use PodsData to get field

        if ( isset( $this->actions_custom[ 'get_field' ] ) && is_callable( $this->actions_custom[ 'get_field' ] ) )
            return call_user_func_array( $this->actions_custom[ 'get_field' ], array( $field, &$this ) );

        if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
            if ( 'Pod' == get_class( $this->pod ) )
                $value = $this->pod->get_field( $field );
            else
                $value = $this->pod->field( $field );
        }
        elseif ( isset( $this->row[ $field ] ) )
            $value = $this->row[ $field ];

        return $this->do_hook( 'get_field', $value, $field );
    }

    /**
     * @return bool
     */
    public function get_data () {
        if ( false !== $this->pod && is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) ) {
            $orderby = array();

            if ( 'reorder' == $this->action && empty( $this->reorder[ 'orderby' ] ) )
                $orderby[ $this->reorder[ 'on' ] ] = $this->reorder[ 'orderby_dir' ];

            if ( !empty( $this->orderby ) ) {
                $this->orderby = (array) $this->orderby;

                foreach ( $this->orderby as $order ) {
                    if ( false === strpos( ' ', $order ) && !isset( $orderby[ $order ] ) )
                        $orderby[ $order ] = $this->orderby_dir;
                }
            }

            $params = array(
                'where' => pods_var_raw( $this->action, $this->where, null, null, true ),
                'orderby' => $orderby,
                'page' => (int) $this->page,
                'limit' => (int) $this->limit,
                'search' => $this->searchable,
                'search_query' => $this->search
            );

            $this->data = $this->pod->find( $params )->data();

            if ( !empty( $this->data ) )
                $this->data_keys = array_keys( $this->data );

            $this->total = $this->pod->total();
            $this->total_found = $this->pod->total_found();
        }
        else {
            if ( !empty( $this->data ) )
                return $this->data;

            if ( empty( $this->sql[ 'table' ] ) )
                return $this->data;

            $orderby = '';

            if ( !empty( $this->orderby ) )
                $orderby = '`' . $this->orderby . '` '
                       . ( false === strpos( $this->orderby, ' ' ) ? strtoupper( $this->orderby_dir ) : '' );

            $this->pods_data->select(
                array(
                    'table' => $this->sql[ 'table' ],
                    'where' => pods_var_raw( $this->action, $this->where, null, null, true ),
                    'orderby' => $orderby,
                    'page' => (int) $this->page,
                    'limit' => (int) $this->limit,
                    'search' => $this->searchable,
                    'search_query' => $this->search,
                    'fields' => $this->fields[ 'search' ]
                )
            );

            $this->data = $this->pods_data->data;
            $this->data_keys = array_keys( $this->data );
            $this->total = $this->pods_data->total();
            $this->total_found = $this->pods_data->total_found();
        }

        return $this->data;
    }

    /**
     * Sort out data alphabetically by a key
     */
    public function sort_data () {
        // only do this if we have a default orderby
        if ( isset( $this->orderby[ 'default' ] ) ) {
            $orderby = $this->orderby[ 'default' ];
            foreach ( $this->data as $k => $v ) {
                $sorter[ $k ] = strtolower( $v[ $orderby ] );
            }
            if ( $this->orderby_dir == 'ASC' )
                asort( $sorter );
            else
                arsort( $sorter );
            foreach ( $sorter as $key => $val ) {
                $intermediary[] = $this->data[ $key ];
            }
            if ( isset( $intermediary ) ) {
                $this->data = $intermediary;
                $this->data_keys = array_keys( $this->data );
            }
        }
    }

    /**
     * @return array
     */
    public function get_row ( &$counter = 0 ) {
        if ( is_object( $this->pod ) && ( 'Pods' == get_class( $this->pod ) || 'Pod' == get_class( $this->pod ) ) )
            $this->row = $this->pod->fetch();
        else {
            $this->row = false;

            if ( !empty( $this->data ) ) {
                if ( empty( $this->data_keys ) || count( $this->data ) != count( $this->data_keys ) )
                    $this->data_keys = array_keys( $this->data );

                if ( count( $this->data ) == $this->total_found && isset( $this->data_keys[ $counter ] ) && isset( $this->data[ $this->data_keys[ $counter ] ] ) ) {
                    $this->row = $this->data[ $this->data_keys[ $counter ] ];

                    $counter++;
                }
            }

            if ( false === $this->row && 0 < (int) $this->id && !empty( $this->sql[ 'table' ] ) ) {
                $this->pods_data->select(
                    array(
                        'table' => $this->sql[ 'table' ],
                        'where' => '`' . $this->sql[ 'field_id' ] . '` = ' . (int) $this->id,
                        'limit' => 1
                    )
                );

                $this->row = $this->pods_data->fetch();
            }
        }

        return $this->row;
    }

    /**
     * @param bool $reorder
     *
     * @return mixed
     */
    public function manage ( $reorder = false ) {
        $this->do_hook( 'manage', $reorder );

        if ( isset( $this->actions_custom[ 'manage' ] ) && is_callable( $this->actions_custom[ 'manage' ] ) )
            return call_user_func_array( $this->actions_custom[ 'manage' ], array( $reorder, &$this ) );

        $this->screen_meta();

        if ( true === $reorder )
            wp_enqueue_script( 'jquery-ui-sortable' );
        ?>
    <div class="wrap">
        <div id="icon-edit-pages" class="icon32"<?php if ( false !== $this->icon ) { ?> style="background-position:0 0;background-image:url(<?php echo $this->icon; ?>);"<?php } ?>><br /></div>
        <h2>
            <?php
            if ( true === $reorder ) {
                echo $this->heading[ 'reorder' ] . ' ' . $this->items;
                ?>
                <small>(<a href="<?php echo pods_var_update( array( 'action' . $this->num => 'manage', 'id' . $this->num => '' ), array( 'page' ), $this->exclusion() ); ?>">&laquo; <?php _e( 'Back to Manage', 'pods' ); ?></a>)</small>
                <?php
            }
            else
                echo $this->heading[ 'manage' ] . ' ' . $this->items;
            if ( !in_array( 'add', $this->actions_disabled ) && !in_array( 'add', $this->actions_hidden ) ) {
                ?>
                <a href="<?php echo pods_var_update( array( 'action' . $this->num => 'add' ), array( 'page' ), $this->exclusion() ); ?>" class="add-new-h2"><?php echo $this->label[ 'add' ]; ?></a>
                <?php
            }
            if ( !in_array( 'reorder', $this->actions_disabled ) && !in_array( 'reorder', $this->actions_hidden ) && false !== $this->reorder[ 'on' ] ) {
                ?>
                <a href="<?php echo pods_var_update( array( 'action' . $this->num => 'reorder' ), array( 'page' ), $this->exclusion() ); ?>" class="add-new-h2"><?php echo $this->label[ 'reorder' ]; ?></a>
                <?php
            }
            ?>
        </h2>
        <?php if ( true !== $reorder ) { ?>
        <form id="posts-filter" action="<?php echo pods_var_update( array( 'pg' . $this->num => '' ), array( 'page' ), $this->exclusion() ); ?>" method="get">
        <?php } ?>
            <?php
            if ( isset( $this->actions_custom[ 'header' ] ) && is_callable( $this->actions_custom[ 'header' ] ) )
                return call_user_func_array( $this->actions_custom[ 'header' ], array( $reorder, &$this ) );

            if ( false === $this->data )
                $this->get_data();
            elseif ( $this->sortable ) // we have the data already as an array
                $this->sort_data();

            if ( !in_array( 'export', $this->actions_disabled ) && 'export' == $this->action )
                $this->export();

            if ( ( !empty( $this->data ) || false !== $this->search ) && false !== $this->searchable ) {
                ?>
                <p class="search-box" align="right">
                    <?php
                    $excluded_filters = array( 'search' . $this->num, 'pg' . $this->num );
                    foreach ( $this->filters as $filter ) {
                        $excluded_filters[] = 'filter_' . $filter . '_start';
                        $excluded_filters[] = 'filter_' . $filter . '_end';
                        $excluded_filters[] = 'filter_' . $filter;
                    }
                    $this->hidden_vars( $excluded_filters );
                    $date_exists = false;
                    foreach ( $this->filters as $filter ) {
                        // use PodsFormUI fields
                        if ( !isset( $this->fields[ 'search' ][ $filter ] ) )
                            continue;
                        if ( in_array( $this->fields[ 'search' ][ $filter ][ 'type' ], array( 'date', 'datetime' ) ) ) {
                            if ( false === $date_exists ) {
                                $date_exists = true;/*
                                ?>
                                <link type="text/css" rel="stylesheet" href="<?php echo $this->assets_url; ?>/jquery/ui.datepicker.css" />
                                <script type="text/javascript">
                                    jQuery( document ).ready( function () {
                                        jQuery.getScript( '<?php echo $this->assets_url; ?>/jquery/ui.datepicker.js', function () {
                                            jQuery( 'input.admin_ui_date' ).datepicker();
                                        } );
                                    } );
                                </script>
                                <?php*/
                            }
                            $start = pods_var( 'filter_' . $filter . '_start', 'get', $this->fields[ 'search' ][ $filter ][ 'filter_default' ] );
                            $end = pods_var( 'filter_' . $filter . '_end', 'get', $this->fields[ 'search' ][ $filter ][ 'filter_ongoing_default' ] );
                            ?>
                            &nbsp;&nbsp; <label for="admin_ui_filter_<?php echo $filter; ?>_start"><?php echo $this->fields[ 'search' ][ $filter ][ 'filter_label' ]; ?>:</label>
                            <input type="text" name="filter_<?php echo $filter; ?>_start" class="admin_ui_filter admin_ui_date" id="admin_ui_filter_<?php echo $filter; ?>_start" value="<?php echo ( false !== $start && 0 < strlen( $start ) ) ? date_i18n( 'm/d/Y', strtotime( $start ) ) : ''; ?>" />
                            <label for="admin_ui_filter_<?php echo $filter; ?>_end">to</label>
                            <input type="text" name="filter_<?php echo $filter; ?>_end" class="admin_ui_filter admin_ui_date" id="admin_ui_filter_<?php echo $filter; ?>_end" value="<?php echo ( false !== $end && 0 < strlen( $end ) ) ? date_i18n( 'm/d/Y', strtotime( $end ) ) : ''; ?>" />
                            <?php
                        }
                        elseif ( 'related' == $this->fields[ 'search' ][ $filter ][ 'type' ] && false !== $this->fields[ 'search' ][ $filter ][ 'related' ] ) {
                            $selected = pods_var( 'filter_' . $filter, 'get', $this->fields[ 'search' ][ $filter ][ 'filter_default' ] );
                            if ( !is_array( $this->fields[ 'search' ][ $filter ][ 'related' ] ) ) {
                                // use PodsData to pull data
                                global $wpdb;
                                $related = $wpdb->get_results( 'SELECT `' . $this->fields[ 'search' ][ $filter ][ 'related_id' ] . '`,`' . $this->fields[ 'search' ][ $filter ][ 'related_field' ] . '` FROM ' . $this->fields[ 'search' ][ $filter ][ 'related' ] . ( !empty( $this->fields[ 'search' ][ $filter ][ 'related_sql' ] ) ? ' ' . $this->fields[ 'search' ][ $filter ][ 'related_sql' ] : '' ) );
                                ?>
                                <label for="admin_ui_filter_<?php echo $filter; ?>"><?php echo $this->fields[ 'search' ][ $filter ][ 'filter_label' ]; ?>:</label>
                                <select name="filter_<?php echo $filter; ?><?php echo ( false !== $this->fields[ 'search' ][ $filter ][ 'related_multiple' ] ? '[]' : '' ); ?>" id="admin_ui_filter_<?php echo $filter; ?>"<?php echo ( false !== $this->fields[ 'search' ][ $filter ][ 'related_multiple' ] ? ' size="10" style="height:auto;" MULTIPLE' : '' ); ?>>
                                    <option value="">-- <?php _e( 'Show All', 'pods' ); ?> --</option>
                                    <?php
                                    foreach ( $related as $option ) {
                                        ?>
                                        <option value="<?php echo $option->{$this->fields[ 'search' ][ $filter ][ 'related_id' ]}; ?>"<?php echo ( $option->{$this->fields[ 'search' ][ $filter ][ 'related_id' ]} == $selected ? ' SELECTED' : '' ); ?>><?php echo $option->{$this->fields[ 'search' ][ $filter ][ 'related_field' ]}; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <?php
                            }
                            else {
                                $related = $this->fields[ 'search' ][ $filter ][ 'related' ];
                                ?>
                                <label for="admin_ui_filter_<?php echo $filter; ?>"><?php echo $this->fields[ 'search' ][ $filter ][ 'filter_label' ]; ?>:</label>
                                <select name="filter_<?php echo $filter; ?><?php echo ( false !== $this->fields[ 'search' ][ $filter ][ 'related_multiple' ] ? '[]' : '' ); ?>" id="admin_ui_filter_<?php echo $filter; ?>"<?php echo ( false !== $this->fields[ 'search' ][ $filter ][ 'related_multiple' ] ? ' size="10" style="height:auto;" MULTIPLE' : '' ); ?>>
                                    <option value="">-- <?php _e( 'Show All', 'pods' ); ?> --</option>
                                    <?php
                                    foreach ( $related as $option_id => $option ) {
                                        ?>
                                        <option value="<?php echo $option_id; ?>"<?php echo ( $option->id == $selected ? ' SELECTED' : '' ); ?>><?php echo $option; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <?php
                            }
                        }
                        else {
                            ?>
                            <label for="admin_ui_filter_<?php echo $filter; ?>"><?php echo $this->fields[ 'search' ][ $filter ][ 'filter_label' ]; ?>:</label>
                            <input type="text" name="filter_<?php echo $filter; ?>" class="admin_ui_filter" id="admin_ui_filter_<?php echo $filter; ?>" value="<?php echo pods_var( 'filter_' . $filter, 'get', $this->fields[ 'search' ][ $filter ][ 'filter_default' ] ); ?>" />
                            <?php
                        }
                    }
                    ?>
                    &nbsp;&nbsp; <label<?php echo ( empty( $this->filters ) ) ? ' class="screen-reader-text"' : ''; ?> for="page-search-input"><?php _e( 'Search', 'pods' ); ?>:</label>
                    <input type="text" name="search<?php echo $this->num; ?>" id="page-search-input" value="<?php echo $this->search; ?>" />
                    <input type="submit" value="<?php esc_attr_e( 'Search', 'pods' ); echo ' ' . esc_attr( $this->items ); ?>" class="button" />
                    <?php
                    if ( 0 < strlen( $this->search ) ) {
                        $clear_filters = array();
                        foreach ( $this->filters as $filter ) {
                            $clear_filters[ 'filter_' . $filter . '_start' ] = '';
                            $clear_filters[ 'filter_' . $filter . '_end' ] = '';
                            $clear_filters[ 'filter_' . $filter ] = '';
                        }
                        ?>
                        <br class="clear" />
                        <small>[<a href="<?php echo pods_var_update( $clear_filters, array( 'orderby' . $this->num, 'orderby_dir' . $this->num, 'limit' . $this->num, 'page' ), $this->exclusion() ); ?>"><?php _e( 'Reset Filters', 'pods' ); ?></a>]</small>
                        <br class="clear" />
                        <?php
                    }
                    ?>
                </p>
                <?php
            }
            else {
                ?>
                <br class="clear" />
                <?php
            }

            if ( !empty( $this->data ) && ( false !== $this->pagination_total || false !== $this->pagination || true === $reorder ) || ( !in_array( 'export', $this->actions_disabled ) && !in_array( 'export', $this->actions_hidden ) ) ) {
                ?>
                <div class="tablenav">
                    <?php
                    if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
                        ?>
                        <div class="tablenav-pages<?php echo ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page'; ?>">
                            <?php $this->pagination( 1 ); ?>
                        </div>
                        <?php
                    }

                    if ( true === $reorder ) {
                        ?>
                        <input type="button" value="<?php _e( 'Update Order', 'pods' ); ?>" class="button" onclick="jQuery('form.admin_ui_reorder_form').submit();" />
                        <input type="button" value="<?php _e( 'Cancel', 'pods' ); ?>" class="button" onclick="document.location='<?php echo pods_var_update( array( 'action' . $this->num => 'manage' ), array( 'page' ), $this->exclusion() ); ?>';" />
                        <?php
                    }
                    /*
                    elseif (!in_array('delete', $this->actions_disabled) && !in_array('delete', $this->actions_hidden) && defined('PODS_DEVELOPER')) {
        ?>
                    <div class="alignleft actions">
                        <select name="action">
                            <option value="-1" selected="selected"><?php _e('Bulk Actions', 'pods'); ?></option>
                            <option value="delete"><?php _e('Delete', 'pods'); ?></option>
                        </select> <input type="submit" id="doaction" class="button-secondary action" value="<?php _e('Apply', 'pods'); ?>">
                    </div>
        <?php
                    }*/
                    elseif ( !in_array( 'export', $this->actions_disabled ) && !in_array( 'export', $this->actions_hidden ) ) {
                        ?>
                        <div class="alignleft actions">
                            <strong><?php _e( 'Export', 'pods' ); ?>:</strong>
                            <?php
                            foreach ( $this->export[ 'formats' ] as $format => $separator ) {
                                ?>
                                <input type="button" value=" <?php echo strtoupper( $format ); ?> " class="button" onclick="document.location='<?php echo pods_var_update( array( 'action' . $this->num => 'export', 'export_type' . $this->num => $format ), array( 'page' ), $this->exclusion() ); ?>';" />
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <br class="clear" />
                </div>
                <?php
            }
            else {
                ?>
                <br class="clear" />
                <?php
            }
            ?>
            <div class="clear"></div>
            <?php
            if ( empty( $this->data ) && false !== $this->default_none && false === $this->search ) {
                ?>
                <p><?php _e( 'Please use the search filter(s) above to display data', 'pods' ); ?><?php if ( $this->export ) { ?>, <?php _e( 'or click on an Export to download a full copy of the data', 'pods' ); ?><?php } ?>.</p>
                <?php
            }
            else
                $this->table( $reorder );
            if ( !empty( $this->data ) ) {
                /*if (!in_array('delete', $this->actions_disabled) && !in_array('delete', $this->actions_hidden) && defined('PODS_DEVELOPER')) {
    ?>
            <div class="alignleft actions">
                <select name="action2">
                    <option value="-1" selected="selected"><?php _e('Bulk Actions', 'pods'); ?></option>
                    <option value="delete"><?php _e('Delete', 'pods'); ?></option>
                </select> <input type="submit" id="doaction2" class="button-secondary action" value="<?php _e('Apply', 'pods'); ?>">
            </div>
    <?php
                }*/
                if ( true !== $reorder && ( false !== $this->pagination_total || false !== $this->pagination ) ) {
                    ?>
                    <div class="tablenav">
                        <div class="tablenav-pages<?php echo ( $this->limit < $this->total_found || 1 < $this->page ) ? '' : ' one-page'; ?>">
                            <?php $this->pagination( 0 ); ?>
                            <br class="clear" />
                        </div>
                    </div>
                    <?php
                }
            }

            if ( true !== $reorder ) {
            ?>
        </form>
        <?php } ?>
    </div>
    <?php
    }

    /**
     * @param bool $reorder
     *
     * @return bool|mixed
     */
    public function table ( $reorder = false ) {
        $this->do_hook( 'table', $reorder );
        if ( isset( $this->actions_custom[ 'table' ] ) && is_callable( $this->actions_custom[ 'table' ] ) )
            return call_user_func_array( $this->actions_custom[ 'table' ], array( $reorder, &$this ) );
        if ( empty( $this->data ) ) {
            ?>
        <p><?php echo sprintf( __( 'No %s found', 'pods' ), $this->items ); ?></p>
        <?php
            return false;
        }
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            ?>
        <style type="text/css">
            table.widefat.fixed tbody.reorderable tr {
                height: 50px;
            }

            .dragme {
                background: url(<?php echo PODS_URL; ?>/ui/images/handle.gif) no-repeat;
                background-position: 8px 5px;
                cursor: pointer;
            }

            .dragme strong {
                margin-left: 30px;
            }
        </style>
<form action="<?php echo pods_var_update( array( 'action' . $this->num => 'reorder', 'do' . $this->num => 'save' ), array( 'page' ), $this->exclusion() ); ?>" method="post" class="admin_ui_reorder_form">
<?php
        }
        $table_fields = $this->fields[ 'manage' ];
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] )
            $table_fields = $this->fields[ 'reorder' ];
        if ( false === $table_fields || empty( $table_fields ) )
            return $this->error( __( '<strong>Error:</strong> Invalid Configuration - Missing "fields" definition.', 'pods' ) );
        ?>
        <table class="widefat page fixed wp-list-table" cellspacing="0"<?php echo ( 1 == $reorder && $this->reorder ) ? ' id="admin_ui_reorder"' : ''; ?>>
            <thead>
                <tr>
                    <?php
                    /*if (!in_array('delete', $this->actions_disabled) && !in_array('delete', $this->actions_hidden) && defined('PODS_DEVELOPER')) {
            ?>
                            <th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
            <?php
                    }*/
                    $name_field = false;
                    $fields = array();
                    if ( !empty( $table_fields ) ) {
                        foreach ( $table_fields as $field => $attributes ) {
                            if ( false === $attributes[ 'display' ] )
                                continue;
                            if ( false === $name_field )
                                $id = 'title';
                            else
                                $id = '';
                            if ( 'other' == $attributes[ 'type' ] )
                                $id = '';
                            if ( in_array( $attributes[ 'type' ], array( 'date', 'datetime', 'time' ) ) )
                                $id = 'date';
                            if ( false === $name_field && 'title' == $id )
                                $name_field = true;
                            $fields[ $field ] = $attributes;
                            $fields[ $field ][ 'field_id' ] = $id;
                            $dir = 'DESC';
                            $current_sort = ' asc';
                            if ( isset( $this->orderby[ 'default' ] ) && $field == $this->orderby[ 'default' ] ) {
                                if ( 'DESC' == $this->orderby_dir ) {
                                    $dir = 'ASC';
                                    $current_sort = ' desc';
                                }
                            }

                            $att_id = '';
                            if ( !empty( $id ) )
                                $att_id = ' id="' . $id . '"';

                            $width = '';

                            if ( isset( $attributes[ 'width' ] ) && !empty( $attributes[ 'width' ] ) )
                                $width = ' style="width: ' . $attributes[ 'width' ] . '"';

                            if ( $fields[ $field ][ 'sortable' ] ) {
                                ?>
                                <th scope="col"<?php echo $att_id; ?> class="manage-column column-<?php echo $id; ?> sortable<?php echo $current_sort; ?>"<?php echo $width; ?>>
                                    <a href="<?php echo pods_var_update( array( 'orderby' . $this->num => $field, 'orderby_dir' . $this->num => $dir ), array( 'limit' . $this->num, 'search' . $this->num, 'pg' . $this->num, 'page' ), $this->exclusion() ); ?>"> <span><?php echo $attributes[ 'label' ]; ?></span> <span class="sorting-indicator"></span> </a>
                                </th>
                                <?php
                            }
                            else {
                                ?>
                                <th scope="col"<?php echo $att_id; ?> class="manage-column column-<?php echo $id; ?>"<?php echo $width; ?>><?php echo $attributes[ 'label' ]; ?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                </tr>
            </thead>
            <?php
            if ( 6 < $this->total_found ) {
                ?>
                <tfoot>
                    <tr>
                        <?php
                        /*if (!in_array('delete', $this->actions_disabled) && !in_array('delete', $this->actions_hidden) && defined('PODS_DEVELOPER')) {
            ?>
                                <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
            <?php
                        }*/
                        if ( !empty( $fields ) ) {
                            foreach ( $fields as $field => $attributes ) {
                                $dir = 'ASC';
                                if ( $field == $this->orderby ) {
                                    $current_sort = 'desc';
                                    if ( 'ASC' == $this->orderby_dir ) {
                                        $dir = 'DESC';
                                        $current_sort = 'asc';
                                    }
                                }

                                $width = '';

                                if ( isset( $attributes[ 'width' ] ) && !empty( $attributes[ 'width' ] ) )
                                    $width = ' style="width: ' . $attributes[ 'width' ] . '"';

                                if ( $fields[ $field ][ 'sortable' ] ) {
                                    ?>
                                    <th scope="col" class="manage-column column-<?php echo $id; ?> sortable <?php echo $current_sort; ?>"<?php echo $width; ?>><a href="<?php echo pods_var_update( array( 'orderby' . $this->num => $field, 'orderby_dir' . $this->num => $dir ), array( 'limit' . $this->num, 'search' . $this->num, 'pg' . $this->num, 'page' ), $this->exclusion() ); ?>"><span><?php echo $attributes[ 'label' ]; ?></span><span class="sorting-indicator"></span></a></th>
                                    <?php
                                }
                                else {
                                    ?>
                                    <th scope="col" class="manage-column column-<?php echo $id; ?>"<?php echo $width; ?>><?php echo $attributes[ 'label' ]; ?></th>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </tr>
                </tfoot>
                <?php
            }
            ?>
            <tbody id="the-list"<?php echo ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) ? ' class="reorderable"' : ''; ?>>
                <?php
                if ( !empty( $this->data ) && is_array( $this->data ) ) {
                    $counter = 0;

                    while ( $row = $this->get_row( $counter ) ) {
                        if ( is_object( $row ) )
                            $row = get_object_vars( (object) $row );

                        $toggle_class = '';

                        if ( is_array( $this->actions_custom ) && isset( $this->actions_custom[ 'toggle' ] ) ) {
                            $toggle_class = ' pods-toggled-on';

                            if ( !isset( $row[ 'toggle' ] ) || empty( $row[ 'toggle' ] ) )
                                $toggle_class = ' pods-toggled-off';
                        }
                        ?>
                        <tr id="item-<?php echo $row[ $this->sql[ 'field_id' ] ]; ?>" class="iedit<?php echo $toggle_class; ?>">
                            <?php
                            /*if (!in_array('delete', $this->actions_disabled) && !in_array('delete', $this->actions_hidden) && defined('PODS_DEVELOPER')) {
            ?>
                            <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="<?php echo $row[$this->sql['field_id']]; ?>"></th>
            <?php
                            }*/
                            foreach ( $fields as $field => $attributes ) {
                                if ( false === $attributes[ 'display' ] )
                                    continue;

                                if ( !isset( $row[ $field ] ) )
                                    $row[ $field ] = $this->get_field( $field );

                                if ( false !== $attributes[ 'custom_display' ] ) {
                                    if ( is_callable( $attributes[ 'custom_display' ] ) )
                                        $row[ $field ] = call_user_func_array( $attributes[ 'custom_display' ], array( $row, &$this ) );
                                    elseif ( is_object( $this->pod ) && class_exists( 'Pods_Helpers' ) )
                                        $row[ $field ] = $this->pod->helper( $attributes[ 'custom_display' ], $row[ $field ], $field );
                                }
                                else {
                                    ob_start();

                                    $field_value = trim( (string) PodsForm::field_method( $attributes[ 'type' ], 'ui', $this->id, $row[ $field ], $field, $attributes, $fields, $this->pod ) );

                                    $field_output = ob_get_clean();

                                    if ( 0 < strlen( $field_value ) )
                                        $row[ $field ] = $field_value;
                                    elseif ( 0 < strlen( $field_output ) )
                                        $row[ $field ] = $field_output;
                                    else {
                                        // run formats
                                    }
                                }

                                if ( false !== $attributes[ 'custom_relate' ] ) {
                                    global $wpdb;
                                    $table = $attributes[ 'custom_relate' ];
                                    $on = $this->sql[ 'field_id' ];
                                    $is = $row[ $this->sql[ 'field_id' ] ];
                                    $what = array( 'name' );
                                    if ( is_array( $table ) ) {
                                        if ( isset( $table[ 'on' ] ) )
                                            $on = pods_sanitize( $table[ 'on' ] );
                                        if ( isset( $table[ 'is' ] ) && isset( $row[ $table[ 'is' ] ] ) )
                                            $is = pods_sanitize( $row[ $table[ 'is' ] ] );
                                        if ( isset( $table[ 'what' ] ) ) {
                                            $what = array();
                                            if ( is_array( $table[ 'what' ] ) ) {
                                                foreach ( $table[ 'what' ] as $wha ) {
                                                    $what[] = pods_sanitize( $wha );
                                                }
                                            }
                                            else
                                                $what[] = pods_sanitize( $table[ 'what' ] );
                                        }
                                        if ( isset( $table[ 'table' ] ) )
                                            $table = $table[ 'table' ];
                                    }
                                    $table = pods_sanitize( $table );
                                    $wha = implode( ',', $what );
                                    $sql = "SELECT {$wha} FROM {$table} WHERE `{$on}`='{$is}'";
                                    $value = @current( $wpdb->get_results( $sql, ARRAY_A ) );
                                    if ( !empty( $value ) ) {
                                        $val = array();
                                        foreach ( $what as $wha ) {
                                            if ( isset( $value[ $wha ] ) )
                                                $val[] = $value[ $wha ];
                                        }
                                        if ( !empty( $val ) )
                                            $row[ $field ] = implode( ' ', $val );
                                    }
                                }
                                if ( 'title' == $attributes[ 'field_id' ] ) {
                                    if ( !in_array( 'edit', $this->actions_disabled ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) ) {
                                        ?>
                <td class="post-title page-title column-title"><strong><a class="row-title" href="<?php echo pods_var_update( array( 'action' . $this->num => 'edit', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ); ?>" title="Edit &#8220;<?php echo htmlentities( $row[ $field ], ENT_COMPAT, get_bloginfo( 'charset' ) ); ?>&#8221;"><?php echo $row[ $field ]; ?></a></strong>
                                        <?php
                                    }
                                    elseif ( !in_array( 'view', $this->actions_disabled ) && ( false === $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) ) {
                                        ?>
                <td class="post-title page-title column-title"><strong><a class="row-title" href="<?php echo pods_var_update( array( 'action' . $this->num => 'view', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ); ?>" title="View &#8220;<?php echo htmlentities( $row[ $field ], ENT_COMPAT, get_bloginfo( 'charset' ) ); ?>&#8221;"><?php echo $row[ $field ]; ?></a></strong>
                                        <?php
                                    }
                                    else {
                                        ?>
                <td class="post-title page-title column-title<?php echo ( 1 == $reorder && $this->reorder ) ? ' dragme' : ''; ?>"><strong><?php echo $row[ $field ]; ?></strong>
                                        <?php
                                    }

                                    if ( true !== $reorder || in_array( 'reorder', $this->actions_disabled ) || false === $this->reorder[ 'on' ] ) {
                                        $toggle = false;
                                        $actions = array();

                                        if ( !in_array( 'view', $this->actions_disabled ) )
                                            $actions[ 'view' ] = '<span class="view"><a href="' . pods_var_update( array( 'action' . $this->num => 'view', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ) . '" title="' . __( 'View this item', 'pods' ) . '">' . __( 'View', 'pods' ) . '</a></span>';

                                        if ( !in_array( 'edit', $this->actions_disabled ) )
                                            $actions[ 'edit' ] = '<span class="edit"><a href="' . pods_var_update( array( 'action' . $this->num => 'edit', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ) . '" title="' . __( 'Edit this item', 'pods' ) . '">' . __( 'Edit', 'pods' ) . '</a></span>';

                                        if ( !in_array( 'duplicate', $this->actions_disabled ) )
                                            $actions[ 'duplicate' ] = '<span class="edit"><a href="' . pods_var_update( array( 'action' . $this->num => 'duplicate', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ) . '" title="' . __( 'Duplicate this item', 'pods' ) . '">' . __( 'Duplicate', 'pods' ) . '</a></span>';

                                        if ( !in_array( 'delete', $this->actions_disabled ) )
                                            $actions[ 'delete' ] = '<span class="delete"><a href="' . pods_var_update( array( 'action' . $this->num => 'delete', 'id' . $this->num => $row[ $this->sql[ 'field_id' ] ] ), array( 'page' ), $this->exclusion() ) . '" title="' . __( 'Delete this item', 'pods' ) . '" class="submitdelete" onclick="if(confirm(\'' . __( 'You are about to permanently delete this item\n Choose \\\'Cancel\\\' to stop, \\\'OK\\\' to delete.', 'pods' ) . '\')){return true;}return false;">' . __( 'Delete', 'pods' ) . '</a></span>';

                                        if ( is_array( $this->actions_custom ) ) {
                                            foreach ( $this->actions_custom as $custom_action => $custom_data ) {
                                                if ( is_array( $custom_data ) && ( isset( $custom_data[ 'link' ] ) || isset( $custom_data[ 'callback' ] ) ) ) {
                                                    if ( !in_array( $custom_action, array( 'add', 'view', 'edit', 'duplicate', 'delete', 'save', 'export', 'reorder' ) ) ) {
                                                        if ( 'toggle' == $custom_action ) {
                                                            $toggle = true;
                                                            $toggle_labels = array(
                                                                __( 'Enable', 'pods' ),
                                                                __( 'Disable', 'pods' )
                                                            );

                                                            $custom_data[ 'label' ] = ( $row[ 'toggle' ] ? $toggle_labels[ 1 ] : $toggle_labels[ 0 ] );
                                                        }

                                                        if ( !isset( $custom_data[ 'label' ] ) )
                                                            $custom_data[ 'label' ] = ucwords( str_replace( '_', ' ', $custom_action ) );

                                                        if ( !isset( $custom_data[ 'link' ] ) ) {
                                                            $vars = array(
                                                                'action' => $custom_action,
                                                                'id' => $row[ $this->sql[ 'field_id' ] ]
                                                            );

                                                            if ( 'toggle' == $custom_action )
                                                                $vars[ 'toggle' ] = (int) ( !$row[ 'toggle' ] );

                                                            $custom_data[ 'link' ] = pods_var_update( $vars );
                                                        }

                                                        $actions[ $custom_action ] = '<span class="edit"><a href="' . $this->do_template( $custom_data[ 'link' ], $row ) . '" title="' . esc_attr( $custom_data[ 'label' ] ) . ' this item">' . $custom_data[ 'label' ] . '</a></span>';
                                                    }
                                                }
                                            }
                                        }

                                        $actions = $this->do_hook( 'row_actions', $actions );

                                        if ( !empty( $actions ) ) {
                                            ?>
                                            <div class="row-actions<?php echo ( $toggle ? ' row-actions-toggle' : '' ); ?>">
                                                <?php
                                                if ( isset( $this->actions_custom[ 'actions_start' ] ) && is_callable( $this->actions_custom[ 'actions_start' ] ) )
                                                    call_user_func_array( $this->actions_custom[ 'actions_start' ], array( $row, $actions, &$this ) );

                                                echo implode( ' | ', $actions );

                                                if ( isset( $this->actions_custom[ 'actions_end' ] ) && is_callable( $this->actions_custom[ 'actions_end' ] ) )
                                                    call_user_func_array( $this->actions_custom[ 'actions_end' ], array( $row, $actions, &$this ) );
                                                ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                    else {
                                        ?>
                                        <input type="hidden" name="order[]" value="<?php echo $row[ $this->sql[ 'field_id' ] ]; ?>" />
                                        <?php
                                    }
                                    ?>
                </td>
<?php
                                }
                                elseif ( 'date' == $attributes[ 'type' ] ) {
                                    ?>
                                    <td class="date column-date"><abbr title="<?php echo esc_attr( $row[ $field ] ); ?>"><?php echo $row[ $field ]; ?></abbr></td>
                                    <?php
                                }
                                else {
                                    ?>
                                    <td class="author"><?php echo $row[ $field ]; ?></td>
                                    <?php
                                }
                            }
                            ?>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
        if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
            ?>
</form>
<?php
        }
        ?>
    <script type="text/javascript">
        jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
            <?php
            if ( true === $reorder && !in_array( 'reorder', $this->actions_disabled ) && false !== $this->reorder[ 'on' ] ) {
                ?>
            jQuery( document ).ready( function () {
                jQuery( ".reorderable" ).sortable( {axis : "y", handle : ".dragme"} );
                jQuery( ".reorderable" ).bind( 'sortupdate', function ( event, ui ) {
                    jQuery( 'table.widefat tbody tr' ).removeClass( 'alternate' );
                    jQuery( 'table.widefat tbody tr:even' ).addClass( 'alternate' );
                } );
            } );
                <?php
            }
            ?>
    </script>
    <?php
    }

    /**
     *
     */
    public function screen_meta () {
        $screen_html = $help_html = '';
        $screen_link = $help_link = '';
        if ( !empty( $this->screen_options ) && !empty( $this->help ) ) {
            foreach ( $this->ui_page as $page ) {
                if ( isset( $this->screen_options[ $page ] ) ) {
                    if ( is_array( $this->screen_options[ $page ] ) ) {
                        if ( isset( $this->screen_options[ $page ][ 'link' ] ) ) {
                            $screen_link = $this->screen_options[ $page ][ 'link' ];
                            break;
                        }
                    }
                    else {
                        $screen_html = $this->screen_options[ $page ];
                        break;
                    }
                }
            }
            foreach ( $this->ui_page as $page ) {
                if ( isset( $this->help[ $page ] ) ) {
                    if ( is_array( $this->help[ $page ] ) ) {
                        if ( isset( $this->help[ $page ][ 'link' ] ) ) {
                            $help_link = $this->help[ $page ][ 'link' ];
                            break;
                        }
                    }
                    else {
                        $help_html = $this->help[ $page ];
                        break;
                    }
                }
            }
        }
        $screen_html = $this->do_hook( 'screen_meta_screen_html', $screen_html );
        $screen_link = $this->do_hook( 'screen_meta_screen_link', $screen_link );
        $help_html = $this->do_hook( 'screen_meta_help_html', $help_html );
        $help_link = $this->do_hook( 'screen_meta_help_link', $help_link );
        if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) || 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
            ?>
        <div id="screen-meta">
            <?php
            $this->do_hook( 'screen_meta_pre' );
            if ( 0 < strlen( $screen_html ) ) {
                ?>
                <div id="screen-options-wrap" class="hidden">
                    <form id="adv-settings" action="" method="post">
                        <?php
                        echo $screen_html;
                        $fields = array();
                        foreach ( $this->ui_page as $page ) {
                            if ( isset( $this->fields[ $page ] ) && !empty( $this->fields[ $page ] ) )
                                $fields = $this->fields[ $page ];
                        }
                        if ( !empty( $fields ) || true === $this->pagination ) {
                            ?>
                            <h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
                            <?php
                            if ( !empty( $fields ) ) {
                                ?>
                                <div class="metabox-prefs">
                                    <?php
                                    $this->do_hook( 'screen_meta_screen_options' );
                                    foreach ( $fields as $field => $attributes ) {
                                        if ( false === $attributes[ 'display' ] || true === $attributes[ 'hidden' ] )
                                            continue;
                                        ?>
                                        <label for="<?php echo $field; ?>-hide">
                                            <input class="hide-column-tog" name="<?php echo $this->unique_identifier; ?>_<?php echo $field; ?>-hide" type="checkbox" id="<?php echo $field; ?>-hide" value="<?php echo $field; ?>" checked="checked"><?php echo $attributes[ 'label' ]; ?></label>
                                        <?php
                                    }
                                    ?>
                                    <br class="clear">
                                </div>
                                <h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
                                <?php
                            }
                            ?>
                            <div class="screen-options">
                                <?php
                                if ( true === $this->pagination ) {
                                    ?>
                                    <input type="text" class="screen-per-page" name="wp_screen_options[value]" id="<?php echo $this->unique_identifier; ?>_per_page" maxlength="3" value="20"> <label for="<?php echo $this->unique_identifier; ?>_per_page"><?php echo $this->items; ?> per page</label>
                                    <?php
                                }
                                $this->do_hook( 'screen_meta_screen_submit' );
                                ?>
                                <input type="submit" name="screen-options-apply" id="screen-options-apply" class="button" value="Apply">
                                <input type="hidden" name="wp_screen_options[option]" value="<?php echo $this->unique_identifier; ?>_per_page">
                                <?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </form>
                </div>
                <?php
            }
            if ( 0 < strlen( $help_html ) ) {
                ?>
                <div id="contextual-help-wrap" class="hidden">
                    <div class="metabox-prefs">
                        <?php echo $help_html; ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <div id="screen-meta-links">
                <?php
                $this->do_hook( 'screen_meta_links_pre' );
                if ( 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
                    ?>
                    <div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">
                        <?php
                        if ( 0 < strlen( $help_link ) ) {
                            ?>
                            <a href="<?php echo $help_link; ?>" class="show-settings">Help</a>
                            <?php
                        }
                        else {
                            ?>
                            <a href="#contextual-help" id="contextual-help-link" class="show-settings">Help</a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) ) {
                    ?>
                    <div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
                        <?php
                        if ( 0 < strlen( $screen_link ) ) {
                            ?>
                            <a href="<?php echo $screen_link; ?>" class="show-settings">Screen Options</a>
                            <?php
                        }
                        else {
                            ?>
                            <a href="#screen-options" id="show-settings-link" class="show-settings">Screen Options</a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                $this->do_hook( 'screen_meta_links_post' );
                ?>
            </div>
            <?php
            $this->do_hook( 'screen_meta_post' );
            ?>
        </div>
        <?php
        }
    }

    /**
     * @param bool $header
     *
     * @return mixed
     */
    public function pagination ( $header = false ) {
        $this->do_hook( 'pagination', $header );

        if ( isset( $this->actions_custom[ 'pagination' ] ) && is_callable( $this->actions_custom[ 'pagination' ] ) )
            return call_user_func_array( $this->actions_custom[ 'pagination' ], array( $header, &$this ) );

        $total_pages = ceil( $this->total_found / $this->limit );
        $request_uri = pods_var_update( array( 'pg' . $this->num => '' ), array( 'limit' . $this->num, 'orderby' . $this->num, 'orderby_dir' . $this->num, 'search' . $this->num, 'page' ), $this->exclusion() );

        if ( false !== $this->pagination_total ) {
            ?>
        <span class="displaying-num"><?php echo $this->total_found; ?> item<?php echo ( 1 == $this->total_found ) ? '' : 's'; ?></span>
        <?php
        }

        if ( false !== $this->pagination ) {
            if ( 1 < $total_pages ) {
                ?>
            <a class="first-page<?php echo ( 1 < $this->page ) ? '' : ' disabled'; ?>" title="<?php _e( 'Go to the first page', 'pods' ); ?>" href="<?php echo $request_uri; ?>">&laquo;</a>
            <a class="prev-page<?php echo ( 1 < $this->page ) ? '' : ' disabled'; ?>" title="<?php _e( 'Go to the previous page', 'pods' ); ?>" href="<?php echo $request_uri; ?>&pg<?php echo $this->num; ?>=<?php echo max( $this->page - 1, 1 ); ?>">&lsaquo;</a>
            <?php
                if ( true == $header ) {
                    ?>
                <span class="paging-input"><input class="current-page" title="<?php _e( 'Current page', 'pods' ); ?>" type="text" name="pg<?php echo $this->num; ?>" value="<?php echo $this->page; ?>" size="<?php echo strlen( $total_pages ); ?>"> <?php _e( 'of', 'pods' ); ?> <span class="total-pages"><?php echo $total_pages; ?></span></span>
                <script>

                    jQuery( document ).ready( function ( $ ) {
                        var pageInput = $( 'input.current-page' );
                        var currentPage = pageInput.val();
                        pageInput.closest( 'form' ).submit( function ( e ) {
                            if ( (1 > $( 'select[name="action"]' ).length || $( 'select[name="action"]' ).val() == -1) && (1 > $( 'select[name="action2"]' ).length || $( 'select[name="action2"]' ).val() == -1) && pageInput.val() == currentPage ) {
                                pageInput.val( '1' );
                            }
                        } );
                    } );
                </script>
                <?php
                }
                else {
                    ?>
                <span class="paging-input"><?php echo $this->page; ?> <?php _e( 'of', 'pods' ); ?> <span class="total-pages"><?php echo $total_pages; ?></span></span>
                <?php
                }
                ?>
            <a class="next-page<?php echo ( $this->page < $total_pages ) ? '' : ' disabled'; ?>" title="<?php _e( 'Go to the next page', 'pods' ); ?>" href="<?php echo $request_uri; ?>&pg<?php echo $this->num; ?>=<?php echo min( $this->page + 1, $total_pages ); ?>">&rsaquo;</a>
            <a class="last-page<?php echo ( $this->page < $total_pages ) ? '' : ' disabled'; ?>" title="<?php _e( 'Go to the last page', 'pods' ); ?>'" href="<?php echo $request_uri; ?>&pg<?php echo $this->num; ?>=<?php echo $total_pages; ?>">&raquo;</a>
            <?php
            }
        }
    }

    /**
     * @param bool $options
     *
     * @return mixed
     */
    public function limit ( $options = false ) {
        $this->do_hook( 'limit', $options );
        if ( isset( $this->actions_custom[ 'limit' ] ) && is_callable( $this->actions_custom[ 'limit' ] ) )
            return call_user_func_array( $this->actions_custom[ 'limit' ], array( $options, &$this ) );
        if ( false === $options || !is_array( $options ) || empty( $options ) )
            $options = array( 10, 25, 50, 100, 200 );
        if ( !in_array( $this->limit, $options ) )
            $this->limit = $options[ 1 ];
        foreach ( $options as $option ) {
            if ( $option == $this->limit )
                echo " <span class=\"page-numbers current\">{$option}</span>";
            else
                echo ' <a href="' . pods_var_update( array( 'limit' => $option ), array( 'orderby' . $this->num, 'orderby_dir' . $this->num, 'search' . $this->num, 'page' ), $this->exclusion() ) . '">' . $option . '</a>';
        }
    }

    /**
     * @param $code
     * @param bool|array $row
     *
     * @return mixed
     */
    public function do_template ( $code, $row = false ) {
        if ( isset( $this->ui[ 'pod' ] ) && false !== $this->ui[ 'pod' ] && is_object( $this->ui[ 'pod' ] ) )
            return $this->ui[ 'pod' ]->do_magic_tags( $code );
        else {
            if ( false !== $row ) {
                $this->temp_row = $this->row;
                $this->row = $row;
            }
            $code = preg_replace_callback( "/({@(.*?)})/m", array( $this, "do_magic_tags" ), $code );
            if ( false !== $row ) {
                $this->row = $this->temp_row;
                unset( $this->temp_row );
            }
        }
        return $code;
    }

    /**
     * @param $tag
     *
     * @return string
     */
    public function do_magic_tags ( $tag ) {
        $tag = trim( $tag, ' {@}' );
        $tag = explode( ',', $tag );

        if ( empty( $tag ) || !isset( $tag[ 0 ] ) || 0 < strlen( trim( $tag[ 0 ] ) ) )
            return;

        foreach ( $tag as $k => $v ) {
            $tag[ $k ] = trim( $v );
        }

        $field_name = $tag[ 0 ];
        $value = $this->get_field( $field_name );

        if ( isset( $tag[ 1 ] ) && !empty( $tag[ 1 ] ) && is_callable( $tag[ 1 ] ) )
            $value = call_user_func_array( $tag[ 1 ], array( $value, $field_name, $this->row, &$this ) );

        $before = $after = '';

        if ( isset( $tag[ 2 ] ) && !empty( $tag[ 2 ] ) )
            $before = $tag[ 2 ];

        if ( isset( $tag[ 3 ] ) && !empty( $tag[ 3 ] ) )
            $after = $tag[ 3 ];

        if ( 0 < strlen( $value ) )
            return $before . $value . $after;

        return;
    }

    /**
     * @param bool|array $exclude
     * @param bool|array $array
     */
    public function hidden_vars ( $exclude = false, $array = false ) {
        $exclude = $this->do_hook( 'hidden_vars', $exclude, $array );
        if ( false === $exclude )
            $exclude = array();
        if ( !is_array( $exclude ) )
            $exclude = explode( ',', $exclude );
        $get = $_GET;
        if ( is_array( $array ) ) {
            foreach ( $array as $key => $val ) {
                if ( 0 < strlen( $val ) )
                    $get[ $key ] = $val;
                else
                    unset( $get[ $key ] );
            }
        }
        foreach ( $get as $k => $v ) {
            if ( in_array( $k, $exclude ) )
                continue;
            ?>
        <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
        <?php
        }
    }

    /**
     * @return array
     */
    public function exclusion () {
        $exclusion = self::$excluded;

        foreach ( $exclusion as &$exclude ) {
            $exclude = $exclude . $this->num;
        }

        return $exclusion;
    }

    /*
        // Example code for use with $this->do_hook
        public function my_filter_function ($args, $obj) {
            $obj[0]->item = 'Post';
            $obj[0]->add = true;
            // args are an array (0 => $arg1, 1 => $arg2)
            // may have more than one arg, dependant on filter
            return $args;
        }
        add_filter('pods_ui_post_init', 'my_filter_function', 10, 2);
    */
    /**
     * @return array|bool|mixed|null
     */
    private function do_hook () {
        $args = func_get_args();
        if ( empty( $args ) )
            return false;
        $name = array_shift( $args );
        return pods_do_hook( "ui", $name, $args, $this );
    }
}
