<?php

/**
 * Meta: Basic data format for meta system
 * @param string $time Create time of meta
 * @param string $type Type of meta
 * @param array $tag Tags array of meta
 * @param int $uid Creator of this meta
 */
class Meta
{
    private $time, $type, $tag, $uid;

    public function __construct(string $Time, string $Type, array $Tag, int $Uid)
    {
        $this->time = $Time;
        $this->type = $Type;
        $this->tag = $Tag;
        $this->uid = $Uid;
    }

    public function get()
    {
        return array('time' => $this->time, 'type' => $this->type, 'tag' => $this->tag, 'uid' => $this->uid);
    }
}

/**
 * Text: Text meta
 * @param Meta $meta The meta data of this text
 * @param string $title The title of text
 * @param string $content The content of text
 */
class Text extends Meta
{
    private $title, $content;

    public function __construct(Meta $meta, $t, $c)
    {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->title = $t;
        $this->content = $c;
    }

    public function get()
    {
        return array('title' => $this->title, 'content' => $this->content) + parent::get();
    }
}

/**
 * File: Meta file
 * @param Meta $meta The meta data of this meta file
 * @param string $fileName The filename of this meta file
 */
class File extends Meta
{
    private $fileName;

    public function __construct(Meta $meta, $i)
    {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->fileName = $i;
    }

    public function get()
    {
        return array('fileName' => $this->fileName) + parent::get();
    }
}

/**
 * MetaArray: A set of structured metas
 * @param Meta $meta Meta data of this meta
 * @param array $metas Metas that constitute this meta array
 */
class MetaArray extends Meta
{
    private $metaArray;

    public function __construct(Meta $meta, array $a)
    {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->metaArray = $a;
    }

    public function get()
    {
        return array('metaArray' => $this->metaArray) + parent::get();
    }
}
