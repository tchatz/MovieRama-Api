<?php

use Phalcon\Http\Request;
use Phalcon\Http\Response;

class UserController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        
    }

    /**
     * @api {post} /login
     * @apiSampleRequest off
     * @apiName UserLogin
     * @apiGroup User Login
     * @apiVersion 0.1.0
     * 
     * @apiParam username
     * @apiParam password
     * 
     * @apiParamExample {json} Request-Example:
     *     {
     *       "username": "guest Username",
     *       "password": "guest Password"
     *     }
     *
     * @apiSuccess {Array} status 
     * @apiSuccess {Integer} code 1: Successfull login 0: wrong credentials
     * @apiSuccess {String} msg 
     * @apiSuccess {Array} data 
     * @apiSuccess {String} token Json Web Token
     * @apiSuccess {String} firstName 
     * @apiSuccess {Array} lastName
     * @apiSuccess {String} userId 
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * 
     *{
     * "data": {
     *      "token": "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOlsiYXVkaWVuY2VfMSIsImF1ZGllbmNlXzIiXSwiZXhwIjoxNDY2OTgwNTEyLCJpYXQiOjE0NjY5Nzg3MTIsImlzcyI6InlvdXJfaXNzdWVyIiwianRpIjoiMyIsIm5iZiI6MTQ2Njk3ODcxMiwic3ViIjoieW91cl9zdWJqZWN0IiwiY2xhaW1fbmFtZSI6ImNsYWltX3ZhbHVlIiwidXNlcl9yb2xlIjoiTUVNQkVSIn0.e2V2hWFhgRbfbhqhAE_eQcCJOUmaxs-jsVMUy7Eq674",
     *      "firstName": "Thomas",
     *      "lastName": "Chatzidimitris",
     *      "userId": "3"
     * },
     * "status": {
     *      "code": 1,
     *      "msg": "Successfull login"
     * }
     *}
     * 
     *@apiErrorExample Error-Response:
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "wrong credentials"
     *  }
     * }
     */
    public function loginAction() {
        //get http request
        $request = new Request();

        //default answer
        $dataResponse = array(
            'data' => array(
                'token' => '',
                'user' => '',
            ),
            'status' => array(
                'code' => 0,
                'msg' => 'wrong credentials',
            )
        );
        
        //get json items
        $itemData = $request->getJsonRawBody();

        //validate username and password fields
        if ((isset($itemData->username) && trim($itemData->username) != '') && (isset($itemData->password) && trim($itemData->password) != '')) {
            $username = $itemData->username;
            $password = $itemData->password;
            
            //find user by username
            $user = Users::findFirst(
                            array(
                                "conditions" => "username = :username:",
                                "bind" => array("username" => $username)
            ));
            
            //if user exists
            if ($user) {
                
                //check if password is valid (using checkHash())
                if ($this->security->checkHash($password, $user->password)) {

                    //initialise tokenGenerator class
                    $token = new tokenGenerator();

                    $dataResponse = array(
                        'data' => array(
                            'token' => $token->loginJwt($user->id, "MEMBER", $request), //get json web token
                            'firstName' => $user->first_name,
                            'lastName' => $user->last_name,
                            'userId' => $user->id
                        ),
                        'status' => array(
                            'code' => 1,
                            'msg' => 'Successfull login',
                        )
                    );
                }
            } else {
                //for security purposes "reset" hash
                $this->security->hash(rand());
            }
        }

        //init new http response
        $response = new Response();
        //add content
        $response->setJsonContent($dataResponse);
        //add header
        $response->setHeader("Content-Type", "application/json");
        //return response
        return $response;
    }

    /**
     * @api {post} /signup
     * @apiSampleRequest off
     * @apiGroup User Signup
     * @apiVersion 0.1.0
     * 
     * @apiParam username
     * @apiParam password
     * @apiParam firstName
     * @apiParam lastName
     * 
     * @apiParamExample {json} Request-Example:
     *     {
     *       "username": "guest Username",
     *       "password": "guest Password",
     *       "firstName": "guest first name",
     *       "lastName": "guest last name",
     *     }
     *
     * @apiSuccess {Array} status 
     * @apiSuccess {Integer} code 1: User account created! 0: Error
     * @apiSuccess {String} msg 
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * 
     *{
     * "status": {
     *  "code": 1,
     *  "msg": "User account created!"
     * }
     *}
     * 
     *@apiErrorExample Error-Response 1:
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "Username exists"
     *  }
     * }
     *
     * @apiErrorExample Error-Response 2:
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "Missing required fields"
     *  }
     * }
     *
     *  @apiErrorExample Error-Response 3:
     * Error on save()
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "Something goes wrong!"
     *  }
     * }
     *    
     */
    public function signupAction() {
        $request = new Request();

        $itemData = $request->getJsonRawBody();

        //validate username and password fields
        if ((isset($itemData->username) && trim($itemData->username) != '') && (isset($itemData->password) && trim($itemData->password) != '')) {
            $username = $itemData->username;
            $password = $itemData->password;
            $fistName = $itemData->firstName;
            $lastName = $itemData->lastName;

            $userExists = Users::findFirst(
                            array(
                                "conditions" => "username = :username:",
                                "bind" => array("username" => $username)
            ));

            //check if username exists
            if (!$userExists) {
                $user = new Users();
                $user->username = $username;
                $user->password = $this->security->hash($password);
                $user->first_name = $fistName;
                $user->last_name = $lastName;
                if ($user->save()) { //if save is ok
                    $dataResponse = array(
                        'status' => array(
                            'code' => 1,
                            'msg' => 'User account created!',
                        )
                    );
                } else {
                    $dataResponse = array(
                        'status' => array(
                            'code' => 0,
                            'msg' => 'Something goes wrong!',
                        )
                    );
                }
            } else {
                $dataResponse = array(
                    'status' => array(
                        'code' => 0,
                        'msg' => 'Username exists',
                    )
                );
            }
        } else {
            $dataResponse = array(
                'status' => array(
                    'code' => 0,
                    'msg' => 'Missing required fields',
                )
            );
        }

        $response = new Response();
        $response->setJsonContent($dataResponse);
        $response->setHeader("Content-Type", "application/json");
        return $response;
    }

}
