<?php
$router->post('api/study/start', 'Api\Study@start', 'api.study.start', ['web']);
$router->post('api/study/heartbeat', 'Api\Study@heartbeat', 'api.study.heartbeat', ['web']);
$router->post('api/study/pause', 'Api\Study@pause', 'api.study.pause', ['web']);
$router->post('api/study/resume', 'Api\Study@resume', 'api.study.resume', ['web']);
$router->post('api/study/checkin', 'Api\Study@checkin', 'api.study.checkin', ['web']);
$router->post('api/study/finish', 'Api\Study@finish', 'api.study.finish', ['web']);
$router->get('api/study/history', 'Api\Study@history', 'api.study.history', ['web']);
$router->get('api/teacher/live-sessions', 'Api\TeacherLiveSessions@index', 'api.teacher.live_sessions', ['web', 'role:teacher']);
$router->get('api/notifications/count', 'Api\Notifications@count', 'api.notifications.count', ['web']);
$router->get('api/notifications', 'Api\Notifications@list', 'api.notifications.list', ['web']);
