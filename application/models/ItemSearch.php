<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/

/**
 * @package Omeka
 * @subpackage Models
 * @author CHNM
 * @copyright Center for History and New Media, 2007-2008
 **/
class ItemSearch
{
    protected $_select;
    
    /**
     * Constructor.  Adds a SQL_CALC_FOUND_ROWS column to the sql statement
     * 
     * @param Zend_Db_Select
     * @return void
     **/
    public function __construct($select)
    {   
        $this->_select = $select;
    }
    
    public function getSelect()
    {
        return $this->_select;
    }
    
    public function getDb()
    {
        return Omeka_Context::getInstance()->getDb();
    }
    
    /**
     * The trail of this function:
     *     items_search_form() form helper  --> ItemsController::browseAction()  
     * --> ItemTable::findBy() --> here
     *
     * @return void
     **/
    public function advanced($advanced)
    {
        $db = $this->getDb();

        $select = $this->getSelect();
                        
        foreach ($advanced as $k => $v) {
            
            $value = $v['terms'];
            
            // SELECT i.* FROM omeka_items i
            // WHERE i.id IN (
            //     SELECT i.id FROM omeka_items i
            //         LEFT JOIN omeka_items_elements ie 
            //         ON ie.item_id = i.id AND ie.element_id = 3
            //         WHERE
            //             ie.text IS NULL)
            // 
            //     AND i.id IN (
            //         ...
            //         WHERE 
            //         ie.text LIKE '%foo%'
            // 
            //     )

            //Determine what the WHERE clause should look like
            switch ($v['type']) {
                case 'contains':
                    $predicate = "LIKE " . $db->quote('%'.$value .'%');
                    break;
                case 'does not contain':
                    $predicate = "NOT LIKE " . $db->quote('%'.$value .'%');
                    break;
                case 'is empty':    
                    $predicate = "IS NULL";
                    break;
                case 'is not empty':
                    $predicate = "IS NOT NULL";
                    break;
                default:
                    throw new Exception( 'Invalid search type given!' );
                    break;
            }
            
            $elementId = (int) $v['element_id'];
            
            // This does not use Omeka_Db_Select b/c there is no conditional SQL
            // and it is easier to read without all the extra cruft.
            $subQuery = "SELECT i.id FROM $db->Item i 
                        LEFT JOIN $db->ElementText etx 
                        ON etx.record_id = i.id AND etx.element_id = " . $db->quote($elementId) ."
                        LEFT JOIN $db->RecordType rty
                        ON etx.record_type_id = rty.id AND rty.name = 'Item'
                        WHERE etx.text $predicate";
            
            // Each advanced search mini-form represents another subquery
           $select->where('i.id IN ( ' . (string) $subQuery . ' )'); 

        }

    }
    
    /**
     * Search query consists of a derived table that is INNER JOIN'ed to
     * the main SQL query.  That derived table is a union of two SELECT
     * queries.  The first query searches the FULLTEXT index on the 
     * items_elements table, and the second query searches the tags table
     * for every word in the search terms and assigns each found result 
     * a rank of '1'. That should make tagged items show up higher on the found
     * results list for a given search.
     *
     * @return void
     **/    
    public function simple($terms)
    {
        $db = $this->getDb();
        $select = $this->getSelect();
        
        /*
        SELECT i.*, s.rank
        FROM items i
        INNER JOIN 
        (
            SELECT i.id as item_id, MATCH (ie.text) AGAINST ('foo bar') as rank
            FROM items i
            INNER JOIN items_elements ie ON ie.item_id = i.id
            WHERE MATCH (ie.text) AGAINST ('foo bar')
            UNION 
            SELECT i.id as item_id, 1 as rank
            FROM items i
            INNER JOIN taggings tg ON (tg.relation_id = i.id AND tg.type = "Item")
            INNER JOIN tags t ON t.id = tg.tag_id
            WHERE (t.name = 'foo' OR t.name = 'bar')
        ) s ON s.item_id = i.id
        */
        
        $searchQuery  = (string) $this->_getElementsQuery($terms) . " UNION ";
        $searchQuery .= (string) $this->_getTagsQuery($terms);
                
        // INNER JOIN to the main SQL query and then ORDER BY rank DESC
        $select->joinInner(array('s'=>new Zend_Db_Expr('('. $searchQuery . ')')), 's.item_id = i.id', array())
            ->order('s.rank DESC'); 
                        
    }
    
    protected function _getElementsQuery($terms)
    {
        $db = $this->getDb();
        $quotedTerms = $db->quote($terms);
                
        // This doesn't really need to use a Select object because this query
        // is not dynamic.  
        $query = "
            SELECT i.id as item_id, MATCH (etx.text) AGAINST ($quotedTerms) as rank
            FROM $db->Item i 
            INNER JOIN $db->ElementText etx ON etx.record_id = i.id
            INNER JOIN $db->RecordType rty ON rty.id = etx.record_type_id AND rty.name = 'Item'
            WHERE MATCH (etx.text) AGAINST ($quotedTerms)";
        
        return $query;
    }
    
    protected function _getTagsQuery($terms)
    {
        $db = $this->getDb();
        
        $rank = 1;

        $tagList = preg_split('/\s+/', $terms);
        //Also make sure the tag list contains the whole search string, just in case that is found
        $tagList[] = $terms; 
            
        $select = new Omeka_Db_Select;
        $select->from( array('i'=>$db->Item), array('item_id'=>'i.id', 'rank'=>new Zend_Db_Expr($rank)))
            ->joinInner( array('tg'=>$db->Taggings), 'tg.relation_id = i.id AND tg.type = "Item"', array())
            ->joinInner( array('t'=>$db->Tag), 't.id = tg.tag_id', array());
            
        foreach ($tagList as $tag) {
            $select->orWhere('t.name LIKE ?', $tag);
        }
        
        return $select;
    }
    
}