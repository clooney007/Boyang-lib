<?php
/**Boyang changes some function hooks/actions           WP CIVICRM**/
function civicrm_wp_scripts() {
  if (!civicrm_wp_initialize()) {
    return;
  }

  require_once 'CRM/Core/Smarty.php';
  $template = CRM_Core_Smarty::singleton();
  $buffer   = $template->fetch('CRM/common/jquery.files.tpl');
  $lines    = preg_split('/\s+/', $buffer);
  foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) {
      continue;
    }
    if (strpos($line, '.js') !== FALSE) {
      wp_enqueue_script($line, WP_PLUGIN_URL . "/civicrm/civicrm/$line");
    }
  }

  //add namespacing js
  wp_enqueue_script('js/jquery.conflict.js', WP_PLUGIN_URL . '/civicrm/civicrm/js/jquery.conflict.js');

  return;
}

function civicrm_wp_styles() {
  if (!civicrm_wp_initialize()) {
    return;
  }

  require_once 'CRM/Core/Smarty.php';
  $template = CRM_Core_Smarty::singleton();
  $buffer   = $template->fetch('CRM/common/jquery.files.tpl');
  $lines    = preg_split('/\s+/', $buffer);
  foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) {
      continue;
    }
    if (strpos($line, '.css') !== FALSE) {
      wp_register_style($line, WP_PLUGIN_URL . "/civicrm/civicrm/$line");
      wp_enqueue_style($line);
    }
  }

  wp_register_style('civicrm/css/deprecate.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/deprecate.css");
  wp_enqueue_style('civicrm/css/deprecate.css');
  wp_register_style('civicrm/css/civicrm.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/civicrm.css");
  wp_enqueue_style('civicrm/css/civicrm.css');
  wp_register_style('civicrm/css/extras.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/extras.css");
  wp_enqueue_style('civicrm/css/extras.css');

  return;
}


  if (!is_admin()) {
    add_filter('get_header', 'civicrm_wp_shortcode_includes');
  }

  if (!civicrm_wp_in_civicrm()) {
    return;
  }

  if (!is_admin()) {
    add_action('wp_print_styles', 'civicrm_wp_styles');

    add_action('wp_footer', 'civicrm_buffer_end');

    // we do this here rather than as an action, since we dont control
    // the order
    civicrm_buffer_start();

    civicrm_wp_frontend();
  }
  else {
    add_action('admin_print_styles', 'civicrm_wp_styles');
  }
  add_action('wp_print_scripts', 'civicrm_wp_scripts');
}

function civicrm_add_form_button($context) {
  if (!civicrm_wp_initialize()) {
    return '';
  }

  $config      = CRM_Core_Config::singleton();
  $imageBtnURL = $config->resourceBase . 'i/widget/logo.png';
  $out         = '<a href="#TB_inline?width=480&inlineId=civicrm_frontend_pages" class="thickbox" id="add_civi" title="' . __("Add CiviCRM Public Pages", 'CiviCRM') . '"><img src="' . $imageBtnURL . '" hieght="15" width="15" alt="' . __("Add CiviCRM Public Pages", 'CiviCRM') . '" /></a>';
  return $context . $out;
}

function civicrm_add_form_button_html() {
  $title = _e("Please select a CiviCRM front-end page type.", "CiviCRM");

  $now = date("Ymdhis");

  $sql = "
SELECT id, title
FROM   civicrm_contribution_page
WHERE  is_active = 1
AND    (
         ( start_date IS NULL AND end_date IS NULL )
OR       ( start_date <= $now AND end_date IS NULL )
OR       ( start_date IS NULL AND end_date >= $now )
OR       ( start_date <= $now AND end_date >= $now )
       )
";

  $dao = CRM_Core_DAO::executeQuery($sql);
  $contributionPages = array();
  while ($dao->fetch()) {
    $contributionPages[$dao->id] = $dao->title;
  }

  $sql = "
SELECT id, title
FROM   civicrm_event
WHERE  is_active = 1
AND ( is_template = 0 OR is_template IS NULL )
AND    (
         ( start_date IS NULL AND end_date IS NULL )
OR       ( start_date <= $now AND end_date IS NULL )
OR       ( start_date IS NULL AND end_date >= $now )
OR       ( start_date <= $now AND end_date >= $now )
OR       ( start_date >= $now )
       )
";

  $dao = CRM_Core_DAO::executeQuery($sql);
  $eventPages = array();
  while ($dao->fetch()) {
    $eventPages[$dao->id] = $dao->title;
  }
  ?>
  <script>
     function InsertCiviFrontPages( ) {
    var form_id = jQuery("#add_civicomponent_id").val();
    if (form_id == ""){
      alert ('Please select a frontend element.');
      return;
    }

    var action;
    var mode;
    var pid;
    var component = jQuery("#add_civicomponent_id").val( );
    switch ( component ) {
      case 'contribution':
        var pid  = jQuery("#add_contributepage_id").val();
        var mode = jQuery("input[name='component_mode']:checked").val( );
        break;
      case 'event':
        var pid    = jQuery("#add_eventpage_id").val();
        var action = jQuery("input[name='event_action']:checked").val( );
        var mode   = jQuery("input[name='component_mode']:checked").val( );
        break;
      case 'user-dashboard':
        break;
    }

    // [ civicrm component=contribution/event/profile id=N mode=test/live action=info/register/create/search/edit/view ]
    var shortcode = '[civicrm component="' + component + '"';

    if ( pid ) {
      shortcode = shortcode + ' id="'+ pid +'"';
    }

    if ( mode ) {
      shortcode = shortcode + ' mode="'+ mode +'"';
    }

    if ( action ) {
      shortcode = shortcode + ' action="'+ action +'"';
    }

    shortcode = shortcode + ']';
    window.send_to_editor( shortcode );
  }

  jQuery(function() {
      jQuery('#add_civicomponent_id').change(function(){
          var component = jQuery(this).val();
          switch ( component ) {
            case 'contribution':
              jQuery('#contribution-section').show();
              jQuery('#event-section').hide();
              jQuery('#component-section').show();
              jQuery('#action-section-event').hide();
              break;
            case 'event':
              jQuery('#contribution-section').hide();
              jQuery('#event-section').show();
              jQuery('#component-section').show();
              jQuery('#action-section-event').show();
              break;
            case 'user-dashboard':
              jQuery('#contribution-section').hide();
              jQuery('#event-section').hide();
              jQuery('#component-section').hide();
              jQuery('#action-section-event').hide();
              break;
          }
        });
    });
  </script>

      <div id="civicrm_frontend_pages" style="display:none;">
      <div class="wrap">
      <div>
      <div style="padding:15px 15px 0 15px;">
      <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;">
      <?php echo $title; ?>
      </h3>
      <span>
      <?php echo $title; ?>
      </span>
      </div>
      <div style="padding:15px 15px 0 15px;">
      <select id="add_civicomponent_id">
      <option value="">  <?php _e("Select a frontend element."); ?>  </option>
                                                                         <option value="contribution">Contribution Page</option>
                                                                         <option value="event">Event Page</option>
                                                                         <option value="user-dashboard">User Dashboard</option>
                                                                         </select>

                                                                         <span id="contribution-section" style="display:none;">
                                                                         <select id="add_contributepage_id">
                                                                         <?php
                                                                         foreach ($contributionPages as $key => $value) { ?>
                                                                                                                          <option value="<?php echo absint($key) ?>"><?php echo esc_html($value) ?></option>
                                                                                                                          <?php
  }?>
  </select>
      </span>

      <span id="event-section" style="display:none;">
      <select id="add_eventpage_id">
      <?php
      foreach ($eventPages as $key => $value) { ?>
                                                <option value="<?php echo absint($key) ?>"><?php echo esc_html($value) ?></option>
                                                <?php
  }?>
  </select>
      </span>
      <br>
      <span id="action-section-event" style="display:none;">
      <div style="padding:15px 15px 0 15px;">
      <input type="radio" name="event_action" value="info" checked="checked" /> Event Info Page
      <input type="radio" name="event_action" value="register" /> Event Registration Page
      </div>
      </span>
      <br/>
      <span id="component-section" style="display:none;">
      <div style="padding:15px 15px 0 15px;">
      <input type="radio" name="component_mode" value="live" checked="checked"/> Live Page
      <input type="radio" name="component_mode" value="test" /> Test Drive
      </div>
      </span>
      <br/>
      <div style="padding:8px 0 0 0; font-size:11px; font-style:italic; color:#5A5A5A"><?php _e("Can't find your form? Make sure it is active.", "gravityforms"); ?></div>
                                                                                                                                                                        </div>
                                                                                                                                                                        <div style="padding:15px;">
                                                                                                                                                                        <input type="button" class="button-primary" value="Insert Form" onclick="InsertCiviFrontPages();"/>&nbsp;&nbsp;&nbsp;
  <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel"); ?></a>
                                                                                                                </div>
                                                                                                                </div>
                                                                                                                </div>
                                                                                                                </div>

                                                                                                                <?php
                                                                                                                }

function civicrm_shortcode_handler($atts) {
  extract(shortcode_atts(array(
        'component' => 'contribution',
        'action' => NULL,
        'mode' => NULL,
        'id' => NULL,
        'cid' => NULL,
        'gid' => NULL,
        'cs' => NULL,
      ),
      $atts
    ));

  $args = array(
    'reset' => 1,
    'id' => $id,
  );

  switch ($component) {
    case 'contribution':
      if ($mode == 'preview' || $mode == 'test') {
        $args['action'] = 'preview';
      }
      $args['q'] = 'civicrm/contribute/transact';
      break;

    case 'event':
      switch ($action) {
        case 'register':
          $args['q'] = 'civicrm/event/register';
          if ($mode == 'preview' || $mode == 'test') {
            $args['action'] = 'preview';
          }
          break;

        case 'info':
          $args['q'] = 'civicrm/event/info';
          break;

        default:
          echo 'Do not know how to handle this shortcode<p>';
          return;
      }
      break;

    case 'user-dashboard':
      $args['q'] = 'civicrm/user';
      unset($args['id']);
      break;

    default:
      echo 'Do not know how to handle this shortcode<p>';
      return;
  }

  foreach ($args as $key => $value) {
    if ($value !== NULL) {
      $_GET[$key] = $value;
    }
  }

  return civicrm_wp_frontend(TRUE);
}

function civicrm_wp_in_civicrm() {
  return (isset($_GET['page']) &&
    $_GET['page'] == 'CiviCRM'
  ) ? TRUE : FALSE;
}

function civicrm_wp_shortcode_includes() {
  global $post;
  if (preg_match('/\[civicrm/', $post->post_content)) {
    add_action('wp_print_styles', 'civicrm_wp_styles');
    add_action('wp_print_scripts', 'civicrm_wp_scripts');
  }
}

function wp_get_breadcrumb() {
  global $wp_set_breadCrumb;
  return $wp_set_breadCrumb;
}

function wp_set_breadcrumb($breadCrumb) {
  global $wp_set_breadCrumb;
  $wp_set_breadCrumb = $breadCrumb;
  return $wp_set_breadCrumb;
}

function t($str, $sub = NULL) {
  if (is_array($sub)) {
    $str = str_replace(array_keys($sub), array_values($sub), $str);
  }
  return $str;
}

function civicrm_user_register($userID) {
  _civicrm_update_user($userID);
}

function civicrm_profile_update($userID) {
  _civicrm_update_user($userID);
}

function _civicrm_update_user($userID) {
  $user = get_userdata($userID);
  if ($user) {
    civicrm_wp_initialize();

    require_once 'CRM/Core/BAO/UFMatch.php';
    CRM_Core_BAO_UFMatch::synchronize($user,
      TRUE,
      'WordPress',
      'Individual'
    );
  }
}

function civicrm_buffer_start() {
  ob_start("civicrm_buffer_callback");
}

function civicrm_buffer_end() {
  ob_end_flush();
}

function civicrm_buffer_callback($buffer) {
  // modify buffer here, and then return the updated code
  return $buffer;
}

civicrm_wp_main();

