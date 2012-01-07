<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

Yii::app()->clientScript->registerScript('deleteActionJs',"
function deleteAction(actionId) {

	if(confirm('".Yii::t('app','Are you sure you want to delete this item?')."')) {
		$.ajax({
			url: '" . CHtml::normalizeUrl(array('actions/delete')) . "/'+actionId+'?ajax=1',
			type: 'POST',
			//data: 'id='+actionId,
			success: function(response) {
				if(response=='Success')
					$('#history-'+actionId).fadeOut(200,function() { $('#history-'+actionId).remove(); });
				}
		});
	}
}
",CClientScript::POS_HEAD);

if(empty($data->type)) {
	if($data->complete=='Yes')
		$type = 'complete';
	else if($data->dueDate < time())
		$type = 'overdue';
	else
		$type = 'action';
} else
	$type = $data->type;



?>



<div class="view" id="history-<?php echo $data->id; ?>">
	<!--<div class="deleteButton">
		<?php //echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
	</div>-->
	<div class="icon <?php echo $type; ?>"></div>
	<div class="header">
		<?php
		if(empty($data->type)) {
			if ($data->complete=='Yes') {
				echo CHtml::link(Yii::t('actions','Action').':',array('actions/view','id'=>$data->id)).' ';
				echo Yii::t('actions','Completed {date}',array('{date}'=>Actions::formatDate($data->completeDate)));
			} else {
				echo '<b>'.CHtml::link(Yii::t('actions','Action').':',array('actions/view','id'=>$data->id)).' ';
				echo Actions::parseStatus($data->dueDate).'</b>';
			}
				
		} else if ($data->type == 'attachment') {
			if($data->completedBy=='Email')
				echo Yii::t('actions','Email Message:').' '.Actions::formatDate($data->completeDate);
			else
				echo Yii::t('actions','Attachment:').' '.Actions::formatDate($data->completeDate);
				//UserChild::getUserLinks($data->completedBy);
				
			echo ' ';
			
			//if ($data->complete=='Yes')
				//echo Actions::formatDate($data->completeDate);
			//else
				//echo Actions::parseStatus($data->dueDate);
		} else if ($data->type == 'workflow') {
			$actionData = explode(':',$data->actionDescription);
			
			$workflowRecord = CActiveRecord::model('Workflow')->findByPk($actionData[0]);
			$stageRecords = CActiveRecord::model('WorkflowStage')->findAllByAttributes(array('workflowId'=>$actionData[0]),new CDbCriteria(array('order'=>'id ASC')));
			
			echo Yii::t('workflow','Workflow:').'<b> '.$workflowRecord->name .'/'.$stageRecords[$actionData[1]-1]->name.'</b> ';
		}
		?>
		<div class="buttons">
			<?php
			if (empty($data->type)) {
				if ($data->complete=='Yes')
					echo CHtml::link('['.Yii::t('actions','Uncomplete').']',array('actions/uncomplete','id'=>$data->id,'redirect'=>1),array());
				else {
					echo CHtml::link('['.Yii::t('actions','Complete').']',array('actions/complete','id'=>$data->id,'redirect'=>1),array());
				}
				
				echo ' '.CHtml::link('['.Yii::t('actions','Update').']',array('actions/update','id'=>$data->id,'redirect'=>1),array()) . ' ';
			}
			if ($data->type != 'workflow')
				echo ' '.CHtml::link('[x]','#',array('onclick'=>'deleteAction('.$data->id.'); return false'));
			?>
		</div>
	</div>
	<div class="description">
		<?php
		if($type=='attachment' && $data->completedBy!='Email')
			echo MediaChild::attachmentActionText($this->convertUrls($data->actionDescription),true,true);
		else if($type=='workflow') {
		
			if(count($actionData) == 2) {
				if(count($workflowRecord) > 0 && $actionData[1] <= count($stageRecords)) {
					
					if($data->complete == 'Yes')
						echo ' <b>'.Yii::t('workflow','Completed').'</b> '.date('Y-m-d',$data->completeDate);
					else
						echo ' <b>'.Yii::t('workflow','Started').'</b> '.date('Y-m-d',$data->createDate);
				}
			}
		} else
			echo $this->convertUrls($data->actionDescription);	// convert LF and CRLF to <br />
		?>
	</div>
	<div class="footer">
	<?php if(empty($data->type) || $data->type=='workflow') {
		if ($data->complete == 'Yes') {
			echo Yii::t('actions','Completed by {name}',array('{name}'=>UserChild::getUserLinks($data->completedBy)));
		} else {
			$userLink = UserChild::getUserLinks($data->assignedTo);
			$userLink = empty($userLink)? Yii::t('actions','Anyone') : $userLink;
			echo Yii::t('actions','Assigned to {name}',array('{name}'=>$userLink));
		}
	} else if ($data->type == 'note') {
		echo UserChild::getUserLinks($data->completedBy);
		echo ' '.Actions::formatDate($data->completeDate);
	} else if ($data->type == 'attachment' && $data->completedBy!='Email') {
		echo Yii::t('media','Uploaded by {name}',array('{name}'=>UserChild::getUserLinks($data->completedBy)));
	}
	?>
	</div>

</div>