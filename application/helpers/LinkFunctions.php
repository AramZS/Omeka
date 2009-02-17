<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package OmekaThemes
 * @subpackage LinkHelpers
 **/

/**
 * Uses uri() to generate <a> tags for a given link.
 * 
 * @since 0.10 No longer escapes the text for the link.  This text must be valid
 * HTML.
 * @since 0.10 No longer prepends the word 'View' to the text of the link.  Instead
 * 'View' is the default text.
 *
 * @param Omeka_Record|string $record The name of the controller to use for the
 * link.  If a record instance is passed, then it inflects the name of the 
 * controller from the record class.
 * @param string $action The action to use for the link (optional)
 * @param string $text The text to put in the link.  Default is 'View'.
 * @param array $props Attributes for the <a> tag
 * @return string HTML
 **/
function link_to($record, $action=null, $text='View', $props = array())
{
    // If we're linking directly to a record, use the URI for that record.
    if($record instanceof Omeka_Record) {
        $url = record_uri($record, $action);
    }
    else {
        // Otherwise $record is the name of the controller to link to.
        $urlOptions = array();
        //Use Zend Framework's built-in 'default' route
        $route = 'default';
        $urlOptions['controller'] = (string) $record;
        if($action) $urlOptions['action'] = (string) $action;
        $url = uri($urlOptions, $route);
    }

	$attr = !empty($props) ? ' ' . _tag_attributes($props) : '';
	return '<a href="'. $url . '"' . $attr . '>' . $text . '</a>';
}

/**
 * @since 0.10 Function signature has changed so that the item to link to can be
 * determined by the context of the function call.  Also, text passed to the link
 * must be valid HTML (will not be automatically escaped because any HTML can be
 * passed in, e.g. an <img /> or the like).
 * 
 * @param string HTML for the text of the link.
 * @param array Properties for the <a> tag. (optional)
 * @param string The page to link to (this will be the 'show' page almost always
 * within the public theme).
 * @param Item Used for dependency injection testing or to use this function outside
 * the context of a loop.
 * @return string HTML
 **/
function link_to_item($text = null, $props = array(), $action = 'show', $item=null)
{
    if(!$item) {
        $item = get_current_item();
    }

	$text = (!empty($text) ? $text : strip_formatting(item('Dublin Core', 'Title')));
	
	return link_to($item, $action, $text, $props);
}

/**
 * @since 0.10 First argument is now the text of the link, 2nd argument are the 
 * query parameters to merge in to the href for the link.
 * 
 * @param string $text The text of the link.
 * @param array $params A set of query string parameters to merge in to the href
 * of the link.  E.g., if this link was clicked on the items/browse?collection=1
 * page, and array('foo'=>'bar') was passed as this argument, the new URI would be
 * items/browse?collection=1&foo=bar.
 */
function link_to_items_rss($text = 'RSS', $params=array())
{	
	return '<a href="' . items_output_uri('rss2', $params) . '" class="rss">' . $text . '</a>';
}

/**
 * Link to the item immediately following the current one.
 * 
 * @since 0.10 Signature has changed to reflect the use of get_current_item()
 * instead of passing the $item object as the first argument.
 * @uses get_current_item()
 * @uses link_to()
 * @return string
 **/
function link_to_next_item($text="Next Item --&gt;", $props=array())
{
    $item = get_current_item();
	if($next = $item->next()) {
		return link_to($next, 'show', $text, $props);
	}
}

/**
 * @see link_to_next_item()
 * @return string
 **/
function link_to_previous_item($text="&lt;-- Previous Item", $props=array())
{
    $item = get_current_item();
	if($previous = $item->previous()) {
		return link_to($previous, 'show', $text, $props);
	}
}

/**
 * 
 * @since 0.10 Signature has changed so that $text is the first argument.  Uses
 * get_current_collection() to determine what collection to link to.  Or you can 
 * pass it the Collection record as the last argument.
 * @param string $text Optional text to use for the title of the collection.  Default
 * behavior is to use the name of the collection.
 * @param array $props Set of attributes to use for the link.
 * @param array $action The action to link to for the collection.  Default is 'show'.
 * @param array $collectionObj Optional Collection record can be passed to this
 * to override the collection object retrieved by get_current_collection().
 * @return string
 **/
function link_to_collection($text=null, $props=array(), $action='show', $collectionObj = null)
{
    if (!$collectionObj) {
        $collectionObj = get_current_collection();
    }
    
	$text = (!empty($text) ? $text : (!empty($collectionObj->name) ? $collectionObj->name : '[Untitled]'));
	
	return link_to($collectionObj, $action, $text, $props);
}

/**
 * @deprecated Please use link_to_item(item_thumbnail()) instead.
 * @return string|false
 **/
function link_to_thumbnail($item, $props=array(), $action='show', $random=false)
{
    return _link_to_archive_image($item, $props, $action, $random, 'thumbnail');
}

/**
 * @deprecated Please use link_to_item(item_fullsize()) instead.
 * @return string|false
 **/
function link_to_fullsize($item, $props=array(), $action='show', $random=false)
{
    return _link_to_archive_image($item, $props, $action, $random, 'fullsize');
}

/**
 * @deprecated Please use link_to_item(item_square_thumbnail()) instead.
 * @return string|false
 **/
function link_to_square_thumbnail($item, $props=array(), $action='show', $random=false)
{
    return _link_to_archive_image($item, $props, $action, $random, 'square_thumbnail');
}

/**
 * Returns a link to an item, where the link has been populated by a specific image format for the item.
 * 
 * @deprecated Used internally by deprecated helpers, do not use this.
 * @access private
 * @return string|false
 **/
function _link_to_archive_image($item, $props=array(), $action='show', $random=false, $imageType = 'thumbnail')
{
	if(!$item or !$item->exists()) return false;
	
	$path = 'items/'.$action.'/' . $item->id;
	$output = '<a href="'. uri($path) . '" ' . _tag_attributes($props) . '>';
	
	if($random) {
		$output .= archive_image($item, array(), null, null, $imageType);
	}else {
		$output .= archive_image($item->Files[0], array(), null, null, $imageType);
	}
	$output .= '</a>';	
	
	return $output;
}

/**
 * 
 * @since 0.10 All arguments to this function are optional.  If no text is given,
 * it will automatically use the text for the 'site_title' option.
 * @since 0.10 The text passed to this function will not be automatically escaped
 * with htmlentities(), which allows for passing images or other HTML in place of text.
 * @return string
 **/
function link_to_home_page($text = null, $props = array())
{
    if (!$text) {
        $text = settings('site_title');
    }
	$uri = WEB_ROOT;
	return '<a href="'.$uri.'" '._tag_attributes($props).'>' . $text . "</a>\n";
}

/**
 * 
 * @since 0.10 Arguments follow the same pattern as link_to_home_page().
 * @see link_to_home_page()
 * @return string
 **/
function link_to_admin_home_page($text = null, $props = array())
{
    if (!$text) {
        $text = settings('site_title');
    }
	return '<a href="'.admin_uri('').'" '._tag_attributes($props).'>'. $text."</a>\n";
}