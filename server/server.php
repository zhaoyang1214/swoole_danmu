<?php
echo date('Y-m-d H:i:s'), " start\n";
$server = new Swoole\WebSocket\Server('0.0.0.0', 2333);

$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, // 设置单次最大发送长度
]);

$server->on('open', function (Swoole\WebSocket\Server $server, Swoole\Http\Request $request) {
    echo date('Y-m-d H:i:s'), " server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame) {
    echo date('Y-m-d H:i:s'), " receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $json = $frame->data;
    $data = json_decode($json, true);
    // ... 对请求数据过滤、校验
    $connections = $server->connections;
    echo date('Y-m-d H:i:s'), " 当前服务器共有 ", count($connections), " 个连接\n";
    foreach ($connections as $fd) {
        if($server->exist($fd)) {
            $pushRes = $server->push($fd, json_encode($data));
            echo date('Y-m-d H:i:s'), " 发送给 {$fd} $pushRes \n";
        }
    }
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();

