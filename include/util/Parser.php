<?php

namespace HyperDown;

/**
 * Parser
 *
 * @copyright Copyright (c) 2012 SegmentFault Team. (http://segmentfault.com)
 * @author Joyqi <joyqi@segmentfault.com>
 * @license BSD License
 */
class Parser
{
    /** change by xeonds:recover this to public
     * _whiteList
     *
     * @var string
     */
    public $_commonWhiteList = 'kbd|b|i|strong|em|sup|sub|br|code|del|a|hr|small';

    /**
     * html tags
     *
     * @var string
     */
    private $_blockHtmlTags = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|svg|script|noscript';

    /** change by xeonds:recover this to public
     * _specialWhiteList
     *
     * @var mixed
     * @access private
     */
    public $_specialWhiteList = array(
        'table'  =>  'table|tbody|thead|tfoot|tr|td|th'
    );

    /**
     * _footnotes
     *
     * @var array
     */
    private $_footnotes;

    /**
     * @var bool
     */
    private $_html = false;

    /**
     * @var bool
     */
    private $_line = false;

    /**
     * @var array
     */
    private $blockParsers = array(
        array('code', 10),
        array('shtml', 20),
        array('pre', 30),
        array('ahtml', 40),
        array('shr', 50),
        array('list', 60),
        array('math', 70),
        array('html', 80),
        array('footnote', 90),
        array('definition', 100),
        array('quote', 110),
        array('table', 120),
        array('sh', 130),
        array('mh', 140),
        array('dhr', 150),
        array('default', 9999)
    );

    /**
     * _blocks
     *
     * @var array
     */
    private $_blocks;

    /**
     * _current
     *
     * @var string
     */
    private $_current;

    /**
     * _pos
     *
     * @var int
     */
    private $_pos;

    /**
     * _definitions
     *
     * @var array
     */
    private $_definitions;

    /**
     * @var array
     */
    private $_hooks = array();

    /**
     * @var array
     */
    private $_holders;

    /**
     * @var string
     */
    private $_uniqid;

    /**
     * @var int
     */
    private $_id;

    /**
     * @var array
     */
    private $_parsers = array();

    /**
     * makeHtml
     *
     * @param mixed $text
     * @return string
     */
    public function makeHtml($text)
    {
        $this->_footnotes = array();
        $this->_definitions = array();
        $this->_holders = array();
        $this->_uniqid = md5(uniqid());
        $this->_id = 0;

        usort($this->blockParsers, function ($a, $b)
        {
            return $a[1] < $b[1] ? -1 : 1;
        });

<<<<<<< HEAD:core/util/Parser.php
        foreach ($this->blockParsers as $parser)
        {
=======
        foreach ($this->blockParsers as $parser) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            list($name) = $parser;

            if (isset($parser[2]))
            {
                $this->_parsers[$name] = $parser[2];
            }
            else
            {
                $this->_parsers[$name] = array($this, 'parseBlock' . ucfirst($name));
            }
        }

        $text = $this->initText($text);
        $html = $this->parse($text);
        $html = $this->makeFootnotes($html);
        $html = $this->optimizeLines($html);

        return $this->call('makeHtml', $html);
    }

    /**
     * @param $html
     */
    public function enableHtml($html = true)
    {
        $this->_html = $html;
    }

    /**
     * @param bool $line
     */
    public function enableLine($line = true)
    {
        $this->_line = $line;
    }

    /**
     * @param $type
     * @param $callback
     */
    public function hook($type, $callback)
    {
        $this->_hooks[$type][] = $callback;
    }

    /**
     * @param $str
     * @return string
     */
    public function makeHolder($str)
    {
        $key = "\r" . $this->_uniqid . $this->_id . "\r";
        $this->_id++;
        $this->_holders[$key] = $str;

        return $key;
    }

    /**
     * @param $text
     * @return mixed
     */
    private function initText($text)
    {
        $text = str_replace(array("\t", "\r"),  array('    ', ''),  $text);
        return $text;
    }

    /**
     * @param $html
     * @return string
     */
    private function makeFootnotes($html)
    {
        if (count($this->_footnotes) > 0)
        {
            $html .= '<div class="footnotes"><hr><ol>';
            $index = 1;

            while ($val = array_shift($this->_footnotes))
            {
                if (is_string($val))
                {
                    $val .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                }
                else
                {
                    $val[count($val) - 1] .= " <a href=\"#fnref-{$index}\" class=\"footnote-backref\">&#8617;</a>";
                    $val = count($val) > 1 ? $this->parse(implode("\n", $val)) : $this->parseInline($val[0]);
                }

                $html .= "<li id=\"fn-{$index}\">{$val}</li>";
                $index++;
            }

            $html .= '</ol></div>';
        }

        return $html;
    }

    /**
     * parse
     *
     * @param string $text
     * @param bool $inline
     * @param int $offset
     * @return string
     */
    private function parse($text, $inline = false, $offset = 0)
    {
        $blocks = $this->parseBlock($text, $lines);
        $html = '';

        // inline mode for single normal block
        if ($inline && count($blocks) == 1 && $blocks[0][0] == 'normal')
        {
            $blocks[0][3] = true;
        }

<<<<<<< HEAD:core/util/Parser.php
        foreach ($blocks as $block)
        {
=======
        foreach ($blocks as $block) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            list($type, $start, $end, $value) = $block;
            $extract = array_slice($lines, $start, $end - $start + 1);
            $method = 'parse' . ucfirst($type);

            $extract = $this->call('before' . ucfirst($method), $extract, $value);
            $result = $this->{$method}($extract, $value, $start + $offset, $end + $offset);
            $result = $this->call('after' . ucfirst($method), $result, $value);

            $html .= $result;
        }

        return $html;
    }

    /**
     * @param $text
     * @param $clearHolders
     * @return string
     */
    private function releaseHolder($text, $clearHolders = true)
    {
        $deep = 0;
        while (strpos($text, "\r") !== false && $deep < 10)
        {
            $text = str_replace(array_keys($this->_holders), array_values($this->_holders), $text);
            $deep++;
        }

        if ($clearHolders)
        {
            $this->_holders = array();
        }

        return $text;
    }

