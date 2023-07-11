<?php
class UserModel extends Database
{

    public function deleteUser($id)
    {
        $this->delete('users', 'user_id', $id);
    }

    public function getUserByEmail($email)
    {
        return $this->select('users', "email = '$email'");
    }

    public function getUserById($id)
    {
        return $this->select('users', "user_id = $id");
    }

    public function getUsers()
    {
        return $this->select('users', 'user_id', null);
    }

    public function postUsers($users)
    {
        $columns = array('full_name', 'email');
        $data = array_map(fn($user) => array($user->full_name, $user->email), $users);

        $this->insert('users', $columns, $data);
    }

    public function putUsers($users)
    {
        $columns = array('full_name', 'email');
        $data = array_map(fn($user) => array($user->full_name, $user->email), $users);
        $allIds = array_map(fn($user) => $user->user_id, $users);

        $this->update('users', 'user_id', $allIds, $columns, $users);
    }
}