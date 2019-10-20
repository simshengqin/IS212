<?php

class Section {
    // property declaration
    private $userid;
    private $course;
    private $section;
    private $amount;

    public function __construct($userid, $course, $section, $amount)
    {
        $this->userid = $userid;
        $this->course = $course;
        $this->section = $section;
        $this->amount = $amount;
    }
    public function getUserid(){
        return $this->userid;
    }
    public function getCourse(){
        return $this->course;
    }
    public function getSection(){
        return $this->section;
    }
    public function getAmount(){
        return $this->amount;
    }
}

?>