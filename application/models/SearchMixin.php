<?php
class SearchMixin extends Omeka_Record_Mixin
{
    protected $_text;
    protected $_public = true;
    
    public function __construct($record)
    {
        $this->_record = $record;
    }
    
    public function addSearchText($text)
    {
        $this->_text .= " $text";
    }
    
    public function setSearchTextPrivate()
    {
        $this->_public = false;
    }
    
    public function afterSave()
    {
        $recordName = get_class($this->_record);
        $searchText = $this->_record->getDb()->getTable('SearchText')->findByRecord($recordName, $this->_record->id);
        if (!$searchText) {
            $searchText = new SearchText;
            $searchText->record_name = $recordName;
            $searchText->record_id = $this->_record->id;
        }
        $searchText->public = $this->_public;
        $searchText->text = $this->_text;
        $searchText->save();
    }
}