    /**
     * @param $start
     * @param int $end
     * @return string
     */
    private function markLine($start, $end = -1)
    {
        if ($this->_line)
        {
            $end = $end < 0 ? $start : $end;
            return '<span class="line" data-start="' . $start
                . '" data-end="' . $end . '" data-id="' . $this->_uniqid . '"></span>';
        }

        return '';
    }

    /**
     * @param array $lines
     * @param $start
     * @return string[]
     */
    private function markLines(array $lines, $start)
    {
        $i = -1;

<<<<<<< HEAD:core/util/Parser.php
        return $this->_line ? array_map(function ($line) use ($start, &$i)
        {
            $i++;
=======
        return $this->_line ? array_map(function ($line) use ($start, &$i) {
            $i ++;
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            return $this->markLine($start + $i) . $line;
        }, $lines) : $lines;
    }

    /**
     * @param $html
     * @return string
     */
    private function optimizeLines($html)
    {
        $last = 0;

        return $this->_line ?
            preg_replace_callback(
                "/class=\"line\" data\-start=\"([0-9]+)\" data\-end=\"([0-9]+)\" (data\-id=\"{$this->_uniqid}\")/",
                function ($matches) use (&$last)
                {
                    if ($matches[1] != $last)
                    {
                        $replace = 'class="line" data-start="' . $last . '" data-start-original="' . $matches[1] . '" data-end="' . $matches[2] . '" ' . $matches[3];
                    }
                    else
                    {
                        $replace = $matches[0];
                    }

                    $last = $matches[2] + 1;
                    return $replace;
                },
                $html
            ) : $html;
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    private function call($type, $value)
    {
        if (empty($this->_hooks[$type]))
        {
            return $value;
        }

        $args = func_get_args();
        $args = array_slice($args, 1);

        foreach ($this->_hooks[$type] as $callback)
        {
            $value = call_user_func_array($callback, $args);
            $args[0] = $value;
        }

        return $value;
    }

    /**
     * parseInline
     *
     * @param string $text
     * @param string $whiteList
     * @param bool $clearHolders
     * @param bool $enableAutoLink
     * @return string
     */
    private function parseInline($text, $whiteList = '', $clearHolders = true, $enableAutoLink = true)
    {
        $text = $this->call('beforeParseInline', $text);

        // code
        $text = preg_replace_callback(
            "/(^|[^\\\])(`+)(.+?)\\2/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  $matches[1] . $this->makeHolder(
                    '<code>' . htmlspecialchars($matches[3]) . '</code>'
                );
            },
            $text
        );

        // mathjax
        $text = preg_replace_callback(
            "/(^|[^\\\])(\\$+)(.+?)\\2/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  $matches[1] . $this->makeHolder(
                    $matches[2] . htmlspecialchars($matches[3]) . $matches[2]
                );
            },
            $text
        );

