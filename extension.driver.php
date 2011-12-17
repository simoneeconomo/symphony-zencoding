<?php

	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.extensionmanager.php');

	Class extension_ZenCoding extends Extension{

#		const ALL_FIELDS = 'all';
#		const NO_FIELDS = 'none';

		public function about(){
			return array(
					'name' => 'Zen Coding',
					'version' => '1.2.1',
					'author' => array(
						'name' => 'Simone Economo',
						'website' => 'http://www.lineheight.net',
						'email' => 'my.ekoes@gmail.com'
					),
			);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendScripts'
				),
#				array(
#					'page' => '/backend/',
#					'delegate' => 'ModifyTextareaFieldPublishWidget',
#					'callback' => 'sectionFieldTextareas'
#				),
				array(
					'page' => '/administration/',
					'delegate' => 'AdminPagePostGenerate',
					'callback' => 'systemTextareas'
				),
#				array(
#					'page' => '/system/preferences/',
#					'delegate' => 'AddCustomPreferenceFieldsets',
#					'callback' => 'appendPreferences'
#				),
#				array(
#					'page' => '/system/preferences/',
#					'delegate' => 'Save',
#					'callback' => 'savePreferences'
#				),
			);
		}

#		private function __isAllowedField($id) {
#			$config = $this->_Parent->Configuration->get('allowed_fields', 'zen_coding');

#			if ($config == self::ALL_FIELDS)
#				return true;
#			else if ($config == self::NO_FIELDS)
#				return false;
#			else
#				return @in_array($id, explode(',', $config));
#		}

#		private function __isAllowedSection($id) {
#			$config = $this->_Parent->Configuration->get('allowed_fields', 'zen_coding');
#			$config = $config == null ? false : $config;

#			if ($config == self::ALL_FIELDS)
#				return @in_array($id, Symphony::Database()->fetchCol('parent_section',
#					"SELECT parent_section FROM tbl_fields WHERE type = 'textarea'"
#				));
#			else if ($config == self::NO_FIELDS)
#				return false;
#			else
#				return @in_array($id, Symphony::Database()->fetchCol('parent_section',
#					"SELECT parent_section FROM tbl_fields WHERE id IN (" . $config . ")"
#				));
#		}

#		private function __buildOptionsArray($context) {
#			$sectionManager = new SectionManager($context['parent']);
#			$sections = $sectionManager->fetch(NULL, 'ASC', 'sortorder');

#			$outline = array();

#			if(is_array($sections) && !empty($sections)){
#				foreach($sections as $section)
#					$outline[$section->get('id')] = array(
#						'name' => $section->get('name'),
#						'fields' => $section->fetchFields($type = "textarea"),
#					);
#			}

#			$options = array();
#			$options[] = array(self::ALL_FIELDS, false, __("All fields"));

#			foreach($outline as $section){
#				if(!is_array($section['fields'])) continue;

#				$fields = array();

#				foreach($section['fields'] as $field)
#					$fields[] = array($field->get('id'), $this->__isAllowedField($field->get('id')), $field->get('label'));

#				if(is_array($fields) && !empty($fields))
#					$options[] = array('label' => $section['name'], 'options' => $fields);
#			}

#			return $options;

#		}

		public function appendScripts($context) {
			$callback = $context['parent']->getPageCallback();

			if (in_array($callback['driver'], array('blueprintspages', 'blueprintsutilities')))
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);

			if ($callback['driver'] == 'publish') {
				$sectionManager = new SectionManager($context['parent']);
				$section_id = $sectionManager->fetchIDFromHandle($callback['context']['section_handle']);

				if ($this->__isAllowedSection($section_id))
					$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);
			}
		}

#		public function sectionFieldTextareas($context) {
#			if (!$this->__isAllowedField($context['field']->get('id'))) return;

#			$classes = $context['textarea']->getAttribute('class');
#			$context['textarea']->setAttribute('class', $classes .' zc-use_tab-true zc-syntax-xsl zc-profile-xml');

#			$c = $context['label']->getChildren();

#			if (empty($c)) {
#				$i = new XMLElement('i');
#				$context['label']->appendChild($i);
#			} else {
#				$i = $c[0];
#				$value  = ". " . $i->getValue();
#			}

#			$i->setValue('Zen Coding' . $value);
#		}

		public function systemTextareas($context) {

			$callback = $this->_Parent->getPageCallback();

			if (!in_array($callback['driver'], array('blueprintsutilities', 'blueprintspages'))) return;

			$dom = @DOMDocument::loadHTML($context['output']);
			$xpath = new DOMXPath($dom);

			$textarea = $xpath->query("//textarea")->item(0);
			$label = $textarea->parentNode;

			if (!$textarea) return;

			$classes = $textarea->attributes->getNamedItem('class');
			$classes->appendChild($dom->createTextNode(' zc-use_tab-true zc-syntax-xsl zc-profile-xml'));

			$i = $dom->createElement('i', 'Zen Coding');

			$label->appendChild($i);

			$context['output'] = $dom->saveHTML();
		}

#		public function savePreferences($context){
#			if(!is_array($context['settings']))
#				$context['settings'] = array('zen_coding' => array('allowed_fields' => self::NO_FIELDS));

#			elseif(!isset($context['settings']['zen_coding']))
#				$context['settings']['zen_coding'] = array('allowed_fields' => self::NO_FIELDS);

#			elseif(is_array($context['settings']['zen_coding']['allowed_fields'])) {

#				if(@in_array(self::ALL_FIELDS, $context['settings']['zen_coding']['allowed_fields']))
#					$context['settings']['zen_coding'] = array('allowed_fields' => self::ALL_FIELDS);
#				else
#					$context['settings']['zen_coding']['allowed_fields'] = implode(',', $context['settings']['zen_coding']['allowed_fields']);

#			}
#		}

#		public function appendPreferences($context){
#			$group = new XMLElement('fieldset');
#			$group->setAttribute('class', 'settings');
#			$group->appendChild(new XMLElement('legend', __('Zen Coding')));

#			$label = Widget::Label(__('Allowed fields'));
#			$options = $this->__buildOptionsArray($context);
#			$select = Widget::Select('settings[zen_coding][allowed_fields][]', $options, array('multiple' => 'multiple'));
#			$label->appendChild($select);

#			$group->appendChild($label);
#			$group->appendChild(new XMLElement('p', __('Zen Coding will be enabled on the selected fields above.'), array('class' => 'help')));

#			$context['wrapper']->appendChild($group);
#		}

	}

?>
