<?php


namespace app\controllers;

use app\models\User;
use app\views\Json;
use RedBeanPHP\R;
use wfm\Controller;

/** @property User $model */
class UserController extends Controller
{

    public function indexAction()
    {
        echo __METHOD__;
        $names = $this->model->get_users();
        $one_name = R::getRow( 'SELECT * FROM user WHERE id = 2');
        $this->set(compact('names'));
    }

    public function viewAction()
    {
        echo __METHOD__;
    }

    public function showUserByIdAction()
    {
        $id = $this->route['alias'];
        $user = R::findOne('user', 'id = ?', [$id]);
        if (isset($user)) {
            $objectsJsonUser = [
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'password' => $user->password,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ];
        } else {
            $message = "User {$id} not found";

            return (new Json(
                [
                    'message' => $message,
                ]))->render();
        };

        return (new Json(
            [
                'users' => json_encode($objectsJsonUser)
            ]))->render();
    }

    public function showAllUserAction()
    {
        $users = R::findAll('user');
        $objectsJsonUsers = [];
        foreach ($users as $user) {
            $objectsJsonUsers[] = [
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'password' => $user->password,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ];
        }

        $json = new Json(
            [
                'users' => json_encode($objectsJsonUsers)
            ]);
        return $json->render();
    }

    public function createUserAction()
    {
        $jsonInput = file_get_contents('php://input');
        $body = json_decode($jsonInput, true);
        if (isset($body)) {
            $email = $body['email'];
            if (isset($body['name'])) {
                if (isset($body['surname'])) {
                    if (isset($body['password'])) {
                        if (isset($body['email'])) {
                            $user = R::findOne('user', 'email = ?', [$email]);
                            if (isset($user)) {
                                $message = 'такой email уже есть в БД';

                                return (new Json(
                                    [
                                        'message' => $message,
                                    ]))->render();
                            } else {
                                $newUser = R::dispense('user');
                                $newUser->name = $body['name'];
                                $newUser->surname = $body['surname'];
                                $newUser->password = password_hash($body['password'], PASSWORD_DEFAULT);
                                $newUser->email = $body['email'];
                                $newUser->created_at = date("Y-m-d");
                                $newUser->status = 'user';
                                R::store($newUser);
                                $message = 'новый пользователь с email ' . $body['email'] . ' создан';

                                return (new Json(
                                    [
                                        'message' => $message,
                                    ]))->render();
                            }
                        } else {
                            $message = 'email пользователя не введен';

                            return (new Json(
                                [
                                    'message' => $message,
                                ]))->render();
                        }
                    } else {
                        $message = 'пароль пользователя не введен';

                        return (new Json(
                            [
                                'message' => $message,
                            ]))->render();
                    }
                } else {
                    $message = 'фамилия пользователя не введена';

                    return (new Json(
                        [
                            'message' => $message,
                        ]))->render();
                }

            } else {
                $message = 'имя пользователя не введено';

                return (new Json(
                    [
                        'message' => $message,
                    ]))->render();
            }
        } else {
            $message = 'данных в запросе нет';

            return (new Json(
                [
                    'message' => $message,
                ]))->render();
        }
    }

    public function updateUserAction()
    {
        session_start();
        $jsonInput = file_get_contents('php://input');
        $body = json_decode($jsonInput, true);
        if (isset($body)) {
            $id = $this->route['alias'];
            $user = R::findOne('user', 'id = ?', [$id]);
            if (isset($user)) {
                if ($_SESSION['status_user'] == 'administrator' || $_SESSION['userId'] == $user->id) {
                    $user->name = $body['name'] ?? $user->name;
                    $user->surname = $body['surname'] ?? $user->surname;
                    if (isset($body['password'])) {
                        $user->password = password_hash($body['password'], PASSWORD_DEFAULT);
                    }
                    $user->email = $body['email'] ?? $user->email;
                    $user->status = $body['status'] ?? $user->status;
                    R::store($user);
                    $message = 'пользователь с id ' . $id . ' изменен';
                    $result = true;
                } else {
                    $result = false;
                    $message = 'авторизированный пользователь не администратор';
                }
                return (new Json(
                    [
                        'message' => $message,
                        'result' => $result
                    ]))->render();
            } else {
                $message = 'такого пользовател нет в БД';
                $result = false;

                return (new Json(
                    [
                        'message' => $message,
                        'result' => $result
                    ]))->render();
            }
        } else {
            $message = 'ничего не передано в запросе';
            $result = false;

            return (new Json(
                [
                    'message' => $message,
                    'result' => $result
                ]))->render();
        }
    }

    public function deleteUserAction()
    {
        session_start();
        $id = $this->route['alias'];
        $user = R::findOne('user', 'id = ?', [$id]);
        if ($_SESSION['status_user'] == 'administrator' || $_SESSION['userId'] == $user->id) {
            if (isset($user)) {
                $user = R::load('user', $id);
                R::trash($user);
                $result = true;
                $message = 'пользователь с id: ' . $id . ' удален';
            } else {
                $result = false;
                $message = 'пользователя в таким id нет в БД';
            }
        } else {
            $result = false;
            $message = 'авторизированный пользователь не администратор';
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

}