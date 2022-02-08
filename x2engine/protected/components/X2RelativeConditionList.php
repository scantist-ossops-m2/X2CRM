<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * Generic condition list form which enables user specification of conditions on model properties.
 * User specified conditions can be retrieved through the front-end X2ConditionList API (see 
 * X2ConditionList.js).
 */

class X2RelativeConditionList extends X2Widget {

    /**
     * @var string $id id of container element
     */
    public $id;

    /**
     * @var string $name condition list input name
     */
    public $name; 

    /**
     * @var array $value conditions already added
     */
    public $value; 

    /**
     * @var X2Model $model model whose attributes should be used to populate attribute list
     */
    public $model;

    /**
     * @var bool $useLinkedModels if true, add field options for related models
     */
    public $useLinkedModels = false;

    /**
     * @var array (optional) Used to instantiate JS X2ConditionList class. If not specified, this
     *  value will default to return value of {@link X2Model::getFieldsForDropdown}
     */
    public $attributes;
    
     /**
     * @var array this array will be used to hold all the different time options
     */
   


    /**
     * @var array $_packages
     */
    protected $_packages;

    public static function listOption ($attributes, $name) {
        if ($attributes instanceof Fields) {
            $attributes = $attributes->getAttributes ();
        }
        $data = array(
            'name' => $name,
            'label' => $attributes['attributeLabel'],
        );

        if(isset ($attributes['type']) && $attributes['type'])
            $data['type'] = $attributes['type'];
        if(isset ($attributes['required']) && $attributes['required'])
            $data['required'] = 1;
        if(isset ($attributes['readOnly']) && $attributes['readOnly'])
            $data['readOnly'] = 1;
        if(isset ($attributes['type'])) {
           if (($attributes['type'] === 'assignment' || 
                $attributes['type'] === 'optionalAssignment')) {
               $data['options'] = AuxLib::dropdownForJson(
                   X2Model::getAssignmentOptions(true, true));
            } elseif ($attributes['type'] === 'dropdown' && isset ($attributes['linkType'])) {
                $data['linkType'] = $attributes['linkType'];
                $data['options'] = AuxLib::dropdownForJson(
                    Dropdowns::getItems($attributes['linkType']));
            } elseif ($attributes['type'] === 'link' && isset ($attributes['linkType'])) {
                $staticLinkModel = X2Model::model($attributes['linkType']);
                if(array_key_exists('LinkableBehavior', $staticLinkModel->behaviors())) {
                    $data['linkType'] = $attributes['linkType']; 
                    $data['linkSource'] = Yii::app()->controller->createUrl(
                        $staticLinkModel->autoCompleteSource);
                }
            }
        }

        return $data;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'X2Fields' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2Fields.js',
                        'js/X2FieldsGeneric.js',
                        'js/jquery-ui-timepicker-addon.js',
                    ),
                    'depends' => array ('jquery.ui')
                ),
                'X2RelativeConditionListJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2RelativeConditionList.js',
                    ),
                    'depends' => array ('auxlib', 'X2Fields')
                ),
            );
        }
        return $this->_packages;
    }

    public function init () {
        if (!$this->attributes) {
            
            $TimeFunc = function ($field){

                if($field->type == "date" || $field->type == "dateTime"){
                        return TRUE;

                } else {
                        return FALSE;

                }

            };
            
            $this->attributes = $this->model->getFieldsForRelativeDropdown ($this->useLinkedModels, TRUE, $TimeFunc);
        }
    }

    public function run () {
 
        foreach($this->attributes as $keyone => $TheArray){
            foreach($TheArray as $keytwo => $att){
                $this->attributes[$keyone][$keytwo]["type"] = 'dropdown';
                $this->attributes[$keyone][$keytwo]["options"] = array(

                    array(
                        'Current CY',
                        'Current CY'
                    ),
                    array(
                        'Previous CY',
                        'Previous CY'
                    ),
                    array(
                        '2 CY Ago',
                        '2 CY Ago'
                    ),
                    array(
                        'Next CY',
                        'Next CY'
                    ),
                    array(
                        'Current CQ',
                        'Current CQ'
                    ),
                    array(
                        'Next CQ',
                        'Next CQ'
                    ),
                    array(
                        'Previous CQ',
                        'Previous CQ'
                    ),
                    array(
                        'Last Month',
                        'Last Month'
                    ),
                    array(
                        'This Month',
                        'This Month'
                    ),
                    array(
                        'Next Month',
                        'Next Month'
                    ),
                    array(
                        'Last Week',
                        'Last Week'
                    ),
                    array(
                        'This Week',
                        'This Week'
                    ),
                    array(
                        'Next Week',
                        'Next Week'
                    ),
                    array(
                        'Yesterday',
                        'Yesterday'
                    ),
                    array(
                        'Today',
                        'Today'
                    ),
                    array(
                        'Tomorrow',
                        'Tomorrow'
                    ),
                    array(
                        'Last 7 Days',
                        'Last 7 Days'
                    ),
                    array(
                        'Last 30 Days',
                        'Last 30 Days'
                    ),
                    array(
                        'Last 60 Days',
                        'Last 60 Days'
                    ),
                    array(
                        'Last 90 Days',
                        'Last 90 Days'
                    ),
                    array(
                        'Last 120 Days',
                        'Last 120 Days'
                    ),
                    array(
                        'Next 7 Days',
                        'Next 7 Days'
                    ),
                    array(
                        'Next 30 Days',
                        'Next 30 Days'
                    ),
                    array(
                        'Next 60 Days',
                        'Next 60 Days'
                    ),
                    array(
                        'Next 90 Days',
                        'Next 90 Days'
                    ),
                    array(
                        'Next 120 Days',
                        'Next 120 Days'
                    ),
                   

                );
            }
        }
       
        $this->registerPackages ();
        $this->render ('x2RelativeConditionList');
    }

}
