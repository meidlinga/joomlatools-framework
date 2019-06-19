<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Meta Template Filter
 *
 * Filter to parse meta tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Filter
 */
class KTemplateFilterMeta extends KTemplateFilterTag
{
    /**
     * Parse the text for script tags
     *
     * @param string $text  The text to parse
     * @return string
     */
    protected function _parseTags(&$text)
    {
        $tags = '';

        $matches = array();
        if(preg_match_all('#<meta(.*)\/>#siU', $text, $matches))
        {
            foreach($matches[1] as $key => $match)
            {
                $attribs = $this->parseAttributes( $match);
                $tags .= $this->_renderTag($attribs);
            }

            $text = str_replace($matches[0], '', $text);
        }

        return $tags;
    }

    /**
     * Render the tag
     *
     * @param   array   $attribs Associative array of attributes
     * @param   string  $content The tag content
     * @return string
     */
    protected function _renderTag($attribs = array(), $content = null)
    {
        $attribs = $this->buildAttributes($attribs);

        $html = '<meta'.$attribs.' />'."\n";
        return $html;
    }
}