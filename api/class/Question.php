<?php

/**
 * Created by PhpStorm.
 * User: Dullais
 * Date: 7/25/2018
 * Time: 1:36 PM
 */

/* Created simple api for be, in my opinion there were no need for db as questions can be somewhat elegantly in json file.
 * By editing that file (questions.json) you can add and remove questions from test. Created on PHP 5.6.31, currently i
 * don't have dev server set up and this is what i had locally */

class Question {
    private $action;
    private $questions = null;

    function __construct() {

        if(isset($_POST['question'])) {
            $this->action = $this->clean($_POST['question']);

            if($this->action === 'get') {
                $this->getQuestions();
            } elseif ($this->action === 'validate') {
                $this->validateQuestions();
            } else {
                $this->invalidData();
            }
        } else {
            $this->invalidData();
        }
    }

    //Returns questions
    private function getQuestions() {
        //Loads questions, if they are not loaded yet as well as creating private var
        $questions = $this->loadQuestions(true);

        //So we are not responding with correct answers...
        foreach ($questions as $question => &$answers) {
            foreach ($answers as $key => &$value) {
                $value = false;
            }
        }

        $this->respond($questions);
    }

    //Validates questions and returns mistakes
    private function validateQuestions() {

        if(isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);

            if (json_last_error() === 0) {
                $mistakes = $this->validateAnswers($data);

                $this->respond($mistakes);
            } else {
                $this->invalidData();
            }
        }
    }

    //Loads question if there is need for it, if given true - returns questions as well
    private function loadQuestions($return = false) {

        if($this->questions === null) {
            $this->questions = file_get_contents('./../questions.json');
            $this->questions = json_decode($this->questions,true);
        }

        //Return only if asked to
        if($return) {
            return $this->questions;
        }
    }

    //Responds with JSON
    private function respond(array $response) {

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //Throws error
    private function error($msg = "Fatal error has occurred !") {
        $this->respond(array("error" => $msg));
        die;
    }


    //Validates answers one by one and returns array with mistakes
    private function validateAnswers(array $data) {
        $this->loadQuestions();
        $mistakes = array();

        foreach ($this->questions as $question => $answers) {
            $mistakes[$question] = array();

            foreach ($answers as $key => $value) {

                if(isset($data[$question][$key])) {

                    if($value !== $data[$question][$key]) {

                        array_push($mistakes[$question], array(
                            'answer' => $key,
                            'correct' => $value
                        ));
                    }
                }
                //If this questions dont exist, we are throwing error
                else {
                    $this->invalidData();
                }
            }
        }

        return $mistakes;
    }

    //Common function for invalid data
    private function invalidData() {
        $this->error('Invalid input given !');
    }

    //Cleans string
    private function clean($data) {

        return trim(htmlspecialchars($data));
    }
}