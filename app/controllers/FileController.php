<?php

namespace app\controllers;

use app\models\Directory;
use app\models\File;
use app\models\User;
use app\views\Json;
use wfm\Controller;

class FileController extends Controller
{
    public function showAllFilesAction()
    {
        session_start();
        if (isset($_SESSION['success'])) {
            if ($_SESSION['status_user'] == 'administrator') {
                $files = File::all();
                $objectsJsonUser = [];
                foreach ($files as $file) {
                    $objectsJsonUser[] = [
                        [
                            'name' => $file->name,
                            'path' => $file->path,
                            'user' => $file->user,
                            'directory' => $file->folder->name ?? '',
                        ]
                    ];
                }
                $files = json_encode($objectsJsonUser);
                $message = '';
            } elseif ($_SESSION['status_user'] == 'user') {
                $files = File::where('available_to_users', $_SESSION['userId'])
                    ->get();
                $objectsJsonUser = [];
                foreach ($files as $file) {
                    $objectsJsonUser[] = [
                        [
                            'name' => $file->name,
                            'path' => $file->path,
                            'user' => $file->user,
                            'directory' => $file->folder->name ?? '',
                        ]
                    ];
                }
                $files = json_encode($objectsJsonUser);
                $message = '';
            } else {
                $files = '';
                $message = 'авторизированный пользователь не администратор';
            }
        } else {
            $files = '';
            $message = 'нет авторизированных пользователей';
        }

        return (new Json(
            [
                'message' => $message,
                'files' => $files
            ]))->render();
    }

