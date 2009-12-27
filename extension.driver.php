<?php

	Class extension_ZenCoding extends Extension{

		public function about(){
			return array('name' => 'Zen Coding',
						 'version' => '1.0',
						 'release-date' => '2009-12-27',
						 'author' => array('name' => 'Simone Economo',
										   'website' => 'http://www.lineheight.net',
										   'email' => 'my.ekoes@gmail.com'),
						 'description' => 'Allows HTML/XSLT hi-speed coding inside Symphony\'s page template and utility editors.'
				 		);
		}

		public function getSubscribedDelegates(){
			return array(
//				array(
//					'page' => '/backend/',
//					'delegate' => 'ModifyTextareaFieldPublishWidget',
//					'callback' => '__zenitizeTextarea'
//				),
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__appendScripts'
				),
			);
		}

//		public function __zenitizeTextarea($context) {
//			$textarea = $context['textarea'];
//			$textarea->setAttribute('class', $textarea->getAttribute('class').'zc-use_tab-true zc-syntax-xsl zc-profile-xml');
//		}

		public function __appendScripts($context) {
			$pageCallback = $context['parent']->getPageCallback();
			if (
				   ($pageCallback['driver'] == 'blueprintspages' && $pageCallback['context']['0'] == 'template')
				|| ($pageCallback['driver'] == 'blueprintsutilities')
			) {
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zen_textarea.min.js', 1000, false);
				$context['parent']->Page->addScriptToHead(URL . '/extensions/zencoding/assets/zenitize.js', 1000, false);
			}
		}

	}

?>
