<?php

/**
 * Standardowe tagi
 * @package Parser
 * @subpackage Filters
 * @author wookieb
 * @version 1.2
 */
class BbCodeFilterBasic {
	public $tags = array(
		'b' => array(
			'open' => 'b',
			'close' => 'b',
			'notallowed_child' => array('b')
		),
		'i' => array(
			'open' => 'i',
			'close' => 'i',
			'notallowed_child' => array('i')
		),
		's' => array(
			'open' => 'span class="s"', // podalismy w htmlu bo niepotrzebne jest uzywanie atrybutow jak np zakomentowanych nizej
			'close' => 'span',
			'notallowed_child' => array('s'),
		/* 'attributes'=>array(
		  'dec'=>array(
		  'no_changeable' => true,
		  'attr'			=> 'style',
		  'name'          => 'text-decoration:',
		  'default_value' => 'line-through'
		  )
		  ) */
		),
		'u' => array(
			'open' => 'span class="u"',
			'close' => 'span',
			'notallowed_child' => array('u')
		),
		'color' => array(
			'open' => 'span',
			'close' => 'span',
			'attributes' => array(
				'color' => array(
					'attr' => 'style',
					'type' => 'string',
					'name' => 'color:',
					'required' => true
				)
			)
		),
		'size' => array(
			'open' => 'span',
			'close' => 'span',
			'attributes' => array(
				'size' => array(
					'attr' => 'style',
					'type' => 'number',
					'name' => 'font-size:',
					'dimensions' => array(
						'px' => array(
							'min_value' => 10,
							'max_value' => 16
						),
						'pt' => array(
							'min_value' => 5,
							'max_value' => 14
						)
					),
					'default_dimension' => 'px'
				)
			)
		),
		'quote' => array(
			'open' => 'div class="quote_wrapper"',
			'close' => 'div',
			'parse_body' => 'parseQuote',
			'attributes' => array(
				'quote' => array(
					'type' => 'string'
				)
			)
		)
	);

	/**
	 * Parsuje tag QUOTE
	 * @param array $tag
	 * @param array $openNode
	 * @param array $body
	 * @param array $closeNode
	 * @param BbCodeSettings $settings
	 */
	public function parseQuote($tag, $openNode, $body, &$closeNode, $settings) {
		$divText = '';
		if (isset($openNode['attributes'])) {
			if (isset($openNode['attributes']['tag_attributes']['quote'])) {
				global $pdo;
				$nick = mysql_escape_string($openNode['attributes']['tag_attributes']['quote']);
				$SQL = 'SELECT `userId` FROM `users` WHERE `username` = "'.$nick.'"';
				$QUERY = $pdo->query($SQL);
				if($QUERY->rowCount())
					$divText.='<a href="profil/'.$nick.'">'.$nick.'</a>';
				else
					$divText.=$nick.'';
				
			}

			if (isset($openNode['attributes']['tag_attributes']['date'])) {
				$dateExpr = '/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}$/';
				if (preg_match($dateExpr, $openNode['attributes']['tag_attributes']['date'])) {
					$divText.='<small>('.$openNode['attributes']['tag_attributes']['date'].')</small> ';
				}
				else {
					unset($openNode['attributes']['tag_attributes']['date']);
					$openNode = BbCode::rebuildNode($tag, $openNode, $settings);
				}
			}
		}
		$openNode['text'].='<div class="quote_title">Cytat '.$divText.'</div><div class="quote_area">';
		$closeNode['text'] = '</div>'.$closeNode['text'];
	}
}

?>