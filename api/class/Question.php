<?php

/**
 * Created by PhpStorm.
 * User: Dullais
 * Date: 7/25/2018
 * Time: 1:36 PM
 */

class Question {

    private $action;

    function __construct() {
        if(isset($_GET['question'])) {
            $this->action = $this->clean($_GET['question']);

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

    private function getQuestions() {

        $questions = file_get_contents('./../questions.json');
        $questions = json_decode($questions,true);

        //So we are not responding with correct answers...
        foreach ($questions as $question => &$answers) {
            foreach ($answers as $key => &$value) {
                $value = false;
            }
        }

        $this->respond($questions);
    }

    private function validateQuestions() {

        if(isset($_GET['data'])) {
            $data = json_decode($_GET['data']);

            if (json_last_error() === JSON_ERROR_NONE) {
                $mistakes = $this->validateAnswers($data);


            } else {
                $this->invalidData();
            }
        }
    }

    private function validateAnswers(array $data) {

        return 3;
    }

    private function invalidData() {
        $this->error('Invalid input var given !');
    }

    private function error($msg = "Fatal error has occurred !") {
        $this->respond(array("error" => $msg));
        die;
    }

    private function respond(array $response) {

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function clean($data) {

        return trim(htmlspecialchars($data));
    }
}