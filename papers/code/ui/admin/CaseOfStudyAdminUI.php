<?php
/**
 * Copyright 2018 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

class CaseOfStudyAdminUI extends DataExtension
{
    public function updateCMSFields(FieldList $f)
    {
        //clear all fields
        $oldFields = $f->toArray();
        foreach ($oldFields as $field) {
            $f->remove($field);
        }

        $f->add($rootTab = new TabSet("Root", $tabMain = new Tab('Main')));

        $f->addFieldToTab('Root.Main', new TextField('Title', 'Title'));

        if ($this->owner->ID > 0) {
            // contents
            $config = GridFieldConfig_RecordEditor::create(50);
            $config->addComponent($sort = new GridFieldSortableRows('Order'));
            $contents = new GridField('Contents', 'Contents', $this->owner->Contents(), $config);
            $f->addFieldToTab('Root.Main', $contents);
        }


        $logo_field = new UploadField('Logo', 'Logo');
        $logo_field->setAllowedMaxFileNumber(1);
        $logo_field->setAllowedFileCategories('image');
        $logo_field->setFolderName('papers/logos/cases_of_study');
        $logo_field->getValidator()->setAllowedMaxFileSize(1024*1024*1);
        $f->addFieldToTab('Root.Main', $logo_field);

        $f->addFieldToTab('Root.Main', new HiddenField('SectionID', 'SectionID'));


    }
}