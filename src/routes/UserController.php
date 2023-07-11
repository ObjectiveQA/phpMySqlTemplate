<?php
class UserController extends BaseController
{
    public function delete($path)
    {
        // consume id
        if (array_key_exists(0, $path)) {
            $id = $path[0];
        } else {
            $id = null;
        }

        // validate id
        if (!$this->validateId($id)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Path element following /users must be a number greater than 0.';
            exit;
        }

        // delete from db
        $userModel = new UserModel();
        $userModel->deleteUser($id);

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
            $id = $path[0];
            if (!$this->validateId($id)) {
                header('HTTP/1.1 400 Bad Request');
                echo 'Path element following /users must be a number greater than 0.';
                exit;
            }
        } else {
            $id = null;
        }

        // get from db
        $userModel = new UserModel();
        if ($id) {
            $responseData = $userModel->getUserById($id);
        } else {
            $responseData = $userModel->getUsers();
        }

        $this->sendOutput(
            json_encode($responseData),
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );
        exit;
    }

    public function post()
    {
        $users = $this->consumeBody();

        // post to db
        $userModel = new UserModel();
        // first verify emails are not in use
        foreach($users as $user) {
            $existingUsersWithEmail = $userModel->getUserByEmail($user->email);
            if (count($existingUsersWithEmail) > 0) {
                header('HTTP/1.1 400 Bad Request');
                echo 'Each object in request body must specify an email not already in use.';
                exit;
            }
        }
        // then complete action
        $userModel->postUsers($users);

        header('HTTP/1.1 201 Created');
        exit;
    }

    public function put()
    {
        $users = $this->consumeBody();

        // further validation
        foreach($users as $user) {
            if (
                !(property_exists($user, 'user_id') && is_numeric($user->user_id) && $user->user_id > 0)
            ) {
                header('HTTP/1.1 400 Bad Request');
                echo "Each request body array item must contain a valid 'user_id'.";
                exit;
            };
        }

        $allIds = array_map(fn($user) => $user->user_id, $users);
        $uniqueIds = array_unique($allIds);
        if (count($uniqueIds) < count($allIds)) {
            header('HTTP/1.1 400 Bad Request');
            echo "Each request body array item must specify a unique 'user_id'.";
            exit;
        }

        // put to db
        $userModel = new UserModel();
        // first verify emails are not in use other than for records being updated
        foreach($users as $user) {
            $existingUsersWithEmail = $userModel->getUserByEmail($user->email);
            $userIds = array_map(fn($user) => $user->user_id, $users);
            foreach($existingUsersWithEmail as $key => $existingUser) {
                if (in_array($existingUser['user_id'], $userIds)) {
                    unset($existingUsersWithEmail[$key]);
                }
            }
            if (count($existingUsersWithEmail) > 0) {
                header('HTTP/1.1 400 Bad Request');
                echo 'Each object in request body must specify an email not already in use.';
                exit;
            }
        }
        // then complete action
        $userModel->putUsers($users);

        header('HTTP/1.1 204 No Content');
        exit;
    }

    private function consumeBody()
    {
        // parse incoming json
        $json = file_get_contents('php://input');
        $users = json_decode($json);

        // validate incoming json
        if (!is_array($users)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Request body must be an array';
            exit;
        }

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

        $allEmails = array_map(fn($user) => $user->email, $users);
        $uniqueEmails = array_unique($allEmails);
        if (count($uniqueEmails) < count($allEmails)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Each object in request body must specify a unique email.';
            exit;
        }

        return $users;
    }

    private function validateId($id)
    {
        if (!is_numeric($id) || $id < 1) {
            return false;
        }

        return true;
    }
}