        // escape
        $text = preg_replace_callback(
            "/\\\(.)/u",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $prefix = preg_match("/^[-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]$/", $matches[1]) ? '' : '\\';
                $escaped = htmlspecialchars($matches[1]);
                $escaped = str_replace('$', '&dollar;', $escaped);
                return  $this->makeHolder($prefix . $escaped);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/<(https?:\/\/.+|(?:mailto:)?[_a-z0-9-\.\+]+@[_\w-]+(?:\.[a-z]{2,})+)>/i",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $url = $this->cleanUrl($matches[1]);
                $link = $this->call('parseLink', $url);

                return $this->makeHolder(
                    "<a href=\"{$url}\">{$link}</a>"
                );
            },
            $text
        );

        // encode unsafe tags
        $text = preg_replace_callback(
            "/<(\/?)([a-z0-9-]+)(\s+[^>]*)?>/i",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches) use ($whiteList)
            {
                if ($this->_html || false !== stripos(
                    '|' . $this->_commonWhiteList . '|' . $whiteList . '|',
                    '|' . $matches[2] . '|'
                ))
                {
                    return $this->makeHolder($matches[0]);
                }
                else
                {
=======
            function ($matches) use ($whiteList) {
                if ($this->_html || false !== stripos(
                    '|' . $this->_commonWhiteList . '|' . $whiteList . '|', '|' . $matches[2] . '|'
                )) {
                    return $this->makeHolder($matches[0]);
                } else {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    return $this->makeHolder(htmlspecialchars($matches[0]));
                }
            },
            $text
        );

<<<<<<< HEAD:core/util/Parser.php
        if ($this->_html)
        {
            $text = preg_replace_callback("/<!\-\-(.*?)\-\->/", function ($matches)
            {
=======
        if ($this->_html) {
            $text = preg_replace_callback("/<!\-\-(.*?)\-\->/", function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return $this->makeHolder($matches[0]);
            }, $text);
        }

        $text = str_replace(array('<', '>'),  array('&lt;', '&gt;'),  $text);

        // footnote
        $text = preg_replace_callback(
            "/\[\^((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
                $id = array_search($matches[1], $this->_footnotes);

                if (false === $id)
                {
=======
            function ($matches) {
                $id = array_search($matches[1], $this->_footnotes);

                if (false === $id) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $id = count($this->_footnotes) + 1;
                    $this->_footnotes[$id] = $this->parseInline($matches[1], '', false);
                }

                return $this->makeHolder(
                    "<sup id=\"fnref-{$id}\"><a href=\"#fn-{$id}\" class=\"footnote-ref\">{$id}</a></sup>"
                );
            },
            $text
        );

        // image
        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
                $escaped = htmlspecialchars($this->escapeBracket($matches[1]));
                $url = $this->escapeBracket($matches[2]);
                list($url, $title) = $this->cleanUrl($url, true);
                $title = empty($title) ? $escaped : " title=\"{$title}\"";
=======
            function ($matches) {
                $escaped = htmlspecialchars($this->escapeBracket($matches[1]));
                $url = $this->escapeBracket($matches[2]);
                list ($url, $title) = $this->cleanUrl($url, true);
                $title = empty($title)? $escaped : " title=\"{$title}\"";
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php

                return $this->makeHolder(
                    "<img src=\"{$url}\" alt=\"{$title}\" title=\"{$title}\">"
                );
            },
            $text
        );

        $text = preg_replace_callback(
            "/!\[((?:[^\]]|\\\\\]|\\\\\[)*?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
                $escaped = htmlspecialchars($this->escapeBracket($matches[1]));

                $result = isset($this->_definitions[$matches[2]]) ?
=======
            function ($matches) {
                $escaped = htmlspecialchars($this->escapeBracket($matches[1]));

                $result = isset( $this->_definitions[$matches[2]] ) ?
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    "<img src=\"{$this->_definitions[$matches[2]]}\" alt=\"{$escaped}\" title=\"{$escaped}\">"
                    : $escaped;

                return $this->makeHolder($result);
            },
            $text
        );

        // link
        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\(((?:[^\)]|\\\\\)|\\\\\()+?)\)/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
                $escaped = $this->parseInline(
                    $this->escapeBracket($matches[1]),
                    '',
                    false,
                    false
                );
                $url = $this->escapeBracket($matches[2]);
                list($url, $title) = $this->cleanUrl($url, true);
=======
            function ($matches) {
                $escaped = $this->parseInline(
                    $this->escapeBracket($matches[1]),  '',  false, false
                );
                $url = $this->escapeBracket($matches[2]);
                list ($url, $title) = $this->cleanUrl($url, true);
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $title = empty($title) ? '' : " title=\"{$title}\"";

                return $this->makeHolder("<a href=\"{$url}\"{$title}>{$escaped}</a>");
            },
            $text
        );

        $text = preg_replace_callback(
            "/\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]\[((?:[^\]]|\\\\\]|\\\\\[)+?)\]/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
                $escaped = $this->parseInline(
                    $this->escapeBracket($matches[1]),
                    '',
                    false
                );
                $result = isset($this->_definitions[$matches[2]]) ?
=======
            function ($matches) {
                $escaped = $this->parseInline(
                    $this->escapeBracket($matches[1]),  '',  false
                );
                $result = isset( $this->_definitions[$matches[2]] ) ?
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    "<a href=\"{$this->_definitions[$matches[2]]}\">{$escaped}</a>"
                    : $escaped;

                return $this->makeHolder($result);
            },
            $text
        );

        // strong and em and some fuck
        $text = $this->parseInlineCallback($text);
        $text = preg_replace(
            "/<([_a-z0-9-\.\+]+@[^@]+\.[a-z]{2,})>/i",
            "<a href=\"mailto:\\1\">\\1</a>",
            $text
        );

        // autolink url
        if ($enableAutoLink)
        {
            $text = preg_replace_callback(
                "/(^|[^\"])(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)|(?:mailto:)?[_a-z0-9-\.\+]+@[_\w-]+(?:\.[a-z]{2,})+)($|[^\"])/",
<<<<<<< HEAD:core/util/Parser.php
                function ($matches)
                {
=======
                function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $url = $this->cleanUrl($matches[2]);
                    $link = $this->call('parseLink', $matches[2]);
                    return "{$matches[1]}<a href=\"{$url}\">{$link}</a>{$matches[5]}";
                },
                $text
            );
        }

        $text = $this->call('afterParseInlineBeforeRelease', $text);
        $text = $this->releaseHolder($text, $clearHolders);

        $text = $this->call('afterParseInline', $text);

        return $text;
    }

    /**
     * @param $text
     * @return mixed
     */
    private function parseInlineCallback($text)
    {
        $text = preg_replace_callback(
            "/(\*{3})(.+?)\\1/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  '<strong><em>' .
                    $this->parseInlineCallback($matches[2]) .
                    '</em></strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*{2})(.+?)\\1/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  '<strong>' .
                    $this->parseInlineCallback($matches[2]) .
                    '</strong>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\*)(.+?)\\1/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  '<em>' .
                    $this->parseInlineCallback($matches[2]) .
                    '</em>';
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{3})(.+?)\\2(\s+|$)/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  $matches[1] . '<strong><em>' .
                    $this->parseInlineCallback($matches[3]) .
                    '</em></strong>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_{2})(.+?)\\2(\s+|$)/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  $matches[1] . '<strong>' .
                    $this->parseInlineCallback($matches[3]) .
                    '</strong>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(\s+|^)(_)(.+?)\\2(\s+|$)/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  $matches[1] . '<em>' .
                    $this->parseInlineCallback($matches[3]) .
                    '</em>' . $matches[4];
            },
            $text
        );

        $text = preg_replace_callback(
            "/(~{2})(.+?)\\1/",
<<<<<<< HEAD:core/util/Parser.php
            function ($matches)
            {
=======
            function ($matches) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                return  '<del>' .
                    $this->parseInlineCallback($matches[2]) .
                    '</del>';
            },
            $text
        );

        return $text;
    }

    /**
     * parseBlock
     *
     * @param string $text
     * @param array $lines
     * @return array
     */
    private function parseBlock($text, &$lines)
    {
        $lines = explode("\n", $text);
        $this->_blocks = array();
        $this->_current = 'normal';
        $this->_pos = -1;

        $state = array(
            'special'   =>  implode("|", array_keys($this->_specialWhiteList)),
            'empty'     =>  0,
            'html'      =>  false
        );

        // analyze by line
        foreach ($lines as $key => $line)
        {
            $block = $this->getBlock();
            $args = array($block, $key, $line, &$state, $lines);

            if ($this->_current != 'normal')
            {
                $pass = call_user_func_array($this->_parsers[$this->_current], $args);

                if (!$pass)
                {
                    continue;
                }
            }

            foreach ($this->_parsers as $name => $parser)
            {
                if ($name != $this->_current)
                {
                    $pass = call_user_func_array($parser, $args);

                    if (!$pass)
                    {
                        break;
                    }
                }
            }
        }

        return $this->optimizeBlocks($this->_blocks, $lines);
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockList($block, $key, $line, &$state)
    {
<<<<<<< HEAD:core/util/Parser.php
        if ($this->isBlock('list') && !preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/", $line))
        {
            if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line))
            {
                // ignore code
                return true;
            }
            elseif (
                $state['empty'] <= 1
                && preg_match("/^(\s*)\S+/", $line, $matches)
                && strlen($matches[1]) >= ($block[3][0] + $state['empty'])
            )
            {
=======
        if ($this->isBlock('list') && !preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/", $line)) {
            if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line)) {
                // ignore code
                return true;
            } elseif ($state['empty'] <= 1
                && preg_match("/^(\s*)\S+/", $line, $matches)
                && strlen($matches[1]) >= ($block[3][0] + $state['empty'])) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php

                $state['empty'] = 0;
                $this->setBlock($key);
                return false;
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif (preg_match("/^(\s*)$/", $line) && $state['empty'] == 0)
            {
                $state['empty']++;
=======
            } elseif (preg_match("/^(\s*)$/", $line) && $state['empty'] == 0) {
                $state['empty'] ++;
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->setBlock($key);
                return false;
            }
        }

        if (preg_match("/^(\s*)((?:[0-9]+\.)|\-|\+|\*)\s+/i", $line, $matches))
        {
            $space = strlen($matches[1]);
            $tab = strlen($matches[0]) - $space;
            $state['empty'] = 0;
            $type = false !== strpos('+-*', $matches[2]) ? 'ul' : 'ol';

            // opened
<<<<<<< HEAD:core/util/Parser.php
            if ($this->isBlock('list'))
            {
                if ($space < $block[3][0] || ($space == $block[3][0] && $type != $block[3][1]))
                {
                    $this->startBlock('list', $key, [$space, $type, $tab]);
                }
                else
                {
                    $this->setBlock($key);
                }
            }
            else
            {
=======
            if ($this->isBlock('list')) {
                if ($space < $block[3][0] || ($space == $block[3][0] && $type != $block[3][1])) {
                    $this->startBlock('list', $key, [$space, $type, $tab]);
                } else {
                    $this->setBlock($key);
                }
            } else {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->startBlock('list', $key, [$space, $type, $tab]);
            }

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockCode($block, $key, $line, &$state)
    {
<<<<<<< HEAD:core/util/Parser.php
        if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line, $matches))
        {
            if ($this->isBlock('code'))
            {
                if ($state['code'] != $matches[2])
                {
=======
        if (preg_match("/^(\s*)(~{3,}|`{3,})([^`~]*)$/i", $line, $matches)) {
            if ($this->isBlock('code')) {
                if ($state['code'] != $matches[2]) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $this->setBlock($key);
                    return false;
                }

                $isAfterList = $block[3][2];

<<<<<<< HEAD:core/util/Parser.php
                if ($isAfterList)
                {
=======
                if ($isAfterList) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $state['empty'] = 0;
                    $this->combineBlock()
                        ->setBlock($key);
                }
                else
                {
                    $this->setBlock($key)
                        ->endBlock();
                }
            }
            else
            {
                $isAfterList = false;

<<<<<<< HEAD:core/util/Parser.php
                if ($this->isBlock('list'))
                {
=======
                if ($this->isBlock('list')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $space = $block[3][0];

                    $isAfterList = strlen($matches[1]) >= $space + $state['empty'];
                }

                $state['code'] = $matches[2];

                $this->startBlock('code', $key, array(
                    $matches[1],  $matches[3],  $isAfterList
                ));
            }

            return false;
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('code'))
        {
=======
        } elseif ($this->isBlock('code')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $this->setBlock($key);
            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockShtml($block, $key, $line, &$state)
    {
        if ($this->_html)
        {
            if (preg_match("/^(\s*)!!!(\s*)$/", $line, $matches))
            {
                if ($this->isBlock('shtml'))
                {
                    $this->setBlock($key)->endBlock();
                }
                else
                {
                    $this->startBlock('shtml', $key);
                }

                return false;
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif ($this->isBlock('shtml'))
            {
=======
            } elseif ($this->isBlock('shtml')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->setBlock($key);
                return false;
            }
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockAhtml($block, $key, $line, &$state)
    {
        if ($this->_html)
        {
            if (preg_match("/^\s*<({$this->_blockHtmlTags})(\s+[^>]*)?>/i", $line, $matches))
            {
                if ($this->isBlock('ahtml'))
                {
                    $this->setBlock($key);
                    return false;
<<<<<<< HEAD:core/util/Parser.php
                }
                elseif (empty($matches[2]) || $matches[2] != '/')
                {
=======
                } elseif (empty($matches[2]) || $matches[2] != '/') {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $this->startBlock('ahtml', $key);
                    preg_match_all("/<({$this->_blockHtmlTags})(\s+[^>]*)?>/i", $line, $allMatches);
                    $lastMatch = $allMatches[1][count($allMatches[0]) - 1];

                    if (strpos($line, "</{$lastMatch}>") !== false)
                    {
                        $this->endBlock();
                    }
                    else
                    {
                        $state['html'] = $lastMatch;
                    }
                    return false;
                }
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif (!!$state['html'] && strpos($line, "</{$state['html']}>") !== false)
            {
                $this->setBlock($key)->endBlock();
                $state['html'] = false;
                return false;
            }
            elseif ($this->isBlock('ahtml'))
            {
                $this->setBlock($key);
                return false;
            }
            elseif (preg_match("/^\s*<!\-\-(.*?)\-\->\s*$/", $line, $matches))
            {
=======
            } elseif (!!$state['html'] && strpos($line, "</{$state['html']}>") !== false) {
                $this->setBlock($key)->endBlock();
                $state['html'] = false;
                return false;
            } elseif ($this->isBlock('ahtml')) {
                $this->setBlock($key);
                return false;
            } elseif (preg_match("/^\s*<!\-\-(.*?)\-\->\s*$/", $line, $matches)) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->startBlock('ahtml', $key)->endBlock();
                return false;
            }
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockMath($block, $key, $line)
    {
        if (preg_match("/^(\s*)\\$\\$(\s*)$/", $line, $matches))
        {
            if ($this->isBlock('math'))
            {
                $this->setBlock($key)->endBlock();
            }
            else
            {
                $this->startBlock('math', $key);
            }

            return false;
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('math'))
        {
=======
        } elseif ($this->isBlock('math')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $this->setBlock($key);
            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockPre($block, $key, $line, &$state)
    {
        if (preg_match("/^ {4}/", $line))
        {
            if ($this->isBlock('pre'))
            {
                $this->setBlock($key);
            }
            else
            {
                $this->startBlock('pre', $key);
            }

            return false;
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('pre') && preg_match("/^\s*$/", $line))
        {
=======
        } elseif ($this->isBlock('pre') && preg_match("/^\s*$/", $line)) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $this->setBlock($key);
            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockHtml($block, $key, $line, &$state)
    {
        if (preg_match("/^\s*<({$state['special']})(\s+[^>]*)?>/i", $line, $matches))
        {
            $tag = strtolower($matches[1]);
            if (!$this->isBlock('html', $tag) && !$this->isBlock('pre'))
            {
                $this->startBlock('html', $key, $tag);
            }

            return false;
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif (preg_match("/<\/({$state['special']})>\s*$/i", $line, $matches))
        {
=======
        } elseif (preg_match("/<\/({$state['special']})>\s*$/i", $line, $matches)) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $tag = strtolower($matches[1]);

            if ($this->isBlock('html', $tag))
            {
                $this->setBlock($key)
                    ->endBlock();
            }

            return false;
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('html'))
        {
=======
        } elseif ($this->isBlock('html')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $this->setBlock($key);
            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockFootnote($block, $key, $line)
    {
        if (preg_match("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", $line, $matches))
        {
            $space = strlen($matches[0]) - 1;
            $this->startBlock('footnote', $key, array(
                $space, $matches[1]
            ));

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockDefinition($block, $key, $line)
    {
        if (preg_match("/^\s*\[((?:[^\]]|\\]|\\[)+?)\]:\s*(.+)$/", $line, $matches))
        {
            $this->_definitions[$matches[1]] = $this->cleanUrl($matches[2]);
            $this->startBlock('definition', $key)
                ->endBlock();

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockQuote($block, $key, $line)
    {
        if (preg_match("/^(\s*)>/", $line, $matches))
        {
            if ($this->isBlock('list') && strlen($matches[1]) > 0)
            {
                $this->setBlock($key);
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif ($this->isBlock('quote'))
            {
=======
            } elseif ($this->isBlock('quote')) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->setBlock($key);
            }
            else
            {
                $this->startBlock('quote', $key);
            }

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @param $lines
     * @return bool
     */
    private function parseBlockTable($block, $key, $line, &$state, $lines)
    {
        if (preg_match("/^((?:(?:(?:\||\+)(?:[ :]*\-+[ :]*)(?:\||\+))|(?:(?:[ :]*\-+[ :]*)(?:\||\+)(?:[ :]*\-+[ :]*))|(?:(?:[ :]*\-+[ :]*)(?:\||\+))|(?:(?:\||\+)(?:[ :]*\-+[ :]*)))+)$/", $line, $matches))
        {
            if ($this->isBlock('table'))
            {
                $block[3][0][] = $block[3][2];
                $block[3][2]++;
                $this->setBlock($key, $block[3]);
            }
            else
            {
                $head = 0;

                if (
                    empty($block) ||
                    $block[0] != 'normal' ||
                    preg_match("/^\s*$/", $lines[$block[2]])
                )
                {
                    $this->startBlock('table', $key);
                }
                else
                {
                    $head = 1;
                    $this->backBlock(1, 'table');
                }

                if ($matches[1][0] == '|')
                {
                    $matches[1] = substr($matches[1], 1);

                    if ($matches[1][strlen($matches[1]) - 1] == '|')
                    {
                        $matches[1] = substr($matches[1], 0, -1);
                    }
                }

                $rows = preg_split("/(\+|\|)/", $matches[1]);
                $aligns = array();
                foreach ($rows as $row)
                {
                    $align = 'none';

                    if (preg_match("/^\s*(:?)\-+(:?)\s*$/", $row, $matches))
                    {
                        if (!empty($matches[1]) && !empty($matches[2]))
                        {
                            $align = 'center';
<<<<<<< HEAD:core/util/Parser.php
                        }
                        elseif (!empty($matches[1]))
                        {
                            $align = 'left';
                        }
                        elseif (!empty($matches[2]))
                        {
=======
                        } elseif (!empty($matches[1])) {
                            $align = 'left';
                        } elseif (!empty($matches[2])) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                            $align = 'right';
                        }
                    }

                    $aligns[] = $align;
                }

                $this->setBlock($key, array(array($head), $aligns, $head + 1));
            }

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockSh($block, $key, $line)
    {
        if (preg_match("/^(#+)(.*)$/", $line, $matches))
        {
            $num = min(strlen($matches[1]), 6);
            $this->startBlock('sh', $key, $num)
                ->endBlock();

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @param $lines
     * @return bool
     */
    private function parseBlockMh($block, $key, $line, &$state, $lines)
    {
        if (
            preg_match("/^\s*((=|-){2,})\s*$/", $line, $matches)
            && ($block && $block[0] == "normal" && !preg_match("/^\s*$/", $lines[$block[2]]))
        )
        {    // check if last line isn't empty
            if ($this->isBlock('normal'))
            {
                $this->backBlock(1, 'mh', $matches[1][0] == '=' ? 1 : 2)
                    ->setBlock($key)
                    ->endBlock();
            }
            else
            {
                $this->startBlock('normal', $key);
            }

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockShr($block, $key, $line)
    {
        if (preg_match("/^(\* *){3,}\s*$/", $line))
        {
            $this->startBlock('hr', $key)
                ->endBlock();

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @return bool
     */
    private function parseBlockDhr($block, $key, $line)
    {
        if (preg_match("/^(- *){3,}\s*$/", $line))
        {
            $this->startBlock('hr', $key)
                ->endBlock();

            return false;
        }

        return true;
    }

    /**
     * @param $block
     * @param $key
     * @param $line
     * @param $state
     * @return bool
     */
    private function parseBlockDefault($block, $key, $line, &$state)
    {
        if ($this->isBlock('footnote'))
        {
            preg_match("/^(\s*)/", $line, $matches);
            if (strlen($matches[1]) >= $block[3][0])
            {
                $this->setBlock($key);
            }
            else
            {
                $this->startBlock('normal', $key);
            }
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('table'))
        {
            if (false !== strpos($line, '|'))
            {
                $block[3][2]++;
=======
        } elseif ($this->isBlock('table')) {
            if (false !== strpos($line, '|')) {
                $block[3][2] ++;
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->setBlock($key, $block[3]);
            }
            else
            {
                $this->startBlock('normal', $key);
            }
<<<<<<< HEAD:core/util/Parser.php
        }
        elseif ($this->isBlock('quote'))
        {
            if (!preg_match("/^(\s*)$/", $line))
            { // empty line
=======
        } elseif ($this->isBlock('quote')) {
            if (!preg_match("/^(\s*)$/", $line)) { // empty line
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $this->setBlock($key);
            }
            else
            {
                $this->startBlock('normal', $key);
            }
        }
        else
        {
            if (empty($block) || $block[0] != 'normal')
            {
                $this->startBlock('normal', $key);
            }
            else
            {
                $this->setBlock($key);
            }
        }

        return true;
    }

    /**
     * @param array $blocks
     * @param array $lines
     * @return array
     */
    private function optimizeBlocks(array $blocks, array $lines)
    {
        $blocks = $this->call('beforeOptimizeBlocks', $blocks, $lines);

        $key = 0;
        while (isset($blocks[$key]))
        {
            $moved = false;

            $block = &$blocks[$key];
            $prevBlock = isset($blocks[$key - 1]) ? $blocks[$key - 1] : NULL;
            $nextBlock = isset($blocks[$key + 1]) ? $blocks[$key + 1] : NULL;

            list($type, $from, $to) = $block;

            if ('pre' == $type)
            {
                $isEmpty = array_reduce(
                    array_slice($lines, $block[1], $block[2] - $block[1] + 1),
                    function ($result, $line)
                    {
                        return preg_match("/^\s*$/", $line) && $result;
                    },
                    true
                );

                if ($isEmpty)
                {
                    $block[0] = $type = 'normal';
                }
            }

            if ('normal' == $type)
            {
                // combine two blocks
                $types = array('list', 'quote');

<<<<<<< HEAD:core/util/Parser.php
                if (
                    $from == $to && preg_match("/^\s*$/", $lines[$from])
                    && !empty($prevBlock) && !empty($nextBlock)
                )
                {
                    if (
                        $prevBlock[0] == $nextBlock[0] && in_array($prevBlock[0], $types)
                        && ($prevBlock[0] != 'list'
                            || ($prevBlock[3][0] == $nextBlock[3][0] && $prevBlock[3][1] == $nextBlock[3][1]))
                    )
                    {
=======
                if ($from == $to && preg_match("/^\s*$/", $lines[$from])
                    && !empty($prevBlock) && !empty($nextBlock)) {
                    if ($prevBlock[0] == $nextBlock[0] && in_array($prevBlock[0], $types)
                        && ($prevBlock[0] != 'list'
                            || ($prevBlock[3][0] == $nextBlock[3][0] && $prevBlock[3][1] == $nextBlock[3][1]))) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                        // combine 3 blocks
                        $blocks[$key - 1] = array(
                            $prevBlock[0],  $prevBlock[1],  $nextBlock[2], $prevBlock[3] ?? null
                        );
                        array_splice($blocks, $key, 2);

                        // do not move
                        $moved = true;
                    }
                }
            }

            if (!$moved)
            {
                $key++;
            }
        }

        return $this->call('afterOptimizeBlocks', $blocks, $lines);
    }

    /**
     * parseCode
     *
     * @param array $lines
     * @param array $parts
     * @param int $start
     * @return string
     */
    private function parseCode(array $lines, array $parts, $start)
    {
        list($blank, $lang) = $parts;
        $lang = trim($lang);
        $count = strlen($blank);

        if (!preg_match("/^[_a-z0-9-\+\#\:\.]+$/i", $lang))
        {
            $lang = NULL;
        }
        else
        {
            $parts = explode(':', $lang);
<<<<<<< HEAD:core/util/Parser.php
            if (count($parts) > 1)
            {
=======
            if (count($parts) > 1) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                list($lang, $rel) = $parts;
                $lang = trim($lang);
                $rel = trim($rel);
            }
        }

        $isEmpty = true;

        $lines = array_map(function ($line) use ($count, &$isEmpty)
        {
            $line = preg_replace("/^[ ]{{$count}}/", '', $line);
            if ($isEmpty && !preg_match("/^\s*$/", $line))
            {
                $isEmpty = false;
            }

            return htmlspecialchars($line);
        }, array_slice($lines, 1, -1));
        $str = implode("\n", $this->markLines($lines, $start + 1));

        return $isEmpty ? '' :
            '<pre><code' . (!empty($lang) ? " class=\"{$lang}\"" : '')
            . (!empty($rel) ? " rel=\"{$rel}\"" : '') . '>'
            . $str . '</code></pre>';
    }

    /**
     * parsePre
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @return string
     */
    private function parsePre(array $lines, $value, $start)
    {
        foreach ($lines as &$line)
        {
            $line = htmlspecialchars(substr($line, 4));
        }

        $str = implode("\n", $this->markLines($lines, $start));
        return preg_match("/^\s*$/", $str) ? '' : '<pre><code>' . $str . '</code></pre>';
    }

    /**
     * parseAhtml
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @return string
     */
    private function parseAhtml(array $lines, $value, $start)
    {
        return trim(implode("\n", $this->markLines($lines, $start)));
    }

    /**
     * parseShtml
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @return string
     */
    private function parseShtml(array $lines, $value, $start)
    {
        return trim(implode("\n", $this->markLines(array_slice($lines, 1, -1), $start + 1)));
    }

    /**
     * parseMath
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @param int $end
     * @return string
     */
    private function parseMath(array $lines, $value, $start, $end)
    {
        return '<p>' . $this->markLine($start, $end) . htmlspecialchars(implode("\n", $lines)) . '</p>';
    }

    /**
     * parseSh
     *
     * @param array $lines
     * @param int $num
     * @param int $start
     * @param int $end
     * @return string
     */
    private function parseSh(array $lines, $num, $start, $end)
    {
        $line = $this->markLine($start, $end) . $this->parseInline(trim($lines[0], '# '));
        return preg_match("/^\s*$/", $line) ? '' : "<h{$num}>{$line}</h{$num}>";
    }

    /**
     * parseMh
     *
     * @param array $lines
     * @param int $num
     * @param int $start
     * @param int $end
     * @return string
     */
    private function parseMh(array $lines, $num, $start, $end)
    {
        return $this->parseSh($lines, $num, $start, $end);
    }

    /**
     * parseQuote
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @return string
     */
    private function parseQuote(array $lines, $value, $start)
    {
        foreach ($lines as &$line)
        {
            $line = preg_replace("/^\s*> ?/", '', $line);
        }
        $str = implode("\n", $lines);

        return preg_match("/^\s*$/", $str) ? '' : '<blockquote>' . $this->parse($str, true, $start) . '</blockquote>';
    }

    /**
     * parseList
     *
     * @param array $lines
     * @param mixed $value
     * @param int $start
     * @return string
     */
    private function parseList(array $lines, $value, $start)
    {
        $html = '';
        list($space, $type, $tab) = $value;
        $rows = array();
        $suffix = '';
        $last = 0;

<<<<<<< HEAD:core/util/Parser.php
        foreach ($lines as $key => $line)
        {
            if (preg_match("/^(\s{" . $space . "})((?:[0-9]+\.?)|\-|\+|\*)(\s+)(.*)$/i", $line, $matches))
            {
                if ($type == 'ol' && $key == 0)
                {
                    $start = intval($matches[2]);

                    if ($start != 1)
                    {
=======
        foreach ($lines as $key => $line) {
            if (preg_match("/^(\s{" . $space . "})((?:[0-9]+\.?)|\-|\+|\*)(\s+)(.*)$/i", $line, $matches)) {
                if ($type == 'ol' && $key == 0) {
                    $start = intval($matches[2]);

                    if ($start != 1) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                        $suffix = ' start="' . $start . '"';
                    }
                }

                $rows[] = [$matches[4]];
                $last = count($rows) - 1;
<<<<<<< HEAD:core/util/Parser.php
            }
            else
            {
=======
            } else {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $rows[$last][] = preg_replace("/^\s{" . ($tab + $space) . "}/", '', $line);
            }
        }

<<<<<<< HEAD:core/util/Parser.php
        foreach ($rows as $row)
        {
=======
        foreach ($rows as $row) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $html .= "<li>" . $this->parse(implode("\n", $row), true, $start) . "</li>";
            $start += count($row);
        }

        return "<{$type}{$suffix}>{$html}</{$type}>";
    }

    /**
     * @param array $lines
     * @param array $value
     * @param int $start
     * @return string
     */
    private function parseTable(array $lines, array $value, $start)
    {
        list($ignores, $aligns) = $value;
        $head = count($ignores) > 0 && array_sum($ignores) > 0;

        $html = '<table>';
        $body = $head ? NULL : true;
        $output = false;

        foreach ($lines as $key => $line)
        {
            if (in_array($key, $ignores))
            {
                if ($head && $output)
                {
                    $head = false;
                    $body = true;
                }

                continue;
            }

            $line = trim($line);
            $output = true;

            if ($line[0] == '|')
            {
                $line = substr($line, 1);

                if ($line[strlen($line) - 1] == '|')
                {
                    $line = substr($line, 0, -1);
                }
            }


            $rows = array_map(function ($row)
            {
                if (preg_match("/^\s*$/", $row))
                {
                    return ' ';
                }
                else
                {
                    return trim($row);
                }
            }, explode('|', $line));
            $columns = array();
            $last = -1;

            foreach ($rows as $row)
            {
                if (strlen($row) > 0)
                {
                    $last++;
                    $columns[$last] = array(
                        isset($columns[$last]) ? $columns[$last][0] + 1 : 1,  $row
                    );
<<<<<<< HEAD:core/util/Parser.php
                }
                elseif (isset($columns[$last]))
                {
                    $columns[$last][0]++;
                }
                else
                {
=======
                } elseif (isset($columns[$last])) {
                    $columns[$last][0] ++;
                } else {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                    $columns[0] = array(1, $row);
                }
            }

            if ($head)
            {
                $html .= '<thead>';
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif ($body)
            {
=======
            } elseif ($body) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $html .= '<tbody>';
            }

            $html .= '<tr' . ($this->_line ? ' class="line" data-start="'
                . ($start + $key) . '" data-end="' . ($start + $key)
                . '" data-id="' . $this->_uniqid . '"' : '') . '>';

<<<<<<< HEAD:core/util/Parser.php
            foreach ($columns as $key => $column)
            {
=======
            foreach ($columns as $key => $column) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                list($num, $text) = $column;
                $tag = $head ? 'th' : 'td';

                $html .= "<{$tag}";
                if ($num > 1)
                {
                    $html .= " colspan=\"{$num}\"";
                }

                if (isset($aligns[$key]) && $aligns[$key] != 'none')
                {
                    $html .= " align=\"{$aligns[$key]}\"";
                }

                $html .= '>' . $this->parseInline($text) . "</{$tag}>";
            }

            $html .= '</tr>';

            if ($head)
            {
                $html .= '</thead>';
<<<<<<< HEAD:core/util/Parser.php
            }
            elseif ($body)
            {
=======
            } elseif ($body) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $body = false;
            }
        }

        if ($body !== NULL)
        {
            $html .= '</tbody>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * parseHr
     *
     * @param array $lines
     * @param array $value
     * @param int $start
     * @return string
     */
    private function parseHr($lines, $value, $start)
    {
        return $this->_line ? '<hr class="line" data-start="' . $start . '" data-end="' . $start . '">' : '<hr>';
    }

    /**
     * parseNormal
     *
     * @param array $lines
     * @param bool $inline
     * @param int $start
     * @return string
     */
    private function parseNormal(array $lines, $inline, $start)
    {
        foreach ($lines as $key => &$line)
        {
            $line = $this->parseInline($line);

            if (!preg_match("/^\s*$/", $line))
            {
                $line = $this->markLine($start + $key) . $line;
            }
        }

        $str = trim(implode("\n", $lines));
<<<<<<< HEAD:core/util/Parser.php
        $str = preg_replace_callback("/(\n\s*){2,}/", function () use (&$inline)
        {
=======
        $str = preg_replace_callback("/(\n\s*){2,}/", function () use (&$inline) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            $inline = false;
            return "</p><p>";
        }, $str);
        $str = preg_replace("/\n/", "<br>", $str);

        return preg_match("/^\s*$/", $str) ? '' : ($inline ? $str : "<p>{$str}</p>");
    }

    /**
     * parseFootnote
     *
     * @param array $lines
     * @param array $value
     * @return string
     */
    private function parseFootnote(array $lines, array $value)
    {
        list($space, $note) = $value;
        $index = array_search($note, $this->_footnotes);

        if (false !== $index)
        {
            $lines[0] = preg_replace("/^\[\^((?:[^\]]|\\]|\\[)+?)\]:/", '', $lines[0]);
            $this->_footnotes[$index] = $lines;
        }

        return '';
    }

    /**
     * parseDefine
     *
     * @return string
     */
    private function parseDefinition()
    {
        return '';
    }

    /**
     * parseHtml
     *
     * @param array $lines
     * @param string $type
     * @param int $start
     * @return string
     */
    private function parseHtml(array $lines, $type, $start)
    {
        foreach ($lines as &$line)
        {
            $line = $this->parseInline(
                $line,
                isset($this->_specialWhiteList[$type]) ? $this->_specialWhiteList[$type] : ''
            );
        }

        return implode("\n", $this->markLines($lines, $start));
    }

    /**
     * @param $url
     * @param bool $parseTitle
     *
     * @return mixed
     */
    private function cleanUrl($url, $parseTitle = false)
    {
        $title = null;
        $url = trim($url);

<<<<<<< HEAD:core/util/Parser.php
        if ($parseTitle)
        {
            $pos = strpos($url, ' ');

            if ($pos !== false)
            {
=======
        if ($parseTitle) {
            $pos = strpos($url, ' ');

            if ($pos !== false) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $title = htmlspecialchars(trim(substr($url, $pos + 1), ' "\''));
                $url = substr($url, 0, $pos);
            }
        }

        $url = preg_replace("/[\"'<>\s]/", '', $url);

<<<<<<< HEAD:core/util/Parser.php
        if (preg_match("/^(mailto:)?[_a-z0-9-\.\+]+@[_\w-]+(?:\.[a-z]{2,})+$/i", $url, $matches))
        {
            if (empty($matches[1]))
            {
=======
        if (preg_match("/^(mailto:)?[_a-z0-9-\.\+]+@[_\w-]+(?:\.[a-z]{2,})+$/i", $url, $matches)) {
            if (empty($matches[1])) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
                $url = 'mailto:' . $url;
            }
        }

<<<<<<< HEAD:core/util/Parser.php
        if (preg_match("/^\w+:/i", $url) && !preg_match("/^(https?|mailto):/i", $url))
        {
=======
        if (preg_match("/^\w+:/i", $url) && !preg_match("/^(https?|mailto):/i", $url)) {
>>>>>>> 01ef2da0417be206de7167d263d3dcfbfe7c574a:lib/Parser/Parser.php
            return '#';
        }

        return $parseTitle ? [$url, $title] : $url;
    }

    /**
     * @param $str
     * @return mixed
     */
    private function escapeBracket($str)
    {
        return str_replace(
            array('\[', '\]', '\(', '\)'),
            array('[', ']', '(', ')'),
            $str
        );
    }

    /**
     * startBlock
     *
     * @param mixed $type
     * @param mixed $start
     * @param mixed $value
     * @return $this
     */
    private function startBlock($type, $start, $value = NULL)
    {
        $this->_pos++;
        $this->_current = $type;

        $this->_blocks[$this->_pos] = array($type, $start, $start, $value);

        return $this;
    }

    /**
     * endBlock
     *
     * @return $this
     */
    private function endBlock()
    {
        $this->_current = 'normal';
        return $this;
    }

    /**
     * isBlock
     *
     * @param mixed $type
     * @param mixed $value
     * @return bool
     */
    private function isBlock($type, $value = NULL)
    {
        return $this->_current == $type
            && (NULL === $value ? true : $this->_blocks[$this->_pos][3] == $value);
    }

    /**
     * getBlock
     *
     * @return array
     */
    private function getBlock()
    {
        return isset($this->_blocks[$this->_pos]) ? $this->_blocks[$this->_pos] : NULL;
    }

    /**
     * setBlock
     *
     * @param mixed $to
     * @param mixed $value
     * @return $this
     */
    private function setBlock($to = NULL, $value = NULL)
    {
        if (NULL !== $to)
        {
            $this->_blocks[$this->_pos][2] = $to;
        }

        if (NULL !== $value)
        {
            $this->_blocks[$this->_pos][3] = $value;
        }

        return $this;
    }

    /**
     * backBlock
     *
     * @param mixed $step
     * @param mixed $type
     * @param mixed $value
     * @return $this
     */
    private function backBlock($step, $type, $value = NULL)
    {
        if ($this->_pos < 0)
        {
            return $this->startBlock($type, 0, $value);
        }

        $last = $this->_blocks[$this->_pos][2];
        $this->_blocks[$this->_pos][2] = $last - $step;

        if ($this->_blocks[$this->_pos][1] <= $this->_blocks[$this->_pos][2])
        {
            $this->_pos++;
        }

        $this->_current = $type;
        $this->_blocks[$this->_pos] = array(
            $type,  $last - $step + 1,  $last,  $value
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function combineBlock()
    {
        if ($this->_pos < 1)
        {
            return $this;
        }

        $prev = $this->_blocks[$this->_pos - 1];
        $current = $this->_blocks[$this->_pos];

        $prev[2] = $current[2];
        $this->_blocks[$this->_pos - 1] = $prev;
        $this->_current = $prev[0];
        unset($this->_blocks[$this->_pos]);
        $this->_pos--;

        return $this;
    }
}