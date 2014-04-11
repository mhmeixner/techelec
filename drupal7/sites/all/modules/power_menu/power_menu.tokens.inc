<?php

/**
 * @file
 * Builds placeholder replacement tokens for power_menu.
 */

/**
 * Implements hook_token_info().
 */
function power_menu_token_info() {

  $types['power_menu'] = array(
    'name' => t("Power Menu"),
    'description' => t("Power Menu related tokens."),
  );

  $tokens['menu_path'] = array(
    'name' => t("Menu Path"),
    'description' => t("The menu path or alias form selected menu as token (Works at the moment only with nodes!)."),
  );

  return array(
    'types' => $types,
    'tokens' => array(
      'power_menu' => $tokens,
    ),
  );
}

/**
 * Implements hook_tokens().
 */
function power_menu_tokens($type, $tokens, array $data = array(), array $options = array()) {
  global $base_url;
  $replacements = array();

  if ($type == 'power_menu') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'menu_path':
          $alias = '';

          // TODO: Implement generic handling. Not only for nodes
          if(isset($data['node'])) {
            $path = power_menu_get_path($data['node'], 'node', 'node/' . $data['node']->nid, FALSE);
            $alias = drupal_get_path_alias($path);
          }

          $replacements[$original] = $alias;
          break;
      }
    }
  }

  return $replacements;
}

