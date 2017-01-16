<?php

/**
 * Description of Validator
 *
 * @author Sunil Ftcash
 */
class Validator {

    private $message;
    private $success;
    private $request;
    private $validationArr;
    private $returnResponse;
    private $requestParamsCount;
    private $simplifiedValidationRules;
    private $customMessages;

    //set a default constructor
    public function __construct() {
        /**
         * init all variables
         */
        $this->request = $this->validationArr = $this->simplifiedValidationRules = $this->customMessages = null;
        $this->requestParamsCount = 0;
        $this->success = true;
        $this->message = 'Request is empty.';
        $this->returnResponse = [
            "success" => $this->success,
            "message" => $this->message
        ];
    }

    /**
     * @set request array
     * @param type $request
     * @return type
     * @return array[success=>true,'message'=>'']
     */
    public function setRequest($request = array()) {
        if (is_array($request) && (count($request) > 0)) {
            $this->request = $request;
            $this->success = true;
            $this->message = 'Request is set.';
            $this->setResponse();
        }
        return $this->returnResponse;
    }

    /**
     * @set validation rules array
     * @param type $validationArr
     * @array rules [
     *               "key"=>[rules]
     *              ]
     *             @example [
     *                        "username"=>[required,minLength:10,maxLength:20,uppercase],
     *                        "password"=>[required,minLength:8,alphanumeric,matchValue:confirmPassword],
     *                        "confirmPassword"=>[required,matchValue:password]
     *                      ];
     * @return array[success=>true,'message'=>'']
     * 
     */
    public function setValidationRules($validationArr = array()) {
        if (!is_array($validationArr) && (count($validationArr) <= 0)) {
            $this->success = false;
            $this->message = 'Validation rules are empty';
            $this->setResponse();
        } else {
            $this->validationArr = $validationArr;
            $this->success = true;
            $this->message = 'Validation is set';
            $this->setResponse();
        }

        return $this->returnResponse;
    }

    /**
     * @sets custom messages 
     * @param type $customMessages
     * @return type
     */
    public function setCustomMessage($customMessages = array()) {
        
        if (is_array($customMessages) && (count($customMessages) > 0)) {
            $this->customMessages = $customMessages;
            $this->success = true;
            $this->message = 'Custom messages set.';
            $this->setResponse();
        } else {
            $this->success = false;
            $this->message = 'Custom messages are not set.';
            $this->setResponse();
        }
        return $this->returnResponse;
    }

    /**
     * 
     * @param type $customMessage
     */
    public function validate($customMessage = '') {
        //check params are set and we have validation Rules....
        $this->success = false;
        $this->checkParamsAndValidationRules();
        if ($this->success == true) {
            //Simplify ValidationArr
            $this->simplifyValidationArr();

            //validate 
            $this->validateRules();
            if ($this->success == false) {
                return $this->returnResponse;
            }
        }
    }

    /**
     * 
     */
    private function checkParamsAndValidationRules() {
        if ($this->request == null) {
            $this->success = false;
            $this->message = 'Request is empty';
            $this->setResponse();
        } else if ($this->validationArr == null) {
            $this->success = false;
            $this->message = 'Validation rules are empty';
            $this->setResponse();
        } else {
            $this->success = true;
            $this->message = "parameter are checked and in correct manner.";
            $this->setResponse();
        }
    }

    /**
     * 
     */
    private function simplifyValidationArr() {
        foreach ($this->validationArr as $paramter => $rules) {
            $single = null;
            $double = null;
            foreach ($rules as $rule) {
                if (strpos($rule, ":")) {
                    $content = explode(':', $rule);
                    $double[] = [
                        $content[0] => $content[1]
                    ];
                } else {
                    $single[] = $rule;
                }
            }
            $this->simplifiedValidationRules[$paramter] = [
                "single" => $single,
                "double" => $double
            ];
        }
    }

    private function validateRules() {

        foreach ($this->simplifiedValidationRules as $parameter => $rules) {
            //check for single 
            $this->validateSingles($parameter, $rules['single']);
            if ($this->success == false) {
                return $this->success;
            }
            //check for double
        }
        $this->success = true;
    }

    private function validateSingles($parameter, $singleRules) {
        foreach ($singleRules as $item) {
            $this->checkCases($parameter, $item);
            if ($this->success == false) {
                return $this->success;
            }
        }
    }

    private function checkCases($parameter, $case, $default = 0) {
        $parameterValue = $this->request[$parameter];
        switch (strtolower($case)) {
            case 'required':
                if (empty($parameterValue) || is_null($parameterValue)) {
                    $this->success = false;
                    if (!$this->getCustomMessage($parameter, $case)) {
                        $this->message = "$parameter is required field.";
                    }
                    $this->setResponse();
                }
                break;
        }
    }

    private function getCustomMessage($parameter, $case) {
        if (isset($this->customMessages[$parameter][$case])) {
            $message = $this->customMessages[$parameter][$case];
            if (strpos($message, '#param')) {
                $message = str_replace("#param", $parameter, $message);
            }
            $this->message = $message;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     */
    private function setResponse() {
        $this->returnResponse = [
            'success' => $this->success,
            'message' => $this->message
        ];
    }

}
