<?php

use wfm\Router;

//Router::add('^admin/(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$', ['admin_prefix' => 'admin']);

Router::add('^admin/user?$', ['controller' => 'Admin', 'action' => 'showAllUser']);
Router::add('^admin/user/(?P<alias>[0-9-]+)$', ['controller' => 'Admin', 'action' => 'showUserById']);
Router::add('^admin/user/(?P<alias>[0-9-]+)$', ['controller' => 'Admin', 'action' => 'deleteUser'], "DELETE");
Router::add('^admin/user?$', ['controller' => 'Admin', 'action' => 'editUser'], "PUT");

Router::add('^user/(?P<alias>[0-9-]+)$', ['controller' => 'User', 'action' => 'showUserById']);
Router::add('^users$', ['controller' => 'User', 'action' => 'showAllUser']);
Router::add('^user$', ['controller' => 'User', 'action' => 'createUser'], "POST");
Router::add('^user/(?P<alias>[0-9-]+)$', ['controller' => 'User', 'action' => 'updateUser'], "PUT");
//Router::add('^user/(?P<alias>[0-9-]+)$', ['controller' => 'User', 'action' => 'deleteUser'], "DELETE");

Router::add('^login$', ['controller' => 'Authorization', 'action' => 'login'], "POST");
Router::add('^logout$', ['controller' => 'Authorization', 'action' => 'logout']);

Router::add('^file$', ['controller' => 'File', 'action' => 'addFile'], "POST");
//Router::add('^file$', ['controller' => 'File', 'action' => 'editFile'], "PUT");
//Router::add('^file$', ['controller' => 'File', 'action' => 'showAllFiles']);
Router::add('^file/(?P<alias>[0-9-]+)$', ['controller' => 'File', 'action' => 'deleteFile'], "DELETE");
//Router::add('^file/(?P<alias>[0-9-]+)$', ['controller' => 'File', 'action' => 'showFileById']);
Router::add('^file/share/(?P<alias>[0-9-]+)$', ['controller' => 'File', 'action' => 'showUsersAvailableToFile']);
Router::add('^file/share/(?P<file>[0-9-]+)/(?P<user>[0-9-]+)$', ['controller' => 'File', 'action' => 'shareAvailableToFile'], "PUT");
Router::add('^file/share/(?P<file>[0-9-]+)/(?P<user>[0-9-]+)$', ['controller' => 'File', 'action' => 'deleteAvailableToFile'], "DELETE");

Router::add('^directory$', ['controller' => 'Directory', 'action' => 'addDirectory'], "POST");
Router::add('^directory/(?P<alias>[0-9-]+)$', ['controller' => 'Directory', 'action' => 'deleteDirectory'], "DELETE");
//Router::add('^directory/(?P<alias>[0-9-]+)$', ['controller' => 'Directory', 'action' => 'showFilesInDirectory']);
Router::add('^directory$', ['controller' => 'Directory', 'action' => 'editDirectory'], "PUT");

Router::add('^$', ['controller' => 'Main', 'action' => 'index']);

Router::add('^(?P<controller>[a-z-]+)/(?P<action>[a-z-]+)/?$');
