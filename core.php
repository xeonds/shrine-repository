<?php

require_once 'include/meta.php';
require_once 'include/config.php';
require_once 'include/util/Response.php';
require_once 'include/util/AuthUser.php';
require_once 'include/util/Function.php';

$config = 'config.json';
$static = 'static/';

switch (keyNN($_GET)) {
		/**
	 * A single page program.
	 * including search, comments, tags and more.
	 */
	case '':
	case 'index':
		require $static . 'index.html';
		break;

	case 'admin':
		require $static . 'admin.html';
		break;

		/**
		 * Viewer of metas like file, article.
		 */
	case 'meta':
		require $static . 'meta.html';
		break;

	case 'api':
		header('Access-Control-Allow-Origin:*');
		switch (keyNN($_GET)) {
			case 'v1':
				switch (keyNN($_GET)) {
					case 'user':
						switch (keyNN($_GET)) {
							case 'login':
								$user = new AuthUser(u: $_POST['uid'], p: $_POST['password']);
								if ($user->auth()) {
									echo Response::gen(200, 'success', $user->info);
								} else {
									echo Response::gen(403, 'login failed');
								}
								break;

							case 'register':
								if (false != $uid = (new UserDB)->createUser($_POST['username'], $_POST['password'])) {
									echo Response::gen(200, 'success', (new UserDB)->getUser(uid: $uid));
								} else {
									echo Response::gen(400, 'Create user failed');
								}
								break;

							case 'get_user':
								if (false != $user_data = (new UserDB)->getUser(uid: $_POST['uid'])) {
									echo Response::gen(200, 'success', $user_data);
								} else {
									echo Response::gen(404, 'user id not found');
								}
								break;
						}
						break;

					case 'meta':
						switch (keyNN($_GET)) {
							case 'get_meta':
								if (isset($_POST['id'])) {
									$data = (new MetaDB)->getMeta($_POST['id']);
								} else {
									$data = (new MetaDB)->getList();
								}
								echo Response::gen(200, 'success', $data);
								break;

							case 'create_meta':
								$postData['meta'] = new Meta($_POST['time'], $_POST['type'], explode(',', $_POST['tag']), $_POST['uid']);
								switch ($_POST['type']) {
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
									echo Response::gen(200, 'success', $postData['meta']->get());
								else
									echo Response::gen(400, 'Create meta failed');
								break;

							case 'search_meta':
								/**
								 * TODO: search meta
								 */
								break;
						}
						break;

					case 'config':
						switch (keyNN($_GET)) {
							case 'update':
								(new Config)->save($_POST['config']);
								echo Response::gen(200, 'success');
								break;

							case 'get':
								echo Response::gen(200, 'success', (new Config)->get());
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
