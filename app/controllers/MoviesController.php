<?php

use Phalcon\Http\Request;
use Phalcon\Http\Response;

class MoviesController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        
    }

    public function addMovieAction() {
        $now = new DateTime();
        $request = new Request();
        $token = new tokenGenerator();
        $userId = $token->getUserId($request);

        $dataResponse = array(
            'data' => array(),
            'status' => array(
                'code' => 0,
                'msg' => 'An error occured',
            )
        );

        $itemData = $request->getJsonRawBody();
        if (!isset($itemData->title) || !isset($itemData->description)) {
            $dataResponse = array(
                'data' => array(),
                'status' => array(
                    'code' => 0,
                    'msg' => 'Missing required field',
                )
            );
        } else {

            $movie = new Movies();
            $movie->title = $itemData->title;
            $movie->description = $itemData->description;
            $movie->author = $userId;
            $movie->publication_date = $now->format('Y-m-d H:i:s');
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

    public function getAllMoviesAction($id) {

        $request = new Request();
        $dataResponse = array(
            'data' => array(),
            'status' => array(
                'code' => 1,
                'msg' => 'Get all items',
            )
        );

        $queryRestriction = '';
        if ($request->getHeader(TOKEN_NAME) != NULL) {
            $token = new tokenGenerator();
            $userId = $token->getUserId($request);

            $queryRestriction = " IF( m.author !=$userId,  '0',  '1' ) AS isAuthor,  CASE (SELECT vote_type FROM votes WHERE user=$userId AND movie=m.id )  WHEN  1 THEN 1 WHEN 0 THEN 0 ELSE 'NULL' END AS hasVote ,   ";
        }

        $searchById = '';
        if ($id != '') {
            $searchById = " WHERE m.author = $id";
        }

        $userActivity = array();
        //getting votes from the view table 'voting_system' (SELECT `movie` , SUM(`vote_type`= 1) AS LIKES,SUM(`vote_type`= 0) AS HATES FROM `votes` GROUP BY `movie`)
        $items = $this->db->query(
                        "SELECT $queryRestriction m.*, vs.*, u.id as userId, u.first_name, u.last_name FROM movies m JOIN users u ON m.author = u.id LEFT JOIN voting_system vs ON vs.movie=m.id $searchById"
                )->fetchAll();


        foreach ($items as $item) {

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
