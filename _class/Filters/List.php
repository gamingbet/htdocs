<?php
     
/**
 * Obsługa list (popularna na forach)
 * @package Parser
 * @subpackage Filters
 * @author LapKom
 * @version 0.1
 */
class BbCodeFilterList
{
public $tags=array(
    'list'=>array(
    'open'=>'ul',
    'close'=>'ul',
    'wrap_white_space'=>true,
    'allowed_child'=>array('*','l'),
    'notallowed_parent'=>array('list'),
    'parse_body'=>'parseList',
    'attributes'=>array(
    'list'=>array(
    'attr'=>'style',
    'type'=>'string',
    'values'=>array(
    '1'
    ),
    ),
    ),
    ),
    '*'=>array(
    'open'=>'li',
    'close'=>'li',
    'allowed_parent'=>array('list'),
    ),
    );
     
    /**
    * Parsuje LISTY
    * @param array $tag
    * @param array $openNode
    * @param array $body
    * @param array $closeNode
    * @param BbCodeSettings $settings
    */
    public function parseList($tag, &$openNode, &$body, &$closeNode, $settings)
    {	
    if(isset($openNode['attributes']['tag_attributes']['list'])) {
    $listStyleType = $openNode['attributes']['tag_attributes']['list'];
    switch ($listStyleType) {
    case '1' : $type = 'decimal'; break;
    case '01' : $type = 'decimal-leading-zero'; break;
    case 'a' : $type = 'lower-alpha'; break;
    case 'A' : $type = 'upper-alpha'; break;
    case 'i' : $type = 'lower-roman'; break;
    case 'I' : $type = 'upper-roman'; break;
    }
    $listStyleType = $openNode['attributes']['tag_attributes']['list'] = 'list-style-type:'.$type;
    $openNode=BbCode::rebuildNode($tag, $openNode, $settings);
    $openNode['text'] = str_replace('ul','ol',$openNode['text']);
    $closeNode['text'] = str_replace('ul','ol',$closeNode['text']);
    }
    }
    }
     
    ?>