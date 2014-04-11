<?php

include_once 'PowerMenuHandler.interface.php';

/**
 * Implementation of the interface PowerMenuHandlerInterface.
 */
class PowerMenuTaxonomyHandler implements PowerMenuHandlerInterface {

  /**
   * @see PowerMenuHandlerInterface::configurationForm()
   */
  public function configurationForm() {
    $form = array();

    $form['note'] = array(
      '#markup' => '<p><strong>' . t('Choose the vocabulary which is going to reflect the structure of your menu.') . '</strong></p><p>' . t('You
        can associate each taxonomy therm from the selected vocabulary to a menu link. Has an entity a taxonomy term from this vocabulary associated,
        the plugin sets the active trail to the menu link associated with this taxonomy term.') . '</p>',
    );

    $taxonomies = taxonomy_get_vocabularies();
    $options = array();
    foreach ($taxonomies as $value) {
      $options[$value->vid] = $value->name;
    }

    $vocabulary = variable_get('power_menu_taxonomy_vocabulary', array('vid' => NULL, 'machine_name' => NULL));

    $form['power_menu_taxonomy']['vocabulary'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $vocabulary['vid'],
    );

    return $form;
  }

  /**
   * @see PowerMenuHandlerInterface::configurationFormSubmit()
   */
  public function configurationFormSubmit(array $form, array &$form_state) {

    $vocabulary = taxonomy_vocabulary_load($form_state['values']['vocabulary']);

    $vocabulary = array(
      'vid' => $vocabulary->vid,
      'machine_name' => $vocabulary->machine_name,
    );

    variable_set('power_menu_taxonomy_vocabulary', $vocabulary);
  }

  /**
   * @see PowerMenuHandlerInterface::configurationFormValidate()
   */
  public function configurationFormValidate(array &$elements, array &$form_state, $form_id = NULL) {

  }

  /**
   * @see PowerMenuHandlerInterface::getMenuPathToActivate()
   */
  public function getMenuPathToActivate($entity, $type, array $router_item, $alias) {
    $path = NULL;
    $mlid = NULL;
    $terms = variable_get('power_menu_taxonomy_terms', array());
    $entity_terms = PowerMenuTaxonomyHandler::getTaxonomyTermsFromEntity($entity, $type);

    // Search a mlid for a entity term
    foreach ($entity_terms as $value) {
      if (array_key_exists($value['tid'], $terms)) {
        $mlid = $terms[$value['tid']];
      }
    }

    if ($mlid != NULL) {
      $menu_link = menu_link_load($mlid);

      if ($menu_link) {
        $path = $menu_link['link_path'];
      }
    }

    return $path;
  }

  /**
   * @see PowerMenuHandlerInterface::menuFormAlter()
   */
  public function menuFormAlter(&$menu_item_form, &$form_state) {
    $form = array();

    $vocabulary = variable_get('power_menu_taxonomy_vocabulary', array('vid' => NULL, 'machine_name' => NULL));

    if ($vocabulary['vid'] !== NULL) {
      $vocabulary = taxonomy_vocabulary_load($vocabulary['vid']);
      $vocabulary_name = $vocabulary->name;

      $terms = taxonomy_get_tree($vocabulary->vid);

      $mlid = arg(4);
      $used_terms = variable_get('power_menu_taxonomy_terms', array());

      // Get only the used terms for this mlid
      foreach ($used_terms as $key => $value) {
        if ($mlid != $value) {
          unset($used_terms[$key]);
        }
      }
      // Only the key is needed for #default_value
      $used_terms = array_keys($used_terms);

      // Create the hierarchy and make sure that we mark the ones that are not selectable, because they already belong to an other menu item
      if ($terms) {
        foreach ($terms as $term) {
          $choice = new stdClass();
          $choice->option = array($term->tid => str_repeat('-', $term->depth) . ' ' . $term->name);
          $options[] = $choice;
        }
      }

      $form['power_menu_taxonomy_create'] = array(
        '#type' => 'checkbox',
        '#title' => t('Create Taxonomy term'),
        '#description' => t('The name of the taxonomy term is going to be the title of the menu link. Incase there is already a term with the same name
        in the vocabulary \'%vocabulary\', it\'s not going to be created.', array('%vocabulary' => $vocabulary_name)),
      );

      $form['power_menu_taxonomy_terms'] = array(
        '#type' => 'select',
        '#title' => t('Link to existing term'),
        '#multiple' => TRUE,
        '#options' => $options,
        '#default_value' => $used_terms,
        '#description' => t('Choose a taxonomy term. When displaying a node that has the selected taxonomy term, this menu item will set to active. A term can only belong to one menu item and is a term not selectable it is already asigned to other menu items.'),
        '#post_render' => array('power_menu_taxonomy_terms_post_render'), // Preprocess elements to disable used terms
      );
    }
    else {
      $form['power_menu_taxonomy'] = array(
        '#markup' => t('No menu vocabulary selected. !config_page', array('!config_page' => l(t('Go to the plugin configuration page.'), 'admin/config/search/power_menu/handler/edit/taxonomy'))),
      );
    }

    return $form;
  }

  /**
   * @see PowerMenuHandlerInterface::menuFormValidate()
   */
  public function menuFormValidate(array &$elements, array &$form_state, $form_id = NULL) {

  }

  /**
   * @see PowerMenuHandlerInterface::menuFormSubmit()
   */
  public function menuFormSubmit(array $form, array &$form_state) {
    $terms = variable_get('power_menu_taxonomy_terms', array());
    $vocabulary = variable_get('power_menu_taxonomy_vocabulary', array('vid' => NULL, 'machine_name' => NULL));

    // Add new term if necessary
    if ($vocabulary['vid'] !== NULL && $form_state['values']['power_menu_taxonomy_create']) {
      // Does a term with this name exists
      $term_name = $form_state['values']['link_title'];

      $term = db_select('taxonomy_term_data', 't')
        ->fields('t', array('tid'))
        ->condition('t.name', $term_name)
        ->condition('t.vid', $vocabulary['vid'])
        ->execute()->fetchField();

      if (!$term) {

        $term = new stdClass;
        $term->vid = $vocabulary['vid'];
        $term->name = $term_name;

        // Save the term and add it to the selected terms
        taxonomy_term_save($term);
        $form_state['values']['power_menu_taxonomy_terms'][] = $term->tid;
      }
    }

    if (isset($form_state['values']['power_menu_taxonomy_terms'])) {
      $selected_terms = $form_state['values']['power_menu_taxonomy_terms'];
      $mlid = $form_state['values']['mlid'];

      // First delete all used terms with given mlid
      foreach ($terms as $key => $value) {
        if ($value == $mlid) {
          unset($terms[$key]);
        }
      }

      // Add new selected terms with this mlid
      foreach ($selected_terms as $value) {
        $terms[$value] = $mlid;
      }
    }

    variable_set('power_menu_taxonomy_terms', $terms);
  }

  /**
   * @see PowerMenuHandlerInterface::menuLinkDelete()
   */
  public function menuLinkDelete(array $link) {

    $terms = variable_get('power_menu_taxonomy_terms', array());
    $terms_to_save = array();

    // Remove terms with given menu link id
    foreach($terms as $tid => $mlid) {
      if($mlid != $link['mlid']) {
        $terms_to_save[$tid] = $mlid;
      }
    }

    variable_set('power_menu_taxonomy_terms',$terms_to_save);
  }

  /**
   * Returns all fields with a taxonomy reference to the selected menu taxonomy vocabulary.
   *
   * @param $type
   *   Return only fields for this entity type
   * @return
   *   An array of field definitions
   */
  public static function getTaxonomyFieldsFromEntity($type = FALSE) {
    $vocabulary = variable_get('power_menu_taxonomy_vocabulary', array('vid' => NULL, 'machine_name' => NULL));

    $fileds = field_info_fields();

    // Remove not taxonomy related fields and taxonomy fields that belongs to other vocabularies or entity types
    foreach ($fileds as $field_name => $value) {

      if ($value['type'] != 'taxonomy_term_reference'
        || $value['settings']['allowed_values'][0]['vocabulary'] != $vocabulary['machine_name']
        || ($type) && empty($value['bundles'][$type])
      ) {

        unset($fileds[$field_name]);
      }
    }
    return $fileds;
  }

  /**
   *  Returns an array of taxonomy terms associated with tis entity.
   *
   * @param $entity
   *  The entity
   * @param $type
   *   The entity type
   * @return
   *   An array of terms
   */
  public static function getTaxonomyTermsFromEntity($entity, $type) {
    $terms = array();

    $fileds = PowerMenuTaxonomyHandler::getTaxonomyFieldsFromEntity($type);

    foreach ($fileds as $field_name => $value) {
      if (!empty($entity->$field_name)) {
        $items = field_get_items($type, $entity, $field_name);
        $terms = array_merge($terms, $items);
      }
    }

    return $terms;
  }
}
