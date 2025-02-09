<?php
/**
 * Folded text Plugin: enables folded text font size with syntax ++ text ++
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabian van-de-l_Isle <webmaster [at] lajzar [dot] co [dot] uk>
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Esther Brunner <esther@kaffeehaus.ch>
 */

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_folded_span extends DokuWiki_Syntax_Plugin {

    /** @var helper_plugin_folded */
    protected $helper = null;

    function getType(){ return 'formatting'; }
    function getAllowedTypes() { return array('substition','protected','disabled','formatting'); }
    function getSort(){ return 405; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('\+\+.*?\|(?=.*\+\+)',$mode,'plugin_folded_span'); }
    function postConnect() { $this->Lexer->addExitPattern('\+\+','plugin_folded_span'); }

   /**
    * Handle the match
    */
    function handle($match, $state, $pos, Doku_Handler $handler){
        if ($state == DOKU_LEXER_ENTER){
            $match = trim(substr($match,2,-1)); // strip markup
        } else if ($state == DOKU_LEXER_UNMATCHED) {
            $handler->_addCall('cdata',array($match), $pos);
            return false;
        }
        return array($state, $match);
    }

   /**
    * Create output
    */
    function render($mode, Doku_Renderer $renderer, $data) {
        if (empty($data)) return false;
        list($state, $cdata) = $data;

        if($mode == 'xhtml') {
            switch ($state){
               case DOKU_LEXER_ENTER:
                if ($this->helper === null) {
                    $this->helper = plugin_load('helper', 'folded');
                }
                $folded_id = $this->helper->getNextID();

                if ($this->getConf('unfold_default')) {
                    $renderer->doc .= '<a class="folder open" href="#'.$folded_id.'">';
                } else {
                    $renderer->doc .= '<a class="folder" href="#'.$folded_id.'">';
                }

                if ($cdata)
                    $renderer->doc .= ' '.$renderer->cdata($cdata);

                if ($this->getConf('unfold_default')) {
                    $renderer->doc .= '</a><span class="folded" id="'.$folded_id.'">';
                } else {
                    $renderer->doc .= '</a><span class="folded hidden" id="'.$folded_id.'">';
                }
                break;
                
              case DOKU_LEXER_UNMATCHED:
                $renderer->cdata($cdata);
                break;
                
              case DOKU_LEXER_EXIT:
                $renderer->doc .= '</span>';
                break;
            }
            return true;
        } else {
            if ($cdata) $renderer->cdata($cdata);
        }
        return false;
    }
}
