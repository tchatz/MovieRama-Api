<?php

use Phalcon\Http\Request;
use Phalcon\Http\Response;

class MoviesController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        
    }

    /**
     * @api {post} /addMovie
     * @apiSampleRequest off
     * @apiGroup Add Movie
     * @apiVersion 0.1.0
     * 
     * @apiParam title
     * @apiParam description
     * 
     * @apiParamExample {json} Request-Example:
     *     {
     *       "username": "StarWars",
     *       "password": "Sci-fi movie",
     *     }
     *
     * @apiSuccess {Array} status 
     * @apiSuccess {Integer} code 1: New movie added 0: Error
     * @apiSuccess {String} msg 
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * 
     * {
     * "status": {
     *  "code": 1,
     *  "msg": "New movie added"
     * }
     * }
     * 
     * @apiErrorExample Error-Response 1:
     * Default response
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "An error occured"
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
     *    
     */
    public function addMovieAction() {

        //set current datetime
        $now = new DateTime();
        //http request
        $request = new Request();
        //init tokenGeneration class (classes\tokenGenerator.php)
        $token = new tokenGenerator();
        //get UserId from token
        $userId = $token->getUserId($request);

        //init default response
        $dataResponse = array(
            'data' => array(),
            'status' => array(
                'code' => 0,
                'msg' => 'An error occured',
            )
        );

        //get json data from request
        $itemData = $request->getJsonRawBody();

        //check if 'title' & 'description' are valid
        if (!isset($itemData->title) || !isset($itemData->description)) {
            $dataResponse = array(
                'data' => array(),
                'status' => array(
                    'code' => 0,
                    'msg' => 'Missing required field',
                )
            );
        } else {

            //init movie model
            $movie = new Movies();
            $movie->title = $itemData->title;
            $movie->description = $itemData->description;
            $movie->author = $userId;
            $movie->publication_date = $now->format('Y-m-d H:i:s');
            //check if save() is ok
            if ($movie->save()) {
                $dataResponse = array(
                    'data' => array(),
                    'status' => array(
                        'code' => 1,
                        'msg' => 'New movie added',
                    )
                );
            }
        }
        $response = new Response();
        $response->setJsonContent($dataResponse);
        $response->setHeader("Content-Type", "application/json");
        return $response;
    }

    /**
     * @api {get} /getMovies/{id}
     * @apiSampleRequest off
     * @apiGroup Get Movies
     * @apiVersion 0.1.0
     * 
     * @apiParam id (optional)
     * 
     * 
     *
     * @apiSuccess {Array} status 
     * @apiSuccess {Integer} code 1: Get all items
     * @apiSuccess {String} msg 
     *
     * @apiSuccessExample Success-Response without token:
     * HTTP/1.1 200 OK
     * 
     * {
     *  "data": [
     *      {
     *          "id": "18",
     *          "title": "Avatar",
     *          "description": "dsdfdssd",
     *          "published": "2016-06-26 04:31:38",
     *          "userId": "1",
     *          "user_firstname": "Thomas",
     *          "user_lastname": "Chatzidimitris",
     *          "likes": "1",
     *          "hates": "1",
     *          "userActivity": []
     *      },
     *  ],
     *  "status":{
     *      "code": 1,
     *      "msg": "Get all items"
     *  }
     * }
     * 
     * @apiSuccessExample Success-Response with token:
     * HTTP/1.1 200 OK
     * 
     * {
     *  "data": [
     *      {
     *          "id": "18",
     *          "title": "Avatar",
     *          "description": "dsdfdssd",
     *          "published": "2016-06-26 04:31:38",
     *          "userId": "1",
     *          "user_firstname": "Thomas",
     *          "user_lastname": "Chatzidimitris",
     *          "likes": "1",
     *          "hates": "1",
     *          "userActivity": {
     *              "hasVote": "1",
     *              "isAuthor": "0"
     *          }
     *      },
     *  ],
     *  "status":{
     *      "code": 1,
     *      "msg": "Get all items"
     *  }
     * }
     *    
     */
    public function getAllMoviesAction($id) {

        //get http request
        $request = new Request();

        //init default response
        $dataResponse = array(
            'data' => array(),
            'status' => array(
                'code' => 1,
                'msg' => 'Get all items',
            )
        );

        //----CASE: IF USER SEND THE REQUEST WITH TOKEN (IS LOGGED IN)-----
        //init extra query
        $queryRestriction = '';

        //check if header is empty
        if ($request->getHeader(TOKEN_NAME) != NULL) {

            //init tokenGenerator class
            $token = new tokenGenerator();
            //get UserId from token
            $userId = $token->getUserId($request);
            //init extra-query: 
            //SELECT: If movie author is not equal with userId return 0 else 1 in 'isAuthor' column
            //SELECT: If current user has vote 'like' return 1 else if the vote is 'hate' return 0 else if vote doesn't exists return NULL (as a string) 
            $queryRestriction = " IF( m.author !=$userId,  '0',  '1' ) AS isAuthor,  CASE (SELECT vote_type FROM votes WHERE user=$userId AND movie=m.id )  WHEN  1 THEN 1 WHEN 0 THEN 0 ELSE 'NULL' END AS hasVote ,   ";
        }

        //--- END CASE -----
        //init searchById variable
        $searchById = '';
        //check if request has GET parameter
        if ($id != '') {
            //init extra-query:
            //WHERE: user is author
            $searchById = " WHERE m.author = $id";
        }

        //init userActivity array for response
        $userActivity = array();

        //init main query
        //SELECT: All movies from table movies m
        //SELECT: All authors from table users u
        //SELECT: Getting votes from VIEW table voting_system vs (SELECT `movie` , SUM(`vote_type`= 1) AS LIKES,SUM(`vote_type`= 0) AS HATES FROM `votes` GROUP BY `movie`)
        $items = $this->db->query(
                        "SELECT $queryRestriction m.*, vs.*, u.id as userId, u.first_name, u.last_name FROM movies m JOIN users u ON m.author = u.id LEFT JOIN voting_system vs ON vs.movie=m.id $searchById"
                )->fetchAll();

        //fetch results
        foreach ($items as $item) {
            //init user activity (if user is logged in)
            if (isset($item['hasVote']) && isset($item['isAuthor'])) {
                $userActivity = array(
                    'hasVote' => $item['hasVote'],
                    'isAuthor' => $item['isAuthor']
                );
            }

            $data[] = array(
                'id' => $item['id'],
                'title' => $item['title'],
                'description' => $item['description'],
                'published' => $item['publication_date'],
                'userId' => $item['userId'],
                'user_firstname' => $item['first_name'],
                'user_lastname' => $item['last_name'],
                'likes' => $item['LIKES'],
                'hates' => $item['HATES'],
                'userActivity' => $userActivity
            );
        }
        $dataResponse['data'] = $data;

        $response = new Response();
        $response->setJsonContent($dataResponse);

        $response->setHeader("Content-Type", "application/json");
        return $response;
    }

    
    
        /**
     * @api {post} /voteMovie
     * @apiSampleRequest off
     * @apiGroup vote Movie
     * @apiVersion 0.1.0
     * 
     * @apiParam movie movie id
     * @apiParam vote 1=like, 0=hate
     * 
     * @apiParamExample {json} Request-Example:
     *     {
     *       "movie": "21",
     *       "vote": "1",
     *     }
     *
     * @apiSuccess {Array} status 
     * @apiSuccess {Integer} code 1: vote movie added 0: Error
     * @apiSuccess {String} msg 
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * 
     * {
     * "status": {
     *  "code": 1,
     *  "msg": "Request accepted!"
     * }
     * }
     * 
     * @apiErrorExample Error-Response 1:
     * Default response
     * Case: Current user is author
     * 
     * {
     *  "status": {
     *  "code": 0,
     *  "msg": "You cannot vote this movie!"
     *  }
     * }
     *
     *
     *    
     */
    
    public function voteMovieAction() {
        $request = new Request();
        $itemData = $request->getJsonRawBody();
        $userVote = $itemData->vote; //0 hate, 1 like
        $movie = $itemData->movie;

        $token = new tokenGenerator();
        $userId = $token->getUserId($request);

        $response = new Response();

        //check if user is author
        $isAuthor = Movies::findFirst(array(
                    "conditions" => "author = :user: AND id = :movie:",
                    "bind" => array("user" => $userId, "movie" => $movie)
        ));


        if ($isAuthor) {
            $dataResponse = array(
                'data' => array(),
                'status' => array(
                    'code' => 0,
                    'msg' => 'You cannot vote this movie!',
                )
            );
            $response->setJsonContent($dataResponse);
            $response->setHeader("Content-Type", "application/json");
            return $response;
        }

        //check if vote exists
        $checkVote = Votes::findFirst(array(
                    "conditions" => "user = :user: AND movie = :movie:",
                    "bind" => array("user" => $userId, "movie" => $movie)
        ));
        //if vote does not exists
        if (!$checkVote) {
            $vote = new Votes();
            $vote->user = $userId;
            $vote->movie = $movie;
            $vote->vote_type = $userVote;
            $vote->save();
        } else {
            if ($checkVote->vote_type === $userVote) {

                $checkVote->delete();
            } else {

                $checkVote->vote_type = $userVote;
                $checkVote->save();
            }
        }

        $dataResponse = array(
            'data' => array(),
            'status' => array(
                'code' => 0,
                'msg' => 'Request accepted!',
            )
        );
        $response->setJsonContent($dataResponse);
        $response->setHeader("Content-Type", "application/json");
        return $response;
    }

}
