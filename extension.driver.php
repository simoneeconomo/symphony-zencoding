<?php

	require_once(DOCROOT . '/symphony/lib/toolkit/class.sectionmanager.php');

	Class extension_ZenCoding extends Extension{

		const ALL_FIELDS = 'all';
		const NO_FIELDS = 'none';

		public function about(){
			return array('name' => 'Zen Coding',
						 'version' => '1.1',
						 'release-date' => '2010-01-01',
						 'author' => array('name' => 'Simone Economo',
										   'website' => 'http://www.lineheight.net',
										   'email' => 'my.ekoes@gmail.com'),
						 'description' => 'Flavours Symphony\'s textarea fields with an incredibly easy-to-use, CSS-like syntax for writing HTML, XML & XSLT code'
				 		);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'savePreferences'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'ModifyTextareaFieldPublishWidget',
					'callback' => '__zenitizeTextarea'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__appendScripts'
				),
			);
		}

		public function __isAllowedField($id) {
			$config = $this->_Parent->Configuration->get('allowed_fields', 'zen_coding');

			if ($config == self::ALL_FIELDS)
				return true;
			else if ($config == self::NO_FIELDS)
				return false;
			else
				return @in_array($id, explode(',', $config));
		}

		public function __isAllowedSection($id) {
			$config = $this->_Parent->Configuration->get('allowed_fields', 'zen_coding');

			if ($config == self::ALL_FIELDS)
				return @in_array($id, Symphony::Database()->fetchCol('parent_section', "SELECT parent_section FROM tbl_fields WHERE type = 'textarea'"));
			else if ($config == self::NO_FIELDS)
				return false;
			else
				return @in_array($id, Symphony::Database()->fetchCol('parent_section', "SELECT parent_section FROM tbl_fields WHERE id IN (" . $config . ")"));
		}

		public function __zenitizeTextarea($context) {
			if ($this->__isAllowedField($context['field']->get('id'))) {
				$classes = $context['textarea']->getAttribute('class');
				$context['textarea']->setAttribute('class', $classes .' zc-use_tab-true zc-syntax-xsl zc-profile-xml');

				$c = $context['label']->getChildren();

				if (empty($c)) {
					$i = new XMLElement('i');
					$context['label']->appendChild($i);
				}
				else {
					$i = $c[0];
					$value  = ". " . $i->getValue();
				}

				$i->setValue(__('Zen Coding is enabled (<a href=\'%1$s\' title="%2$s">?</a>)',
					array('http://code.google.com/p/zen-coding/', 'More details about Zen Coding and features enabled')
				) . $value);
			}
		}

		public function __isTemplateEditor($pageCallback) {
			return $pageCallback['driver'] == 'blueprintspages' && $pageCallback['context']['0'] == 'template';
		}

		public function __isUtilityEditor($pageCallback) {
			return $pageCallback['driver'] == 'blueprintsutilities';
		}

		public function __isEntryEditor($pageCallback) {
			return $pageCallback['driver'] == 'publish';
		}

		public function __appendScripts($context) {
			$callback = $context['parent']->getPageCallback();

			if ($this->__isTemplateEditor($callback)) {
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zenitize.js', 1000, false);
			}
			else if ($this->__isUtilityEditor($callback)) {
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zenitize.js', 1000, false);
			}
			else if ($this->__isEntryEditor($callback)) {
				$sectionManager = new SectionManager($context['parent']);
				$section_id = $sectionManager->fetchIDFromHandle($callback['context']['section_handle']);

				if ($this->__isAllowedSection($section_id))
					$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);
			}
		}

		public function savePreferences($context){
			if(!is_array($context['settings']))
				$context['settings'] = array('zen_coding' => array('allowed_fields' => self::NO_FIELDS));
			elseif(!isset($context['settings']['zen_coding']))
				$context['settings']['zen_coding'] = array('allowed_fields' => self::NO_FIELDS);
			elseif(is_array($context['settings']['zen_coding']['allowed_fields'])) {
				if(@in_array(self::ALL_FIELDS, $context['settings']['zen_coding']['allowed_fields']))
					$context['settings']['zen_coding'] = array('allowed_fields' => self::ALL_FIELDS);
				else
					$context['settings']['zen_coding']['allowed_fields'] = implode(',', $context['settings']['zen_coding']['allowed_fields']);
			}
		}

		public function __buildOptionsArray($context) {
			$sectionManager = new SectionManager($context['parent']);
			$sections = $sectionManager->fetch(NULL, 'ASC', 'sortorder');

			$outline = array();

			if(is_array($sections) && !empty($sections)){
				foreach($sections as $section)
					$outline[$section->get('id')] = array(
						'name' => $section->get('name'),
						'fields' => $section->fetchFields($type="textarea"),
					);
			}

			$options = array();
			$options[] = array(self::ALL_FIELDS, false, __("All fields"));

			foreach($outline as $section){
				if(!is_array($section['fields'])) continue;

				$fields = array();

				foreach($section['fields'] as $field)
					$fields[] = array($field->get('id'), $this->__isAllowedField($field->get('id')), $field->get('label'));

				if(is_array($fields) && !empty($fields))
					$options[] = array('label' => $section['name'], 'options' => $fields);
			}

			return $options;

		}

		public function appendPreferences($context){
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Zen Coding')));

			$label = Widget::Label(__('Allowed fields'));
			$options = $this->__buildOptionsArray($context);
			$select = Widget::Select('settings[zen_coding][allowed_fields][]', $options, array('multiple' => 'multiple'));
			$label->appendChild($select);

			$group->appendChild($label);
			$group->appendChild(new XMLElement('p', __('This will let you use Zen Coding in Textarea-based fields of your sections.'), array('class' => 'help')));

			$context['wrapper']->appendChild($group);
		}

	}

?>
