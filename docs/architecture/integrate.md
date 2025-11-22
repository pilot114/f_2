### Проксирование до нового бека

В данный момент, back2 развернут на 2 виртуалках, запросы проксируются по url
с основных доменов:

    https://beta-cp.siberianhealth.com/api/v2/rpc
    =>
    http://192.168.5.39/api/v2/rpc

    https://cp.siberianhealth.com/api/v2/rpc
    =>
    http://192.168.5.49/api/v2/rpc