    public function addFileAction()
    {
        session_start();
//        debug($_SESSION, 1);
        if (isset($_SESSION['success'])) {
            if ($_SESSION['status_user'] == 'administrator') {
                if (isset($_POST['directory_id'])) {
                    $folder = Directory::where('id', $_POST['directory_id'])->first();
                    if (isset($folder)) {
                        if (isset($_FILES['file']['name'])) {
                            foreach ($_FILES as $data) {
                                $fileExist = File::where('name',$data['name'] )->first();
                                if (!isset($fileExist)) {
                                    $directoryName = $folder->name;
                                    $nameFile = $data['name'];
                                    $pathDir = $folder->path;
                                    $path = $pathDir . DIRECTORY_SEPARATOR . $nameFile;
                                    move_uploaded_file($_FILES['file']['tmp_name'], $path);
                                    $newFile = new File();
                                    $newFile->user = $_SESSION['userId'];
                                    $newFile->name = $data['name'];
                                    $newFile->path = $path;
                                    $newFile->directory_id = $folder->id;
                                    $newFile->available_to_users = $_SESSION['userId'] . ' ';
                                    $newFile->save();
                                    $message = 'файл записан в папку user_' . $folder->id_user_create . '/' . $directoryName;
                                    $result = true;
                                } else {
                                    $message = 'файл с таким именем уже есть в БД';
                                    $result = false;
                                }
                            }
                        } else {
                            $message = 'никокого файла не передано';
                            $result = false;
                        }
                    } else {
                        $message = 'такой папки не существует';
                        $result = false;
                    }
                } else {
                    if (isset($_FILES['file']['name'])) {
                        foreach ($_FILES as $data) {
                            $fileExist = File::where('name',$data['name'] )->first();
                            if (!isset($fileExist)) {
                                $nameFile = $data['name'];
                                $pathDir = getcwd()  . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . 'user_' . $_SESSION['userId'];
                                $path = $pathDir . DIRECTORY_SEPARATOR . $nameFile;
                                if(!is_dir($pathDir)) {
                                    $userDirectory = new Directory();
                                    $userDirectory->name = 'user_' . $_SESSION['userId'];
                                    $userDirectory->path = $pathDir;
                                    $userDirectory->id_user_create = $_SESSION['userId'];
                                    $userDirectory->save();
                                    mkdir($pathDir, 0777, true);
                                }
                                move_uploaded_file($_FILES['file']['tmp_name'], $path);
                                $newFile = new File();
                                $newFile->user = $_SESSION['userId'];
                                $newFile->name = $data['name'];
                                $newFile->path = $path;
                                $folderId = Directory::where('name', 'user_' . $_SESSION['userId'])->first();
                                $folderId = $folderId->id;
                                $newFile->directory_id = $folderId;
                                $newFile->available_to_users = $_SESSION['userId'] . ' ';
                                $newFile->save();
                                $message = 'файл записан в папку user_' . $_SESSION['userId'];
                                $result = true;
                            } else {
                                $message = 'файл с таким именем уже есть в БД';
                                $result = false;
                            }
                        }
                    } else {
                        $message = 'никокого файла не передано';
                        $result = false;
                    }
                }
            }
            elseif ($_SESSION['status_user'] == 'user') {
                if (isset($_POST['directory_id'])) {
                    $folder = Directory::where('id', $_POST['directory_id'])->first();
                    if (isset($folder)) {
                        if ($folder->id_user_create == $_SESSION['userId']) {
                            if (isset($_FILES['file']['name'])) {
                                foreach ($_FILES as $data) {
                                    $fileExist = File::where('name',$data['name'] )->first();
                                    if (!isset($fileExist)) {
                                        $directoryName = $folder->name;
                                        $nameFile = $data['name'];
                                        $pathDir = $folder->path;
                                        $path = $pathDir . DIRECTORY_SEPARATOR . $nameFile;
                                        move_uploaded_file($_FILES['file']['tmp_name'], $path);
                                        $newFile = new File();
                                        $newFile->user = $_SESSION['userId'];
                                        $newFile->name = $data['name'];
                                        $newFile->path = $path;
                                        $newFile->directory_id = $folder->id;
                                        $newFile->available_to_users = $_SESSION['userId'] . ' ';
                                        $newFile->save();
                                        $message = 'файл записан в папку user_' . $_SESSION['userId'] . '/' . $directoryName;
                                        $result = true;
                                    } else {
                                        $message = 'файл с таким именем уже есть в БД';
                                        $result = false;
                                    }
                                }
                            } else {
                                $message = 'никокого файла не передано';
                                $result = false;
                            }
                        } else {
                            $message = 'авторизированный пользователь не может записать файл в данную папку';
                            $result = false;
                        }
                    } else {
                        $message = 'такой папки не существует';
                        $result = false;
                    }
                } else {
                    if (isset($_FILES['file']['name'])) {
                        foreach ($_FILES as $data) {
                            $fileExist = File::where('name',$data['name'] )->first();
                            if (!isset($fileExist)) {
                                $nameFile = $data['name'];
                                $pathDir = getcwd()  . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . 'user_' . $_SESSION['userId'];
                                $path = $pathDir . DIRECTORY_SEPARATOR . $nameFile;
                                if(!is_dir($pathDir)) {
                                    $userDirectory = new Directory();
                                    $userDirectory->name = 'user_' . $_SESSION['userId'];
                                    $userDirectory->path = $pathDir;
                                    $userDirectory->id_user_create = $_SESSION['userId'];
                                    $userDirectory->save();
                                    mkdir($pathDir, 0777, true);
                                }
                                move_uploaded_file($_FILES['file']['tmp_name'], $path);
                                $newFile = new File();
                                $newFile->user = $_SESSION['userId'];
                                $newFile->name = $data['name'];
                                $newFile->path = $path;
                                $folderId = Directory::where('name', 'user_' . $_SESSION['userId'])->first();
                                $folderId = $folderId->id;
                                $newFile->directory_id = $folderId;
                                $newFile->available_to_users = $_SESSION['userId'] . ' ';
                                $newFile->save();
                                $message = 'файл записан в папку user_' . $_SESSION['userId'];
                                $result = true;
                            } else {
                                $message = 'файл с таким именем уже есть в БД';
                                $result = false;
                            }
                        }
                    } else {
                        $message = 'никокого файла не передано';
                        $result = false;
                    }
                }
            } else {
                $message = 'авторизированный пользователь имеет статут отличный от обычного пользователя и администратора';
                $result =  false;
            }
        } else {
            $message = 'нет авторизированных пользователей';
            $result =  false;
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

    public function editFileAction()
    {
        session_start();
        $jsonInput = file_get_contents('php://input');
        $body = json_decode($jsonInput, true);
//        debug($body, 1);
        if (isset($_SESSION['success'])) {
            if ($_SESSION['status_user'] == 'administrator') {
                if (isset($body['directory_id'])) {
                    $folder = Directory::find($body['directory_id']);
                    $directoryNameNew = $folder->name;
                    if (isset($folder)) {
                        if (isset($body['id'])) {
                            $file = File::find($body['id']);
//                            $pieces = explode(".", $file->name);
//                            $fileNameNew = $body['newName'] . '.' . end($pieces);
                            if ($file != null) {
                                if (isset($body['newName'])) {
                                    $pieces = explode(".", $file->name);
                                    $fileNameNew = $body['newName'] . '.' . end($pieces);
                                    $fileExist = File::where('name', $fileNameNew)->first();
                                    if (!isset($fileExist)) {
                                        if ($folder->id_parent_folder != null) {
                                            $pathOld = $file->path;
                                            $directoryParentNew = Directory::find($folder->id_parent_folder);
                                            $directoryParentNameNew = $directoryParentNew->name;
                                            $pathNew = getcwd() . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . $directoryParentNameNew . DIRECTORY_SEPARATOR . $directoryNameNew . DIRECTORY_SEPARATOR . $fileNameNew;
                                            $pieces = explode(".", $file->name);
                                            $fileNameNew = $body['newName'] . '.' . end($pieces);
                                            $file->name = $fileNameNew;
                                            $file->path = $pathNew;
                                            $file->directory_id = $body['directory_id'];
                                            $file->save();
                                            rename($pathOld, $pathNew);
                                            $message = 'файл с ид= ' . $file->id .' переименован и перемещен в папку с ид=' . $body['directory_id'];
                                            $result = true;
                                        } else {
                                            $pathOld = $file->path;
                                            $pathNew = getcwd() . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . $directoryNameNew . DIRECTORY_SEPARATOR . $fileNameNew;
                                            $pieces = explode(".", $file->name);
                                            $fileNameNew = $body['newName'] . '.' . end($pieces);
                                            $file->name = $fileNameNew;
                                            $file->path = $pathNew;
                                            $file->directory_id = $body['directory_id'];
                                            $file->save();
                                            rename($pathOld, $pathNew);
                                            $message = 'файл с ид= ' . $file->id .' переименован и перемещен в папку с ид=' . $body['directory_id'];
                                            $result =  true;
                                        }
                                    } else {
                                        $message = 'файл с таким именем есть в БД';
                                        $result =  false;
                                    }
                                } else {
                                    $message = 'не передано имени нового файла';
                                    $result =  false;
                                }
                            } else {
                                $message = 'файла с таким ИД не существует';
                                $result =  false;
                            }
                        } else {
                            $message = 'не передан id файла';
                            $result =  false;
                        }
                    } else {
                        $message = 'такой папки не существует';
                        $result =  false;
                    }
                } else {
                    if (isset($body['id'])) {
                        $file = File::find($body['id']);
                        if ($file != null) {
                            if (isset($body['newName'])) {
                                $pieces = explode(".", $file->name);
                                $fileNameNew = $body['newName'] . '.' . end($pieces);
                                $fileExist = File::where('name', $fileNameNew)->first();
                                if (!isset($fileExist)) {
                                    $pieces = explode(".", $file->name);
                                    $fileNameNew = $body['newName'] . '.' . end($pieces);
                                    $file->name = $fileNameNew;
                                    $arrayPathOld = explode(DIRECTORY_SEPARATOR, $file->path);
                                    $removed = array_pop($arrayPathOld);
                                    $arrayPathOld[] = $fileNameNew;
                                    $pathNew = implode(DIRECTORY_SEPARATOR, $arrayPathOld);
                                    $pathOld = $file->path;
                                    rename($pathOld, $pathNew);
                                    $file->path = $pathNew;
                                    $file->save();
                                    $message = 'у файла с ид=' . $body['id'] . ' изменено имя';
                                    $result =  true;
                                } else {
                                    $message = 'файл с таким именем есть в БД';
                                    $result =  false;
                                }
                            } else {
                                $message = 'не передано имени файла';
                                $result =  false;
                            }
                        } else {
                            $message = 'файла с таким ИД не существует';
                            $result =  false;
                        }
                    } else {
                        $message = 'не передан id файла';
                        $result =  false;
                    }
                }
            } elseif ($_SESSION['status_user'] == 'user') {
                if (isset($body)) {
                    if (isset($body['id'])) {
                        $file = File::find($body['id']);
                        if ($file != null) {
                            if ($file->user == $_SESSION['userId']) {
                                if (isset($body['directory_id'])) {
                                    $directory = Directory::find($body['directory_id']);
                                    $directoryNameNew = $directory->name;
                                    if ($directory->id_user_create == $_SESSION['userId']) {
                                        if ($directory->id_parent_folder != null) {
                                            if (isset($body['newName'])) {
                                                $pieces = explode(".", $file->name);
                                                $fileNameNew = $body['newName'] . '.' . end($pieces);
                                                $fileExist = File::where('name', $fileNameNew)->first();
                                                if (!isset($fileExist)) {
                                                    $pathOld = $file->path;
                                                    $directoryParentNew = Directory::find($directory->id_parent_folder);
                                                    $directoryParentNameNew = $directoryParentNew->name;
                                                    $pathNew = getcwd() . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . $directoryParentNameNew . DIRECTORY_SEPARATOR . $directoryNameNew . DIRECTORY_SEPARATOR . $fileNameNew;
                                                    $pieces = explode(".", $file->name);
                                                    $fileNameNew = $body['newName'] . '.' . end($pieces);
                                                    $file->name = $fileNameNew;
                                                    $file->path = $pathNew;
                                                    $file->directory_id = $body['directory_id'];
                                                    $file->save();
                                                    rename($pathOld, $pathNew);
                                                    $message = 'файл с ид= ' . $file->id .' переименован и перемещен в папку с ид=' . $body['directory_id'];
                                                    $result = true;
                                                } else {
                                                    $message = 'файл с таким именем есть в БД';
                                                    $result =  false;
                                                }
                                            } else {
                                                $message = 'не передано имени файла';
                                                $result =  false;
                                            }
                                        } else {
                                            if (isset($body['newName'])) {
                                                $pieces = explode(".", $file->name);
                                                $fileNameNew = $body['newName'] . '.' . end($pieces);
                                                $fileExist = File::where('name', $fileNameNew)->first();
                                                if (!isset($fileExist)) {
                                                    $pathOld = $file->path;
                                                    $pathNew = getcwd() . DIRECTORY_SEPARATOR . 'dataUser' . DIRECTORY_SEPARATOR . $directoryNameNew . DIRECTORY_SEPARATOR . $fileNameNew;
                                                    $pieces = explode(".", $file->name);
                                                    $fileNameNew = $body['newName'] . '.' . end($pieces);
                                                    $file->name = $fileNameNew;
                                                    $file->path = $pathNew;
                                                    $file->directory_id = $body['directory_id'];
                                                    $file->save();
                                                    rename($pathOld, $pathNew);
                                                    $message = 'файл с ид= ' . $file->id .' переименован и перемещен в папку с ид=' . $body['directory_id'];
                                                    $result = true;
                                                } else {
                                                    $message = 'файл с таким именем есть в БД';
                                                    $result =  false;
                                                }
                                            } else {
                                                $message = 'не передано имени файла';
                                                $result =  false;
                                            }
                                        }
                                    } else {
                                        $message = 'авторизированный пользователь не может переместить файл в эту папку';
                                        $result =  false;
                                    }
                                } else {
                                    if (isset($body['newName'])) {
                                        $pieces = explode(".", $file->name);
                                        $fileNameNew = $body['newName'] . '.' . end($pieces);
                                        $fileExist = File::where('name', $fileNameNew)->first();
                                        if (!isset($fileExist)) {
                                            $pieces = explode(".", $file->name);
                                            $fileNameNew = $body['newName'] . '.' . end($pieces);
                                            $file->name = $fileNameNew;
                                            $arrayPathOld = explode(DIRECTORY_SEPARATOR, $file->path);
                                            $removed = array_pop($arrayPathOld);
                                            $arrayPathOld[] = $fileNameNew;
                                            $pathNew = implode(DIRECTORY_SEPARATOR, $arrayPathOld);
                                            $pathOld = $file->path;
                                            rename($pathOld, $pathNew);
                                            $file->path = $pathNew;
                                            $file->save();
                                            $message = 'у файла с ид=' . $body['id'] . ' изменено имя';
                                            $result =  true;
                                        } else {
                                            $message = 'файл с таким именем есть в БД';
                                            $result =  false;
                                        }
                                    } else {
                                        $message = 'не передано имени файла';
                                        $result =  false;
                                    }
                                }
                            } else {
                                $message = 'авторизированный пользователь не может изменить данный файл';
                                $result =  false;
                            }
                        } else {
                            $message = 'файла с таким ИД не существует';
                            $result =  false;
                        }
                    } else {
                        $message = 'не передан id файла';
                        $result =  false;
                    }
                } else {
                    $message = 'ничего не передено';
                    $result =  false;
                }
            }
        } else {
            $message = 'нет авторизированных пользователей';
            $result =  false;
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

    public function showFileByIdAction()
    {
        session_start();
        if (isset($_SESSION['success'])) {
            $id = $this->route['alias'];
            $file = File::find($id);
            $userIdCreator = $file->user;
            if ($_SESSION['userId'] == $userIdCreator || $_SESSION['status_user'] == 'administrator') {
                $objectsJsonUser[] = [
                    [
                        'name' => $file->name,
                        'path' => $file->path,
                        'user' => $file->user,
                        'directory' => $file->folder->name,
                    ]
                ];
                $files = $objectsJsonUser;
                $message = '';
            } else {
                $files = '';
                $message = 'авторизированный пользователь не может узнать данные этого файла';
            }
        } else {
            $files = '';
            $message = 'нет авторизированных пользователей';
        }

        return (new Json(
            [
                'files' => $files,
                'message' => $message,
            ]))->render();
    }

    public function deleteFileAction()
    {
        session_start();
        $id = $this->route['alias'];
        if (isset($_SESSION['success'])) {
            $file = File::find($id);
            if (isset($file)) {
                if ($_SESSION['userId'] == $file->user || $_SESSION['status_user'] == 'administrator') {
                    $filepath = $file->path;
                    unlink($filepath);
                    $message ='файл с ID= ' . $id . ' удален';
                    $result = true; $file->delete();
                } else {
                    $message = 'авторизированный пользователь не может удалить данный файл';
                    $result = false;
                }
            } else {
                $message = 'файла с таким ИД нет в БД';
                $result = false;
            }
        } else {
            $message = 'нет авторизированных пользователей';
            $result = false;
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

    public function showUsersAvailableToFileAction()
    {
        session_start();
        $id = $this->route['alias'];
        $file = File::find($id);
        if (isset($_SESSION['success'])) {
            if (isset($file)) {
                if ($file->user == $_SESSION['userId'] || $_SESSION['status_user'] == 'administrator') {
                    $arrayAvailableNameUsers = [];
                    $arrayAvailableUsers = explode(' ', $file->available_to_users);
                    foreach ($arrayAvailableUsers as $availableUsers) {
                        $user = User::find($availableUsers);
                        if ($user != null) {
                            $arrayAvailableNameUsers[] = $user->name;
                        }

                    }
                    $message = '';
                    $result = 'пользователи, кому доступен файл: ' . implode(",", $arrayAvailableNameUsers);
                } else {
                    $message = 'авторизированный пользователь не может посмотреть, кому доступен данный файл';
                    $result = false;
                }
            } else {
                $message = 'файла с таким ИД нет в БД';
                $result = false;
            }
        } else {
            $message = 'нет авторизированных пользователей';
            $result = false;
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

    public function shareAvailableToFileAction()
    {
        session_start();
        $idFile = $this->route['file'];
        $idUser = $this->route['user'];
        $file = File::find($idFile);
        $user = User::find($idUser);
        if (isset($file) && isset($user)) {
            $available_to_users = explode(' ', $file->available_to_users);
            if (isset($_SESSION['success'])) {
                var_dump($file->user == $_SESSION['userId']);
                if ($file->user == $_SESSION['userId'] || $_SESSION['status_user'] == 'administrator') {
                        if (in_array($idUser, $available_to_users)) {
                            $message = 'пользователь ' . $user->name . ' уже имеет доступ к файлу';
                            $result = false;
                        } else {
                            $file->available_to_users = $file->available_to_users . ' ' . $idUser;
                            $file->save();
                            $message = 'пользователю c id= ' . $idUser . ' дан доступ к файлу';
                            $result = true;
                        }
                } else {
                    $message = 'авторизированный пользователь не может посмотреть, кому доступен данный файл';
                    $result = false;
                }
            } else {
                $message = 'нет авторизированных пользователей';
                $result = false;
            }
        } else {
            $message = 'файла или пользователя с таким ИД нет в БД';
            $result = false;
        }

        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }

    public function deleteAvailableToFileAction()
    {
        session_start();
        $idFile = $this->route['file'];
        $idUser = $this->route['user'];
        $file = File::find($idFile);
        $user1 = User::find($idUser);
        if (isset($file) && isset($user1)) {
            if (isset($_SESSION['success'])) {
                if ($file->user == $_SESSION['userId'] || $_SESSION['status_user'] == 'administrator') {
                    $availabl_to_users = explode(' ', $file->available_to_users);
                    foreach ($availabl_to_users as $available_to_user) {
                        if ($available_to_user == $idUser) {
                            $message = 'пользователю ' . $user1->name . ' с ид ' . $user1->id . ' убран доступ к файлу';
                            $stringToDelete = ' ' . $idUser;
                            $file->available_to_users = str_replace($stringToDelete, '', $file->available_to_users);
                            $file->save();
                            $result = true;
                            break;
                        } else {
                            $message = 'у пользователя ' . $user1->name . ' с ид ' . $user1->id . ' нет доступа к файлу';
                            $result = false;
                        }
                    }
                } else {
                    $message = 'авторизированный пользователь не может удалить доступ к этому файлу';
                    $result = false;
                }
            } else {
                $message = 'нет авторизированных пользователей';
                $result = false;
            }
        } else {
            $message = 'файла или пользователя с таким ИД нет в БД';
            $result = false;
        }


        return (new Json(
            [
                'message' => $message,
                'result' => $result
            ]))->render();
    }
}