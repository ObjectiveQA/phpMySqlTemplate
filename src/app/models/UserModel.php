<?php
class UserModel extends Database
{
    public function deleteUsers($userIds)
    {
        $deleteWhere = (object) [
            'column' => 'user_id',
            'value' => $userIds,
            'valueArray' => true,
            'valueType' => 'number'
        ];

        $queryObjects = array((object) [
            'statementType' => 'delete',
            'table' => 'users',
            'where' => $deleteWhere
        ]);

        $this->executeQueries($queryObjects);
    }

    public function getUsersByEmails($emails)
    {
        $selectWhere = (object) [
            'column' => 'email',
            'value' => $emails,
            'valueArray' => true,
            'valueType' => 'text'
        ];

        $queryObject = (object) [
            'columns' => null,
            'statementType' => 'select',
            'table' => 'users',
            'where' => $selectWhere
        ];

        return $this->select($queryObject);
    }

    public function getUserById($userId)
    {
        $selectWhere = (object) [
            'column' => 'user_id',
            'value' => $userId,
            'valueType' => 'number'
        ];

        $queryObject = (object) [
            'columns' => null,
            'statementType' => 'select',
            'table' => 'users',
            'where' => $selectWhere
        ];

        $userObjects = $this->select($queryObject);

        if (count($userObjects) === 0) {
            return null;
        }

        return $userObjects[0];
    }

    public function getUsers()
    {
        $queryObject = (object) [
            'columns' => null,
            'statementType' => 'select',
            'table' => 'users',
            'where' => null
        ];

        return $this->select($queryObject);
    }

    public function getUsersById($userIds)
    {
        $where = (object) [
            'column' => 'user_id',
            'value' => $userIds,
            'valueArray' => true,
            'valueType' => 'number'
        ];
        $queryObject = (object) [
            'columns' => null,
            'statementType' => 'select',
            'table' => 'users',
            'where' => $where
        ];

        return $this->select($queryObject);
    }

    public function postUsers($users)
    {
        $userColumns = array(
            (object) ['name' => 'email', 'type' => 'text'],
            (object) ['name' => 'full_name', 'type' => 'text']
        );

        $queryObjects = array();

        foreach($users as $user) {
            $userData = (object) [
                'email' => $user->email,
                'full_name' => $user->full_name
            ];
            $userQueryObj = (object) [
                'columns' => $userColumns,
                'rows' => array($userData),
                'statementType' => 'insert',
                'storeId' => true,
                'table' => 'users'
            ];

            array_push($queryObjects, $userQueryObj);
        }

        $this->executeQueries($queryObjects);
    }

    public function putUsers($users)
    {
        $userColumns = array(
            (object) ['name' => 'email', 'type' => 'text'],
            (object) ['name' => 'full_name', 'type' => 'text']
        );

        $queryObjects = array();

        foreach($users as $user) {
            $userData = (object) [
                'email' => $user->email,
                'full_name' => $user->full_name
            ];
            $where = (object) [
                'column' => 'user_id',
                'value' => $user->user_id,
                'valueType' => 'number'
            ];
            $queryObj = (object) [
                'columns' => $userColumns,
                'row' => $userData,
                'statementType' => 'update',
                'table' => 'users',
                'where' => $where
            ];

            array_push($queryObjects, $queryObj);
        }

        $this->executeQueries($queryObjects);
    }
}
