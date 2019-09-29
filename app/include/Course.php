<?php

class Course {
    private $course;
    private $school;
    private $title;
    private $description;
    private $examdate;
    private $examstart;
    private $examend;

    public function __construct($course, $school, $title, $description, $examdate, $examstart, $examend) {
        $this->course = $course;
        $this->school = $school;
        $this->title = $title;
        $this->description = $description;
        $this->examdate = $examdate;
        $this->examstart = $examstart;
        $this->examend = $examend;   
    }

    public function getCourse() {
        return $this->course;
    }
    public function getSchool() {
        return $this->school;
    }
    public function getTitle() {
        return $this->title;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getExamdate() {
        return $this->examdate;
    }
    public function getExamstart() {
        return $this->examstart;
    }
    public function getExamend() {
        return $this->examend;
    }




}



?>