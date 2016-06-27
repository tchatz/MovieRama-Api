<?php

use Emarref\Jwt\Claim;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tokenGenerator
 *
 * @author tchatz
 */
class tokenGenerator {

    //put your code here
    public function loginJwt($userId, $userRole, $request) {


        $token = new Emarref\Jwt\Token();

// Standard claims are supported
        $token->addClaim(new Claim\Audience(['audience_1', 'audience_2']));
        $token->addClaim(new Claim\Expiration(new \DateTime('30 minutes')));
        $token->addClaim(new Claim\IssuedAt(new \DateTime('now')));
        $token->addClaim(new Claim\Issuer('your_issuer'));
        $token->addClaim(new Claim\JwtId($userId));
        $token->addClaim(new Claim\NotBefore(new \DateTime('now')));
        $token->addClaim(new Claim\Subject('your_subject'));

// Custom claims are supported
        $token->addClaim(new Claim\PublicClaim('claim_name', 'claim_value'));
        $token->addClaim(new Claim\PrivateClaim('user_role', $userRole));
        $jwt = new Emarref\Jwt\Jwt();
        $ipAddress = $request->getClientAddress();
        $algorithm = new Emarref\Jwt\Algorithm\Hs256($ipAddress . SECRET_PHRASE);
        $encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
        $serializedToken = $jwt->serialize($token, $encryption);


        return $serializedToken;
    }

    public function getUserId($request) {
        $tokenRaw = $request->getHeader(TOKEN_NAME);
        $jwt = new Emarref\Jwt\Jwt();
        $token = $jwt->deserialize($tokenRaw);
        $userId = $token->getPayload()->findClaimByName('jti')->getValue();
        return $userId;
    }

    public function autoRefresh($token,$request){
        $token->addClaim(new Claim\Expiration(new \DateTime('30 minutes')));
        $jwt = new Emarref\Jwt\Jwt();
        $ipAddress = $request->getClientAddress();
        $algorithm = new Emarref\Jwt\Algorithm\Hs256($ipAddress . SECRET_PHRASE);
        $encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
        $serializedToken = $jwt->serialize($token, $encryption);
        
        return $serializedToken;
    }
    
}
