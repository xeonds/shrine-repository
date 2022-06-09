<?php

include_once 'core/data/Meta.php';
include_once 'core/logic/MetaDB.php';
include_once 'core/logic/UserDB.php';
include_once 'core/util/Response.php';
include_once 'core/util/AuthUser.php';
include_once 'core/util/Function.php';

$config = 'db/config.json';
$static = 'static/';

switch (keyNN($_GET))
{
	case '':
		require $static . 'index.html';
		break;

	case 'admin':
		require $static . 'admin.html';
		break;

	case 'meta':
		require $static . 'meta.html';
		break;

	case 'api':
		header('Access-Control-Allow-Origin:*');
		switch (keyNN($_GET))
		{
			case 'v1':
				switch (keyNN($_GET))
				{
					case 'user':
						switch (keyNN($_GET))
						{
							case 'login':
								{
									$user = new AuthUser($a = null, $u = $_POST['uid'], $p = $_POST['password']);
									if ($user->auth() == true)
									{
										echo (new Response(200, $data = array(
											'apikey' => $user->udb->getUser($uid = $_POST['uid'])
										)))->get();
									}
									else
									{
										echo (new Response(403, 'login failed'))->get();
									}
								}
								break;

							case 'register':
								if (false != $uid = (new UserDB)->createUser($_POST['username'], $_POST['password']))
									echo (new Response(200, (new UserDB)->getUser($uid = $uid)))->get();
								else
									echo (new Response(400, 'Create user failed', false))->get();
								break;
						}
						break;

					case 'meta':
						switch (keyNN($_GET))
						{
							case 'get_meta':
								if ($id = $_POST['id'] == null)
								{
									$data = (new MetaDB)->getList();
								}
								else
								{
									$data = (new MetaDB)->getMeta($id);
								}
								echo (new Response(200, $data))->get();
								break;

							case 'create_meta':
								$postData['meta'] = new Meta($_POST['time'], $_POST['type'], explode(',', $_POST['tag']), $_POST['uid']);
								switch ($_POST['type'])
								{
									case 'text':
										$postData['meta'] = new Text($postData['meta'], $_POST['title'], $_POST['content']);
										break;

									case 'metaArray':
										$postData['meta'] = new MetaArray($postData['meta'], explode(',', $_POST['metaArray']));
										break;

									default:
										$postData['meta'] = new File($postData['meta'], $_FILES['file']['name']);
										$postData['filePath'] = $_FILES['file']['tmp_name'];
										$postData['fileName'] = $_FILES['file']['name'];
										break;
								}
								if ((new MetaDB)->createMeta($postData))
									echo (new Response(200, $postData['meta']->get()))->get();
								else
									echo (new Response(400, 'Create meta failed', $isSuccess = false))->get();
								break;
						}
						break;

					case 'config':
						switch (keyNN($_GET))
						{
							case 'update':
								break;

							case 'get':
								echo (new Response(200, json_decode(file_get_contents($config), true)))->get();
								break;

							default:
								echo (new Response(404, 'no such api', false))->get();
								break;
						}
						break;
				}
				break;

			case 'v2':
				# code...
				break;
		}
		break;
}
