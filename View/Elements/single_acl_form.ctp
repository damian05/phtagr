<?php
  echo $this->ImageData->acl2select('Media.readPreview', $this->request->data, ACL_READ_PREVIEW, ACL_READ_MASK, array('label' => __("Who can view image?")));
  echo $this->ImageData->acl2select('Media.readOriginal', $this->request->data, ACL_READ_ORIGINAL, ACL_READ_MASK, array('label' => __("Who can download the image?")));
  echo $this->ImageData->acl2select('Media.writeTag', $this->request->data, ACL_WRITE_TAG, ACL_WRITE_MASK, array('label' => __("Who can edit the tags?")));
  echo $this->ImageData->acl2select('Media.writeMeta', $this->request->data, ACL_WRITE_META, ACL_WRITE_MASK, array('label' => __("Who can edit all meta data?")));
  echo $this->Form->input('Group.id', array('type' => 'select', 'options' => $groups, 'selected' => $this->request->data['Media']['group_id'], 'label' => __('Group')));
?>
