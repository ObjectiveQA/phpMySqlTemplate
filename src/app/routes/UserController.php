<?php
class UserController extends BaseController
{
    public function delete($path)
    {
        // consume id
        if (array_key_exists(0, $path)) {
            $userId = $path[0];
        } else {
            $userId = null;
        }

        // validate id
        if (!$this->validateId($userId)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Path element following /users must be a number greater than 0.';
            exit;
        }

        // delete from db
        $userModel = new UserModel();
        $userModel->deleteUsers(array($userId));

        // return response
        $this->sendOutput(
            'Deleted',
            array('Content-Type: application/json', 'HTTP/1.1 204 No Content')
        );
        exit;
    }

    public function get($path)
    {
        // consume and validate path
        if (array_key_exists(0, $path)) {
            $userId = $path[0];
            if (!$this->validateId($userId)) {
                header('HTTP/1.1 400 Bad Request');
                echo 'Path element following /users must be a number greater than 0.';
                exit;
            }
        } else {
            $userId = null;
        }

        // get from db
        $userModel = new UserModel();
        if ($userId) {
            $responseData = $userModel->getUserById($userId);

            if (!$responseData) {
                header('HTTP/1.1 404 Not Found');
                echo "No user found with id $userId.";
                exit;
            }
        } else {
            $responseData = $userModel->getUsers();
        }

        // return response
        $this->sendOutput(
            json_encode($responseData),
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );
        exit;
    }

    public function post()
    {
        // consume body and validate
        $users = $this->consumeBody();
        $this->validatePostUsers($users);

        // post to db
        $userModel = new UserModel();
        $userModel->postUsers($users);

        // return response
        header('HTTP/1.1 201 Created');
        exit;
    }

    public function put()
    {
        // consume body and validate
        $users = $this->consumeBody();
        $this->validatePutUsers($users);
        
        // put to db
        $userModel = new UserModel();
        $userModel->putUsers($users);

        // return response
        header('HTTP/1.1 204 No Content');
        exit;
    }

    private function consumeBody()
    {
        $json = file_get_contents('php://input');
        return json_decode($json);
    }

    private function validateBody($users)
    {
        // validate request is an array
        if (!is_array($users)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Request body must be an array';
            exit;
        }

        // validate all records contain required properties
        foreach($users as $user) {
            if (
                !is_object($user)
                || !(property_exists($user, 'full_name') && is_string($user->full_name) && !empty($user->full_name))
                || !(property_exists($user, 'email') && is_string($user->email) && !empty($user->email))
            ) {
                header('HTTP/1.1 400 Bad Request');
                echo "Each request body array item must be an object with 'full_name' and 'email' properties.";
                exit;
            };
        }

        // validate no duplicate emails in request
        $allEmails = array_map(fn($user) => $user->email, $users);
        $uniqueEmails = array_unique($allEmails);
        if (count($uniqueEmails) < count($allEmails)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Each object in request body must specify a unique email.';
            exit;
        }

        return $users;
    }

    private function validatePostUsers($users) {
        $this->validateBody($users);

        // validate emails are not in use
        $allUserEmails = array_map(fn($user) => $user->email, $users);
        $userModel = new UserModel();
        $existingUsersWithEmails = $userModel->getUsersByEmails($allUserEmails);

        if (count($existingUsersWithEmails) > 0) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Each object in request body must specify an email not already in use.';
            exit;
        }
    }

    private function validatePutUsers($users) {
        $this->validateBody($users);

        // validate all records contain user_id property
        foreach($users as $user) {
            if (
                !(property_exists($user, 'user_id') && is_numeric($user->user_id) && $user->user_id > 0)
            ) {
                header('HTTP/1.1 400 Bad Request');
                echo "Each request body array item must contain a valid 'user_id'.";
                exit;
            };
        }

        // validate no duplicate user_id values in request body
        $allUserIds = array_map(fn($user) => $user->user_id, $users);
        $uniqueIds = array_unique($allUserIds);
        if (count($uniqueIds) < count($allUserIds)) {
            header('HTTP/1.1 400 Bad Request');
            echo "Each request body array item must specify a unique 'user_id'.";
            exit;
        }

        // validate all user_id values in request body exist in the db
        $userModel = new UserModel();

        if (count($userModel->getUsersById($allUserIds)) !== count($users)) {
            header('HTTP/1.1 400 Bad Request');
            echo "Each request body array item must contain an existing 'user_id'.";
            exit;
        }

        // validate that emails are not in use other than for records being updated
        $allUserEmails = array_map(fn($user) => $user->email, $users);
        $existingUsersWithEmails = $userModel->getUsersByEmails($allUserEmails);

        foreach($users as $user) {
            foreach($existingUsersWithEmails as $key => $existingUser) {
                if (in_array($existingUser['user_id'], $allUserIds)) {
                    unset($existingUsersWithEmails[$key]);
                }
            }
        }

        if (count($existingUsersWithEmails) > 0) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Each object in request body must specify an email not already in use.';
            exit;
        }
    }
}
