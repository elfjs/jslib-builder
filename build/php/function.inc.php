<?php

function key_in_array($key, $arr) {
	$flag = false;
	foreach($arr as $k => $v) {
		if ($k == $key) {
			$flag = true;
			break;
		}
	}
	return $flag;
}



/**
 * ��ȡĳ���ļ����µ������ļ��б��ԡ�.����ͷ�ĳ���
 * @param {String} $path Ҫ������ļ�(�б�/�ļ���)·�����������ļ�·�����ļ���·���������ļ�·������
 * @param {String} $baseDir ���ڲ�ѯ��Ŀ¼
 * @param {Boolean} $deep �Ƿ�ݹ��ȡ�����ļ�
 */
function get_dir_list($path = './', $baseDir = './', $deep = false) {
	$list = array();
	
	if (is_array($path)) {
		foreach ($path as $item) {
			$list = array_merge($list, get_dir_list($item, $baseDir, $deep));
		}
	} else {
		$curPath = $baseDir . $path;
		if (is_dir($curPath)) {
			$dir = opendir($curPath);
			$fileList = array();
			while (($file = readdir($dir)) !== false) {
				if (($pos = strpos($file, '.')) === false || $pos > 0) {
					array_push($fileList, $file);
				}
			}
			closedir($dir);
			
			sort($fileList, SORT_STRING);
			
			foreach($fileList as $file) {
				$curFile = $curPath . $file;
				if (is_dir($curFile)) {
					$list = array_merge($list, get_dir_list("$file/", $curPath, $deep));
				} else {
					array_push($list, $curFile);
				}
			}
		} else {
			$list[] = $curPath;
		}
	}
	
	return array_unique($list);
}

function dir_dfs($path, $func, $filter = null) {
	$cur = $path;
	if (file_exists($cur) && (!$filter || $filter($cur))) {
		if ($func($cur) === false) {
			return false;
		}
		if (is_dir($cur)) {
			$dir = opendir($cur);
			$fileList = array();
			while (($f = readdir($dir)) !== false) {
				if ($f != '.' && $f != '..') {
					array_push($fileList, $f);
				}
			}
			closedir($dir);
			
			sort($fileList, SORT_STRING);
			
			foreach($fileList as $file) {
				if (dir_dfs("$cur/$file", $func, $filter) === false) {
					break;
				}
			}
		}
	}
}


/**
 * ��ȡ�ļ���һ���ĵ�ע����Ϣ
 */
function parse_source_head_info($content) {
	$info = null;
	$comment = '';
	$frag = explode('/**', $content);
	if (count($frag) >= 2) {
		$frag = explode('*/', $frag[1]);
		if (count($frag) >= 2) {
			$comment = $frag[0];
		}
	}
	if ($comment) {
		$lines = preg_split("/\s\*\s/", $comment);
		$info = array(
			'ignore' => false,
			'class' => '',
			'description' => '',
			'singleton' => false
		);
		$desc = array();
		foreach ($lines as $line) {
			$line = trim($line);
			if (strpos($line, '@') === 0) {
				if (preg_match("/^@(\w+)(\s[\w\.]+)?\n$/", $line, $type)) {
					switch ($type[1]) {
					case 'ignore':
						$info['ignore'] = true;
						break;
					case 'class':
						$info['class'] = trim($type[2]);
						break;
					case 'singleton':
						$info['singleton'] = true;
						break;
					default:
						break;
					}
				}
			} else {
				if (trim($line)) {
					array_push($desc, $line);
				}
			}
		}
		$info['description'] = join('<br />', $desc);
	}
	return $info;
}
?>