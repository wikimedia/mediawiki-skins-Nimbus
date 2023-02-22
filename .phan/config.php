<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'../../extensions/Echo',
		'../../extensions/PictureGame',
		'../../extensions/PollNY',
		'../../extensions/RandomGameUnit',
		'../../extensions/QuizGame',
		'../../extensions/SocialProfile',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/Echo',
		'../../extensions/PictureGame',
		'../../extensions/PollNY',
		'../../extensions/RandomGameUnit',
		'../../extensions/QuizGame',
		'../../extensions/SocialProfile',
	]
);

return $cfg;
