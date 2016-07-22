<?php

class Book {
    private $object;

    function __construct($book){
        $this->object = new IrbisItem($book['body']);

        $this->id = intval($book['id']);
        $this->title = $this->typography($book['title']);
        $this->subtitle = $book['title_info'];

        if($this->object->field(900, 't') == 'a') {
            $this->type = 'book';
        } else {
            $this->type = 'disk';
        }


        if(!empty($this->object->field(700, 'b'))) {
            $this->author = $this->object->field(700, 'b') . ' ' . $this->object->field(700, 'a');
        } else {
            $this->author = $this->object->field(961, 'a');
        }

        $this->annotation = $this->typography($this->object->field(331)->__toString());
        $this->isbn = str_replace(' ', '', $this->object->field(10, 'a')) ?: null;
        $this->pages = intval($this->object->field(215, 'a'));
        $this->category = $this->object->field(606, 'a');

        if (is_array($this->object->field(210, 'c'))) {

            $this->publication = array(
                'publisher'=> $this->object->field(210, 'c')[0],
                'city' => $this->object->field(210, 'a')[0],
                'year' => $this->object->field(210, 'd')[0],
            );
        } else {
            $this->publication = array(
                'publisher'=> $this->object->field(210, 'c'),
                'city' => $this->object->field(210, 'a'),
                'year' => $this->object->field(210, 'd'),
            );
        }
        $this->lbc = $this->object->field(621)->__toString();
        $this->ageRestriction = $this->object->field(900, 'z') ?: 0;

        $this->contents = array();
        foreach ($this->object->fields[330] as $contentItem) {
            $contentItem = new IrbisField($contentItem);
            array_push($this->contents, $contentItem->sub('c'));
        }

        /*$copies = array();
        if(is_array($deps = $this->object->field(910, 'd'))){
            foreach ($deps as $dep) {
                if(isset($copies[$dep])) {
                    $copies[$dep]++;
                } else {
                    $copies[$dep] = 1;
                }
            }
        } else {
            $item = new IrbisField($this->object->fields[910]);
            $departament = $item->sub('d');

            $copies[$departament] = 1;
        }

        $this->copies = array();
        foreach ($copies as $departament => $count) {
            array_push($this->copies, array(
                'departament' => $departament,
                ''
                'count' => $count
            ));
        }*/

        $this->copies = array();

        if(is_array($this->object->field(910, 'd'))){
            foreach ($this->object->fields[910] as $copy) {
                $copyField = new IrbisField($copy);
                array_push($this->copies, array(
                    'departament' => $copyField->sub('d'),
                    'inventory' => $copyField->sub('b')
                ));
            }
        } else {
           array_push($this->copies, array(
                'departament' => $this->object->field(910, 'd'),
                'inventory' => $this->object->field(910, 'b')
            ));
        }
    }

    private function typography($text) {
        // $replaces = array(
        //     //'/"(\s|$)/' => '«$1',
        //     '/"(\W)/' => '«$1',
        //     '/(\W)"/' => '$1»'
        // );

        // $text = preg_replace(array_keys($replaces), $replaces, $text);

        $text = str_replace(
            array(' - ', '...'),
            array(' — ', '…')
        , $text);

        return $text;
    }
}
