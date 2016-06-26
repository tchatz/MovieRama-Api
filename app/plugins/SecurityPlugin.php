<?php

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Http\Request;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin {

    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    public function getAcl() {

        //throw new \Exception("something");

        if (!isset($this->persistent->acl)) {

            $acl = new AclList();

            $acl->setDefaultAction(Acl::DENY);

            //Register roles
            $roles = array(
                'MEMBER' => new Role('MEMBER'),
                'GUEST' => new Role('GUEST')
            );
            foreach ($roles as $role) {
                $acl->addRole($role);
            }

            //Private area resources
            $privateResources = array(
                'Movies' => array('index', 'addMovie','voteMovie')
            );

            foreach ($privateResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            //Public area resources
            $publicResources = array(
                'User' => array('index', 'login', 'signup'),
                'Movies' => array('getAllMovies')
            );

            foreach ($publicResources as $resource => $actions) {

                $acl->addResource(new Resource($resource), $actions);
            }

            //Grant access to public areas to both users and guests
            foreach ($roles as $role) {

                foreach ($publicResources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $acl->allow($role->getName(), $resource, $action);
                    }
                }
            }

            //Grant access to private area to role ROLE_LOYALTY_MANAGER
            foreach ($privateResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('MEMBER', $resource, $action);
                }
            }



            //The acl is stored in session, APC would be useful here too
            $this->persistent->acl = $acl;
        }

        return $this->persistent->acl;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {

        $request = new Request();

        $jwToken = $request->getHeader(TOKEN_NAME);

        $roles = array();
        if ($jwToken != null) {

            $jwt = new Emarref\Jwt\Jwt();
            try {
                $token = $jwt->deserialize($jwToken);
            } catch (\Exception $e) {
                $data['status'] = array(
                    'code' => 0,
                    'msg' => 'invalid token'
                );
                echo json_encode($data);
                exit;
            }
            $ipAddress = $request->getClientAddress();
            $algorithm = new Emarref\Jwt\Algorithm\Hs256($ipAddress . SECRET_PHRASE);
            $encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
            $context = new Emarref\Jwt\Verification\Context($encryption);
            $context->setAudience('audience_1');
            $context->setIssuer('your_issuer');
            $context->setSubject('your_subject');

            try {
                if ($jwt->verify($token, $context)) {
                    $userId = $token->getPayload()->findClaimByName('jti')->getValue();
                } else {
                    $data['status'] = array(
                        'code' => 0,
                        'msg' => 'invalid token'
                    );
                    echo json_encode($data);
                    exit;
                }
            } catch (Emarref\Jwt\Exception\VerificationException $e) {
                $data['status'] = array(
                    'code' => 0,
                    'msg' => 'invalid token'
                );
                echo json_encode($data);
                exit;
            }

            $user = Users::findFirst($userId);
            if ($user) {
                $roles[] = 'MEMBER';
            } else {
                $data['status'] = array(
                    'code' => 0,
                    'msg' => 'User not found!'
                );
                echo json_encode($data);
                exit;
            }
        } else {
            $roles[] = 'GUEST';
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $acl = $this->getAcl();

        $allowed = Acl::DENY;
        foreach ($roles as $role) {

            $allowed = $acl->isAllowed($role, $controller, $action);

            if ($allowed == Acl::ALLOW) {


                break;
            }
        }

        if ($allowed != Acl::ALLOW) {
            $data['status'] = array(
                'code' => 0,
                'msg' => 'Not allowed'
            );
            echo json_encode($data);
            $this->session->destroy();
            exit;
        }
    }

}
