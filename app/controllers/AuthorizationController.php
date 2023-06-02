<?php

namespace app\controllers;

use app\models\User;
use app\views\Json;
use PHPMailer\PHPMailer\PHPMailer;
use wfm\Controller;

class AuthorizationController extends Controller
{
    public function loginAction()
    {
        $title = 'авторизация';
        $user = '';
        if (isset($_POST['email'])) {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $user = User::where('email', $email)->first();
            if (isset($user)) {
                if ($user->email == $email && password_verify($password, $user->password )) {
                    session_start();
                    $_SESSION['userId'] = $user->id ;
                    if (isset($user->status)) {
                        $_SESSION['status_user'] = $user->status;
                        $_SESSION['auth'] = 'auth';
                        $_SESSION['userId'] = $user->id;
                        $_SESSION['success'] = 'вы авторизированы';
                    }
                    $message = 'пользователь авторизован';

                    return (new Json(
                        [
                            'message' => $message,
                        ]))->render();
                } else {
                    $message = 'неправильно введен пароль';

                    return (new Json(
                        [
                            'message' => $message,
                        ]))->render();
                }
            } else {
                $message = 'такого пользователя нет в БД';

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

    public function logoutAction()
    {
        session_start();
        $result = 'вы успешно вышли с сайта';
        if(isset($_SESSION['userId'])) {
            session_destroy();
        }

        return (new Json(
            [
                'result' => $result
            ]))->render();
    }

    public function passwordResetGetAction()
    {
        if (isset($_GET['code'])) {
            $email = $_GET['email'];
            $user = User::where('email', $email)->first();
            $user->password = password_hash('12345', PASSWORD_DEFAULT);
            $user->save();
            $message = 'пароль для пользователя с email ' . $email . ' установлен на "12345"';

            return new Json(
                [
                    'message' => $message,
                ]);
        }

        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            $user = User::where('email', $email)->first();
            if (isset($user)) {
                $code = rand(1, 1000);
                $letterBody = 'Для восстановления пароля перейдите по <a href="http://' . $_SERVER['SERVER_NAME'] . ':8000' . '/passwordReset?code=' . $code . '&email=' . $email . '">' . 'ссылке</a>.';
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 2;         /*Оставляем как есть*/
                $mail->isSMTP();              /*Запускаем настройку SMTP*/
                $mail->Host = 'app.debugmail.io'; /*Выбираем сервер SMTP*/
                $mail->SMTPAuth = true;        /*Активируем авторизацию на своей почте*/
                $mail->Username = '2e6f2a3c-3a66-4bae-95fe-7e1dbc7b6df5';   /*Имя(логин) от аккаунта почты отправителя */
                $mail->Password = '6e3c8573-3410-43b6-a900-463581444a7c';        /*Пароль от аккаунта  почты отправителя */
                $mail->SMTPSecure = 'false';            /*Указываем протокол*/
                $mail->Port = 25;            /*Указываем порт*/
                $mail->CharSet = 'UTF-8';/*Выставляем кодировку*/

                $mail->setFrom('admin@mail.ru');/*Указываем адрес почты отправителя */
                /*Указываем перечень адресов почты куда отсылаем сообщение*/
                $mail->addAddress($email, $user->name . ' ' . $user->surname);

                $mail->isHTML(true);      /*формируем html сообщение*/
                $mail->Subject = 'сброс пароля'; /*Заголовок сообщения*/
                $mail->Body = $letterBody;/* Текст сообщения */
                $mail->AltBody = 'сообщение о сбросе пароля входа на сайт';/*Описание сообщения */
                $mail->send();
                //                sendEmail($email, 'Восстановление пароля', $letterBody);
                $message = 'На почту  с ' . $email . ' отправлено письмо со ссылкой на восстановление пароля';
                return new Json(
                    [
                        'message' => $message,
                    ]);
            } else {
                $message = 'такого пользователя нет в БД';

                return new Json(
                    [
                        'message' => $message,
                    ]);
            }
        } else {
            $message = 'email не передан';

            return new Json(
                [
                    'message' => $message,
                ]);
        }
    }
}